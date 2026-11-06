<?php

/**
 * Data for the dashboard: the activity grid and history (se_logs, se_orders,
 * se_user), the review card and the log page.
 *
 * Days are built in PHP from the unix timestamps with the current timezone,
 * that avoids the differences between SQLite and MySQL date functions.
 */

/**
 * The areas of the activity grid in display order. "content" areas count as
 * edits, the others are events read from their own tables.
 */
function se_dashboard_activity_types(): array {

    global $lang;

    return [
        'page' => ['label' => $lang['nav_btn_pages'], 'group' => 'content', 'url' => '/admin/pages/', 'new_url' => '/admin/pages/new/', 'new_text' => $lang['btn_new'], 'new_icon' => 'plus'],
        'snippet' => ['label' => $lang['nav_btn_snippets'], 'group' => 'content', 'url' => '/admin/snippets/', 'new_url' => '/admin/snippets/new/', 'new_text' => $lang['btn_new'], 'new_icon' => 'plus'],
        'post' => ['label' => $lang['nav_btn_blog'], 'group' => 'content', 'url' => '/admin/blog/', 'new_url' => '/admin/blog/new/', 'new_text' => $lang['btn_new'], 'new_icon' => 'plus'],
        'product' => ['label' => $lang['nav_btn_products'], 'group' => 'content', 'url' => '/admin/shop/', 'new_url' => '/admin/shop/new/', 'new_text' => $lang['btn_new'], 'new_icon' => 'plus'],
        'event' => ['label' => $lang['nav_btn_events'], 'group' => 'content', 'url' => '/admin/events/', 'new_url' => '/admin/events/new/', 'new_text' => $lang['btn_new'], 'new_icon' => 'plus'],
        'media' => ['label' => $lang['nav_btn_uploads'], 'group' => 'content', 'url' => '/admin/uploads/', 'new_url' => '/admin/uploads/', 'new_text' => $lang['btn_upload'], 'new_icon' => 'upload'],
        'order' => ['label' => $lang['nav_btn_orders'], 'group' => 'event', 'url' => '/admin/shop/orders/', 'new_url' => '', 'new_text' => '', 'new_icon' => ''],
        'user' => ['label' => $lang['nav_btn_user'], 'group' => 'event', 'url' => '/admin/users/', 'new_url' => '/admin/users/new/', 'new_text' => $lang['btn_new'], 'new_icon' => 'plus'],
    ];
}

/**
 * The date in the format from the settings
 *
 * @param int $time unix timestamp
 * @param bool $with_weekday puts the weekday in front, e.g. "Wednesday, 21.10.2026"
 */
function se_dashboard_date_label(int $time, bool $with_weekday = false): string {

    global $lang, $se_settings;

    $date = date((string) ($se_settings['dateformat'] ?? 'Y-m-d'), $time);

    return $with_weekday ? $lang['d0'.date('N', $time)].', '.$date : $date;
}

/**
 * All activity since $since, oldest first: content changes and changes to
 * users by an administrator from se_logs, orders and new users from their own
 * tables.
 *
 * @return array list of time, type, ident, id, key, title, action, source (users: also gone)
 */
function se_dashboard_activity_entries(int $since): array {

    global $db_content, $db_user, $se_settings;

    $entries = [];

    // after a manual update the columns are missing until /install/ has run,
    // the dashboard stays usable and shows no content activity until then
    try {
        $logs = $db_content->select("se_logs", ["time", "source", "action", "object_type", "object_id", "object_key", "object_label"], [
            "time[>=]" => (string) $since,
            "object_type[!]" => null,
            "ORDER" => ["id" => "ASC"]
        ]);
    } catch (\Throwable $e) {
        $logs = [];
    }

    foreach ($logs as $log) {
        $id = $log['object_id'] !== null ? (int) $log['object_id'] : null;
        $key = (string) $log['object_key'];
        $entries[] = [
            'time' => (int) $log['time'],
            'type' => (string) $log['object_type'],
            'ident' => $id ?? $key,
            'id' => $id,
            'key' => $key,
            'title' => (string) $log['object_label'],
            'action' => (string) $log['action'],
            'source' => (string) $log['source']
        ];
    }

    $orders = $db_content->select("se_orders", ["id", "order_nbr", "order_time", "order_price_total"], [
        "order_time[>=]" => $since
    ]);
    $currency = (string) ($se_settings['posts_products_default_currency'] ?? '');
    foreach ($orders as $order) {
        $price = number_format((float) $order['order_price_total'], 2, ',', '.');
        $entries[] = [
            'time' => (int) $order['order_time'],
            'type' => 'order',
            'ident' => (int) $order['id'],
            'id' => (int) $order['id'],
            'key' => '',
            'title' => trim('#'.$order['order_nbr'].', '.$price.' '.$currency),
            'action' => 'create',
            'source' => ''
        ];
    }

    // user_registerdate is a VARCHAR holding a timestamp, deleted accounts have it emptied
    $users = $db_user->select("se_user", ["user_id", "user_nick", "user_registerdate"], [
        "user_registerdate[>=]" => (string) $since,
        "user_class[!]" => "deleted"
    ]);
    foreach ($users as $user) {
        if (!is_numeric($user['user_registerdate'])) {
            continue;
        }
        $entries[] = [
            'time' => (int) $user['user_registerdate'],
            'type' => 'user',
            'ident' => (int) $user['user_id'],
            'id' => (int) $user['user_id'],
            'key' => '',
            'title' => (string) $user['user_nick'],
            'action' => 'create',
            'source' => ''
        ];
    }

    // the log only holds the id of a user, the name is read here: a deleted
    // account is shown as deleted-N and has no working edit form
    $user_ids = [];
    foreach ($entries as $entry) {
        if ($entry['type'] === 'user' && $entry['title'] === '') {
            $user_ids[] = $entry['id'];
        }
    }
    if ($user_ids !== []) {
        $accounts = [];
        foreach ($db_user->select("se_user", ["user_id", "user_nick", "user_class"], ["user_id" => array_values(array_unique($user_ids))]) as $account) {
            $accounts[(int) $account['user_id']] = $account;
        }
        foreach ($entries as &$entry) {
            if ($entry['type'] === 'user' && $entry['title'] === '') {
                $account = $accounts[$entry['id']] ?? null;
                $entry['gone'] = $account === null || $account['user_class'] === 'deleted';
                $entry['title'] = $entry['gone'] ? 'deleted-'.$entry['id'] : (string) $account['user_nick'];
            }
        }
        unset($entry);
    }

    usort($entries, static fn($a, $b) => $a['time'] <=> $b['time']);

    return $entries;
}

/**
 * Where a row in the history leads to. Users and media files only open by POST,
 * see the hidden forms in dashboard/activity.twig.
 */
function se_dashboard_item_link(string $type, ?int $id): string {

    return match ($type) {
        'page' => '/admin/pages/edit/'.$id.'/',
        'snippet' => '/admin/snippets/edit/'.$id.'/',
        'post' => '/admin/blog/edit/'.$id.'/',
        'product' => '/admin/shop/edit/'.$id.'/',
        'event' => '/admin/events/edit/'.$id.'/',
        'order' => '/admin/shop/orders/',
        default => ''
    };
}

/**
 * Everything dashboard/activity.twig needs.
 *
 * @param string $tab overview or history
 * @param string $day selected day (Y-m-d) or empty
 * @param string $type selected area or "all"
 */
function se_dashboard_activity(string $tab, string $day, string $type): array {

    global $lang;

    $types = se_dashboard_activity_types();
    $since = (int) strtotime('today -29 days');
    $entries = se_dashboard_activity_entries($since);

    // a deleted item keeps no link, also on the days before it was deleted
    $last_action = [];
    foreach ($entries as $entry) {
        $last_action[$entry['type'].'|'.$entry['ident']] = $entry['action'];
    }

    // one item per day: saving several times counts once
    $items = [];
    foreach ($entries as $entry) {
        if (!isset($types[$entry['type']])) {
            continue;
        }
        $entry_day = date('Y-m-d', $entry['time']);
        $item_key = $entry_day.'|'.$entry['type'].'|'.$entry['ident'];
        if (!isset($items[$item_key])) {
            $items[$item_key] = $entry + ['day' => $entry_day, 'saves' => 0];
        }
        $items[$item_key]['time'] = $entry['time'];
        $items[$item_key]['action'] = $entry['action'];
        // several people on one day: the last one counts, like for the time
        if ($entry['source'] !== '') {
            $items[$item_key]['source'] = $entry['source'];
        }
        if ($entry['title'] !== '') {
            $items[$item_key]['title'] = $entry['title'];
        }
        if ($entry['action'] !== 'delete') {
            $items[$item_key]['saves']++;
        }
    }

    $counts = [];
    foreach ($items as $item) {
        $counts[$item['day']][$item['type']] = ($counts[$item['day']][$item['type']] ?? 0) + 1;
    }

    // the 30 days of the grid
    $days = [];
    for ($i = 0; $i < 30; $i++) {
        $time = (int) strtotime("+$i days", $since);
        $days[] = [
            'key' => date('Y-m-d', $time),
            'num' => date('j', $time),
            'month' => ($i === 0 || date('j', $time) === '1') ? mb_substr($lang['m'.date('m', $time)], 0, 3) : '',
            'weekend' => in_array(date('w', $time), ['0', '6'], true),
            'label' => se_dashboard_date_label($time, true)
        ];
    }

    // selected day only if it is within the grid
    if (!in_array($day, array_column($days, 'key'), true)) {
        $day = '';
    }
    $day_label = '';
    foreach ($days as $grid_day) {
        if ($grid_day['key'] === $day) {
            $day_label = $grid_day['label'];
        }
    }

    // only areas with activity get a row, each row is measured by its busiest day
    $rows = [];
    foreach ($types as $type_key => $type_data) {
        $per_day = [];
        foreach ($days as $grid_day) {
            $per_day[$grid_day['key']] = $counts[$grid_day['key']][$type_key] ?? 0;
        }
        $sum = array_sum($per_day);
        if ($sum < 1) {
            continue;
        }
        $max = max($per_day);
        $cells = [];
        foreach ($days as $grid_day) {
            $n = $per_day[$grid_day['key']];
            $cells[] = [
                'day' => $grid_day['key'],
                'n' => $n,
                'level' => $n > 0 ? max(1, (int) ceil(4 * $n / $max)) : 0,
                'label' => $type_data['label'].', '.$grid_day['label'].': '.$n
            ];
        }
        $rows[] = ['type' => $type_key, 'label' => $type_data['label'], 'group' => $type_data['group'], 'sum' => $sum, 'cells' => $cells];
    }

    // totals of the whole range for the headline
    $total_edits = 0;
    $total_orders = 0;
    foreach ($rows as $row) {
        if ($row['group'] === 'content') {
            $total_edits += $row['sum'];
        } elseif ($row['type'] === 'order') {
            $total_orders = $row['sum'];
        }
    }

    // detail panel: the selected day or the whole range
    $detail_total = 0;
    $detail = [];
    foreach ($rows as $row) {
        $n = $day !== '' ? ($counts[$day][$row['type']] ?? 0) : $row['sum'];
        if ($n < 1) {
            continue;
        }
        if ($row['group'] === 'content') {
            $detail_total += $n;
        }
        $detail[] = ['type' => $row['type'], 'group' => $row['group'], 'n' => $n] + $types[$row['type']];
    }
    foreach ($detail as &$detail_row) {
        $detail_row['percent'] = ($detail_row['group'] === 'content' && $detail_total > 0) ? (int) round(100 * $detail_row['n'] / $detail_total) : null;
    }
    unset($detail_row);

    if (!isset($types[$type])) {
        $type = 'all';
    }

    return [
        'tab' => $tab,
        'day' => $day,
        'day_label' => $day_label,
        'type' => $type,
        'days' => $days,
        'rows' => $rows,
        'total_edits' => $total_edits,
        'total_orders' => $total_orders,
        'detail' => $detail,
        'detail_total' => $detail_total,
        'open_all_url' => $types[$type]['url'] ?? ''
    ] + se_dashboard_history($items, $last_action, $types, $day, $type);
}

/**
 * The history tab: one row per item and day, newest first, grouped by day.
 *
 * @return array{groups: array, limited: bool}
 */
function se_dashboard_history(array $items, array $last_action, array $types, string $day, string $type): array {

    global $lang;

    // a busy shop has thousands of rows in 30 days, nobody scrolls through them here
    $limit = 300;

    $shown = array_filter($items, static fn($item) => ($day === '' || $item['day'] === $day) && ($type === 'all' || $item['type'] === $type));
    usort($shown, static fn($a, $b) => $b['time'] <=> $a['time']);
    $limited = count($shown) > $limit;

    $groups = [];
    foreach (array_slice($shown, 0, $limit) as $item) {

        $note = '';
        if ($item['action'] === 'delete') {
            $note = $lang['status_deleted'];
        } elseif ($item['saves'] > 1) {
            $note = str_replace('{n}', (string) $item['saves'], $lang['dashboard_activity_saved_times']);
        }

        $is_deleted = !empty($item['gone']) || ($last_action[$item['type'].'|'.$item['ident']] ?? '') === 'delete';

        $groups[$item['day']]['label'] ??= se_dashboard_date_label($item['time'], true);
        $groups[$item['day']]['items'][] = [
            'type' => $item['type'],
            'type_label' => $types[$item['type']]['label'],
            // a snippet without a title still shows its key, others need something to recognize them
            'title' => ($item['title'] === '' && $item['key'] === '' && $item['id'] !== null) ? '#'.$item['id'] : $item['title'],
            // the file name is already the title of a media item
            'key' => $item['type'] === 'snippet' ? $item['key'] : '',
            'source' => $item['source'],
            'time' => date('H:i', $item['time']),
            'note' => $note,
            'link' => $is_deleted ? '' : se_dashboard_item_link($item['type'], $item['id']),
            'post_user' => (!$is_deleted && $item['type'] === 'user') ? $item['id'] : null,
            'post_file' => (!$is_deleted && $item['type'] === 'media') ? $item['key'] : null
        ];
    }

    return ['groups' => $groups, 'limited' => $limited, 'limit' => $limit];
}

/**
 * Security entries for the "To review" card. Single cases are normal (expired
 * forms, typos, bots), so only what deserves a look is summarized here, the
 * rest stays in the log:
 * - CSRF with a logged-in user, per day: always, that can be an attack on an account
 * - CSRF without a login, per path: from 3 cases in 7 days, can be a broken form
 * - account locks: always
 * - failed logins: from 10 in 7 days
 *
 * The log takes at most 20 entries per hour and kind (see se_csrf_failed() and
 * se_log_failed_login()), so all counts are minimum values.
 *
 * @return array{csrf_users: array, csrf_paths: array, locks: array, logins: array|null}
 */
function se_dashboard_security_summary(): array {

    global $db_content;

    $summary = ['csrf_users' => [], 'csrf_paths' => [], 'locks' => [], 'logins' => null];

    try {
        $rows = $db_content->select("se_logs", ["time", "action", "source", "entry"], [
            "action" => ['csrf', 'login_failed', 'login_locked'],
            "time[>=]" => (string) (time() - 30 * 86400),
            "ORDER" => ["id" => "DESC"]
        ]);
    } catch (\Throwable $e) {
        return $summary;
    }

    $week_ago = time() - 7 * 86400;
    $user_days = [];
    $paths = [];
    $locks = [];
    $known = 0;
    $unknown = 0;

    foreach ($rows as $row) {
        $time = (int) $row['time'];
        $is_system = $row['source'] === 'system';

        if ($row['action'] === 'csrf' && !$is_system) {
            $day = date('Y-m-d', $time);
            $user_days[$day] ??= ['date' => se_dashboard_date_label($time), 'time' => date('H:i', $time), 'n' => 0];
            $user_days[$day]['n']++;
        }

        if ($time < $week_ago) {
            continue;
        }

        if ($row['action'] === 'csrf' && $is_system) {
            $path = str_replace('CSRF token missing or invalid: ', '', (string) $row['entry']);
            $paths[$path] = ($paths[$path] ?? 0) + 1;
        } elseif ($row['action'] === 'login_locked') {
            $locks[$row['source']] = ($locks[$row['source']] ?? 0) + 1;
        } elseif ($row['action'] === 'login_failed') {
            $is_system ? $unknown++ : $known++;
        }
    }

    $summary['csrf_users'] = array_slice(array_values($user_days), 0, 3);

    $paths = array_filter($paths, static fn($n) => $n >= 3);
    arsort($paths);
    foreach (array_slice($paths, 0, 3, true) as $path => $n) {
        $summary['csrf_paths'][] = ['path' => (string) $path, 'n' => $n];
    }

    arsort($locks);
    foreach (array_slice($locks, 0, 3, true) as $nick => $n) {
        $summary['locks'][] = ['nick' => (string) $nick, 'n' => $n];
    }

    if ($known + $unknown >= 10) {
        $summary['logins'] = ['total' => $known + $unknown, 'known' => $known, 'unknown' => $unknown];
    }

    return $summary;
}

/**
 * Is a newer stable build available? Asks swiftyedit.net at most every six
 * hours, not on every dashboard load.
 *
 * @return string the available build, empty if there is none or it is unknown
 */
function se_dashboard_available_update(string $installed_build): string {

    $cache_file = SE_CONTENT.'/cache/update_check.json';
    $cached = is_file($cache_file) ? json_decode((string) file_get_contents($cache_file), true) : null;

    if (is_array($cached) && (int) ($cached['time'] ?? 0) > time() - 21600) {
        $stable_build = (string) ($cached['build'] ?? '');
    } else {
        require_once __DIR__.'/../update/functions.php';
        $stable_build = '';
        foreach (se_update_channels(get_remote_versions()) ?? [] as $channel) {
            if ($channel['name'] === 'stable') {
                $stable_build = $channel['build'];
            }
        }
        @file_put_contents($cache_file, json_encode(['time' => time(), 'build' => $stable_build]));
    }

    // builds are compared as strings, see se_update_channels()
    return $installed_build < $stable_build ? $stable_build : '';
}

/**
 * Entries of the log page.
 *
 * @param string $filter all, content or security
 * @param int $page starting at 0
 * @return array{entries: array, has_next: bool}
 */
function se_dashboard_log_entries(string $filter, int $page, int $per_page): array {

    global $db_content;

    $where = [
        "ORDER" => ["id" => "DESC"],
        "LIMIT" => [$page * $per_page, $per_page + 1]
    ];
    if ($filter === 'content') {
        $where["object_type[!]"] = null;
    } elseif ($filter === 'security') {
        $where["action"] = ['csrf', 'login_failed', 'login_locked'];
    }

    $rows = $db_content->select("se_logs", "*", $where);
    $has_next = count($rows) > $per_page;

    $entries = [];
    foreach (array_slice($rows, 0, $per_page) as $row) {
        // entries without an action come from record_log() and may contain
        // HTML or entities - reduced to plain text, Twig escapes the result
        $text = (string) $row['entry'];
        if (empty($row['action'])) {
            $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
        }
        $entries[] = [
            'time' => se_format_datetime($row['time']),
            'source' => (string) $row['source'],
            'text' => $text,
            'priority' => (int) $row['priority']
        ];
    }

    return ['entries' => $entries, 'has_next' => $has_next];
}
