<?php

/**
 * @var object $db_user
 * @var object $smarty
 * @var array $lang
 * @var string $cache_id
 * @var string $hidden_csrf_token
 */

$unlock_code = $_GET['code'] ?? '';

// codes are generated as 32 hex chars (see se_handle_failed_logins())
if(is_string($unlock_code) && preg_match('/^[a-f0-9]{32}$/', $unlock_code)) {

    if(isset($_POST['unlock_account'])) {

        // CSRF token is checked in bootstrap.php - reset unlock code, failed
        // logins and the temporary lock. Only a hash of the code is stored,
        // valid until user_unlock_expires.
        $unlock_data = $db_user->update("se_user", [
            "user_failed_logins" => 0,
            "user_unlock_code" => '',
            "user_unlock_expires" => 0,
            "user_locked_until" => 0
        ], [
            "user_unlock_code" => hash('sha256', $unlock_code),
            "user_unlock_expires[>]" => time()
        ]);

        if ($unlock_data->rowCount() > 0) {
            $smarty->assign('page_content', $lang['account_unlocked']);
        }

    } else {

        // opening the link only shows a button - link previews and mail
        // scanners fetch the link per GET and must not unlock the account
        $unlock_form = '<form method="post" action="?code='.$unlock_code.'">';
        $unlock_form .= $hidden_csrf_token;
        $unlock_form .= '<button type="submit" name="unlock_account" class="btn btn-primary">'.htmlspecialchars($lang['button_unlock_account'], ENT_QUOTES).'</button>';
        $unlock_form .= '</form>';
        $smarty->assign('page_content', $unlock_form);
    }

}
