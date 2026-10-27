<?php

/**
 * delete user accounts (backend and self-deletion in the frontend)
 *
 * accounts are never removed from se_user. there are no foreign keys and
 * SQLite reuses the highest user_id after a DELETE - a new account would
 * inherit orders, comments etc. of the deleted one. instead the row stays as
 * a tombstone (user_class = 'deleted'), all personal data is removed and the
 * linked data is cleaned up:
 *
 * - orders are kept (retention duties), they have their own copy of the addresses
 * - comments are anonymized (or deleted on request), event confirmations deleted
 * - wishlists, carts, remember-me tokens and trusted devices are deleted
 * - api keys created by the user are deactivated
 * - group memberships and page authorizations are removed
 * - authored content (posts, events, products, pages) stays untouched
 */

/** the nick of a deleted account stays reserved for this many seconds */
const SE_DELETED_NICK_RESERVATION = 15552000; // 180 days

/**
 * may the logged-in user manage (edit, delete) the given account?
 * all permission checks of the user management should go through here - the
 * planned super-user role (v3) only has to change this function.
 *
 * @param int $target_user_id
 * @return bool
 */
function se_can_manage_user(int $target_user_id): bool {
    return se_hasPermission('drm_acp_user');
}

/**
 * number of accounts that can manage other users, without the given one
 * @param int $exclude_user_id
 * @return int
 */
function se_count_user_managers(int $exclude_user_id = 0): int {

    global $db_user;

    $managers = $db_user->select("se_user", ["user_id", "user_drm"], [
        "user_class" => "administrator",
        "user_drm[~]" => "drm_acp_user"
    ]);

    $cnt = 0;
    foreach($managers as $manager) {
        if((int) $manager['user_id'] === $exclude_user_id) {
            continue;
        }
        if(in_array('drm_acp_user', explode('|', (string) $manager['user_drm']))) {
            $cnt++;
        }
    }
    return $cnt;
}

/**
 * overview of the data linked to an account - shown before deleting it
 * @param int $user_id
 * @return array
 */
function se_user_delete_summary(int $user_id): array {

    global $db_content, $db_user;

    return [
        'orders' => (int) $db_content->count("se_orders", ["user_id" => $user_id]),
        'orders_open' => se_count_open_orders($user_id),
        'comments' => (int) $db_content->count("se_comments", [
            "comment_author_id" => $user_id,
            "comment_type[!]" => "evc"
        ]),
        'event_confirmations' => (int) $db_content->count("se_comments", [
            "comment_author_id" => $user_id,
            "comment_type" => "evc"
        ]),
        'wishlists' => (int) $db_content->count("se_wishlists", ["user_id" => $user_id]),
        'api_keys' => (int) $db_user->count("se_api_keys", [
            "created_by" => $user_id,
            "is_active" => 1
        ])
    ];
}

/**
 * orders that are not finished yet (status 1 = received)
 * @param int $user_id
 * @return int
 */
function se_count_open_orders(int $user_id): int {

    global $db_content;

    return (int) $db_content->count("se_orders", [
        "user_id" => $user_id,
        "order_status" => 1
    ]);
}

/**
 * why an account can't be deleted - empty string if it can
 *
 * @param int $user_id
 * @param string $context 'acp' (deleted by an administrator) or 'self' (own account in the frontend)
 * @return string key of the $lang entry with the reason
 */
function se_user_delete_blocker(int $user_id, string $context): string {

    $user = se_get_userdata_by_id($user_id);

    if(!is_array($user) || $user['user_class'] === 'deleted') {
        return 'users_delete_not_found';
    }

    if($context === 'acp') {
        if(!se_can_manage_user($user_id)) {
            return 'rm_no_access';
        }
        if($user_id === (int) ($_SESSION['user_id'] ?? 0)) {
            return 'users_delete_own_account';
        }
    }

    if($context === 'self') {
        // backend accounts are deleted by another administrator
        if($user['user_class'] === 'administrator') {
            return 'msg_delete_account_admin';
        }
        // the customer would lose access to status and downloads
        if(se_count_open_orders($user_id) > 0) {
            return 'msg_delete_account_open_orders';
        }
    }

    $is_manager = $user['user_class'] === 'administrator'
        && in_array('drm_acp_user', explode('|', (string) $user['user_drm']));
    if($is_manager && se_count_user_managers($user_id) < 1) {
        return 'users_delete_last_manager';
    }

    return '';
}

/**
 * delete an account - the checks of se_user_delete_blocker() must be done before
 *
 * @param int $user_id
 * @param bool $delete_comments delete the comments instead of anonymizing them
 * @param string $deleted_by nick of the administrator or 'self'
 * @return bool
 */
function se_delete_user(int $user_id, bool $delete_comments = false, string $deleted_by = 'self'): bool {

    global $db_user, $db_content, $lang;

    $user = se_get_userdata_by_id($user_id);
    if(!is_array($user) || $user['user_class'] === 'deleted') {
        return false;
    }

    $nick = (string) $user['user_nick'];

    // avatar - the file name depends on the nick, so remove it first
    $avatar = SE_PUBLIC.'/assets/avatars/'.md5($nick).'.png';
    if($nick !== '' && is_file($avatar)) {
        unlink($avatar);
    }

    // the tombstone - every column is emptied, except the id and the
    // fields that mark the account as deleted. the column types come from
    // the installer, integer columns get 0 instead of an empty string
    $cols = [];
    include rtrim(SE_ROOT, '/').'/install/contents/se_user.php';
    $update = [];
    foreach(array_keys($user) as $column) {
        if($column === 'user_id') {
            continue;
        }
        $is_int = stripos((string) ($cols[$column] ?? ''), 'INT') === 0;
        $update[$column] = $is_int ? 0 : '';
    }
    $update['user_class'] = 'deleted';
    $update['user_nick'] = 'deleted-'.$user_id;
    $update['user_deleted_at'] = time();
    $update['user_deleted_nick'] = se_deleted_nick_hash($nick);

    $result = $db_user->update("se_user", $update, ["user_id" => $user_id]);
    if($result->rowCount() !== 1) {
        return false;
    }

    // login related data
    $db_user->delete("se_tokens", ["user_id" => $user_id]);
    se_2fa_revoke_devices($user_id);
    $db_user->update("se_api_keys", ["is_active" => 0], ["created_by" => $user_id]);

    // group memberships (ids separated by spaces)
    $groups = $db_user->select("se_groups", ["group_id", "group_user"], [
        "group_user[~]" => (string) $user_id
    ]);
    foreach($groups as $group) {
        $members = explode(' ', (string) $group['group_user']);
        $remaining = array_values(array_diff($members, [(string) $user_id]));
        if(count($remaining) !== count($members)) {
            $db_user->update("se_groups", ["group_user" => implode(' ', $remaining)], [
                "group_id" => $group['group_id']
            ]);
        }
    }

    // page authorizations (nicks separated by commas)
    if($nick !== '') {
        foreach(['se_pages', 'se_pages_cache'] as $table) {
            $pages = $db_content->select($table, ["page_id", "page_authorized_users"], [
                "page_authorized_users[~]" => $nick
            ]);
            foreach($pages as $page) {
                $authorized = explode(',', (string) $page['page_authorized_users']);
                $remaining = array_values(array_diff($authorized, [$nick]));
                if(count($remaining) !== count($authorized)) {
                    $db_content->update($table, ["page_authorized_users" => implode(',', $remaining)], [
                        "page_id" => $page['page_id']
                    ]);
                }
            }
        }
    }

    // shop - orders are kept, carts and wishlists are personal
    $db_content->delete("se_carts", ["cart_user_id" => $user_id]);
    foreach(se_get_user_wishlists($user_id) as $wishlist) {
        se_delete_wishlist((int) $wishlist['id'], $user_id);
    }

    // comments - event confirmations only make sense with a name
    $db_content->delete("se_comments", [
        "comment_author_id" => $user_id,
        "comment_type" => "evc"
    ]);
    if($delete_comments) {
        $db_content->delete("se_comments", ["comment_author_id" => $user_id]);
    } else {
        $db_content->update("se_comments", [
            "comment_author" => $lang['label_deleted_user'] ?? 'Deleted user',
            "comment_author_mail" => '',
            "comment_author_id" => null
        ], ["comment_author_id" => $user_id]);
    }

    record_log($deleted_by === 'self' ? 'user' : $deleted_by, "deleted user id: $user_id", "10");

    if(function_exists('se_do_hook')) {
        se_do_hook('backend', 'user.deleted', [
            'user_id' => $user_id,
            'deleted_by' => $deleted_by
        ]);
    }

    return true;
}

/**
 * hash of a deleted nick - keeps it reserved without storing the nick itself
 * @param string $nick
 * @return string
 */
function se_deleted_nick_hash(string $nick): string {
    if(trim($nick) === '') {
        return '';
    }
    return hash_hmac('sha256', strtolower(trim($nick)), se_get_site_secret());
}

/**
 * is the nick still reserved by a recently deleted account?
 * @param string $nick
 * @return bool
 */
function se_deleted_nick_reserved(string $nick): bool {

    global $db_user;

    $hash = se_deleted_nick_hash($nick);
    if($hash === '') {
        return false;
    }

    return $db_user->has("se_user", [
        "user_deleted_nick" => $hash,
        "user_deleted_at[>]" => time() - SE_DELETED_NICK_RESERVATION
    ]);
}
