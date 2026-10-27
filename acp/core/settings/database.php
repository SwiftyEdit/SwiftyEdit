<?php

/**
 * @var array $se_settings
 * @var array $icon
 * @var array $lang
 * @var string $db_type
 * @var object $db_content
 * @var object $db_user
 * @var object $db_posts
 */

error_reporting(E_ALL ^E_WARNING ^E_NOTICE ^E_DEPRECATED);
echo '<div class="subHeader d-flex align-items-center">'.$icon['gear'].' '.$lang['nav_btn_settings'].' / '.$lang['nav_btn_database'].'</div>';

if ($db_type !== 'sqlite') {
    echo '<div class="alert alert-info">'.$lang['label_settings_wal_mode_mysql_hint'].'</div>';
    return;
}

// the journal mode lives in the database file itself, so read it back directly
// instead of trusting the stored preference - that way the toggle always
// reflects reality, even if a restored backup or manual change reset it.
$current_journal_mode = strtolower((string) $db_content->pdo->query('PRAGMA journal_mode')->fetchColumn());
$wal_active = $current_journal_mode === 'wal';

$writer_uri = '/admin-xhr/settings/database/write/';

echo '<div class="card">';
echo '<div class="card-body">';

$input_wal_mode = [
    "input_name" => "prefs_wal_mode",
    "input_value" => $se_settings['wal_mode'],
    "label" => $lang['label_settings_wal_mode_toggle'],
    "type" => "checkbox",
    "status" => $wal_active ? 'checked' : ''
];

echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';
echo se_print_form_input($input_wal_mode);
echo '<button type="submit" class="btn btn-primary" name="update_database" value="update">'.$lang['btn_update'].'</button>';
echo '</form>';

echo '</div>'; // card-body
echo '</div>'; // card

// compact (VACUUM) the database files - deleted data leaves free pages in the
// file that SQLite reuses, but only VACUUM gives that space back to the disk
$db_files = [
    'content.sqlite3' => [$db_content, CONTENT_DB],
    'user.sqlite3' => [$db_user, USER_DB],
    'posts.sqlite3' => [$db_posts, POSTS_DB]
];

echo '<div class="card mt-3">';
echo '<div class="card-body">';
echo '<h5 class="card-title">'.$lang['label_settings_vacuum'].'</h5>';
echo '<p>'.$lang['label_settings_vacuum_description'].'</p>';

echo '<table class="table table-sm">';
echo '<tr><th>'.$lang['label_settings_vacuum_file'].'</th><th class="text-end">'.$lang['label_settings_vacuum_size'].'</th><th class="text-end">'.$lang['label_settings_vacuum_free'].'</th></tr>';
foreach ($db_files as $db_name => [$db_conn, $db_path]) {
    $page_size = (int) $db_conn->pdo->query('PRAGMA page_size')->fetchColumn();
    $free_pages = (int) $db_conn->pdo->query('PRAGMA freelist_count')->fetchColumn();
    echo '<tr>';
    echo '<td><code>'.$db_name.'</code></td>';
    echo '<td class="text-end">'.readable_filesize(filesize($db_path)).'</td>';
    echo '<td class="text-end">'.readable_filesize($page_size * $free_pages).'</td>';
    echo '</tr>';
}
echo '</table>';

echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend" hx-confirm="'.htmlspecialchars($lang['label_settings_vacuum_confirm'], ENT_QUOTES).'">';
echo '<button type="submit" class="btn btn-default" name="vacuum_database" value="vacuum">'.$lang['btn_settings_vacuum'].'</button>';
echo '</form>';

echo '</div>'; // card-body
echo '</div>'; // card
