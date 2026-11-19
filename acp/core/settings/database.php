<?php

/**
 * database settings in /admin/settings/database/ (SQLite only),
 * markup in acp/templates/settings/database.twig - WAL mode and compacting
 * are handled by the settings data-writer (update_database, vacuum_database)
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var string $db_type
 * @var object $db_content
 * @var object $db_user
 * @var object $db_posts
 * @var object $twig
 * @var array $se_db_state app/database.php - mode 'single' or 'legacy'
 * @var array $se_db_legacy_files app/database.php - the files used up to 2.3
 */

require_once __DIR__ . '/functions.php';

$wal_active = false;
$db_files = [];
$legacy_files = [];

if ($db_type === 'sqlite') {
    // the journal mode lives in the database file itself, so read it back directly
    // instead of trusting the stored preference - that way the toggle always
    // reflects reality, even if a restored backup or manual change reset it.
    $wal_active = strtolower((string) $db_content->pdo->query('PRAGMA journal_mode')->fetchColumn()) === 'wal';

    // deleted data leaves free pages in the file that SQLite reuses,
    // only VACUUM gives that space back to the disk
    // keyed by path - since 2.4 all three point to the same file
    $db_connections = [
        CONTENT_DB => $db_content,
        USER_DB => $db_user,
        POSTS_DB => $db_posts
    ];
    foreach ($db_connections as $db_path => $db_conn) {
        $db_name = basename($db_path);
        $page_size = (int) $db_conn->pdo->query('PRAGMA page_size')->fetchColumn();
        $free_pages = (int) $db_conn->pdo->query('PRAGMA freelist_count')->fetchColumn();
        $db_files[] = [
            'name' => $db_name,
            'size' => readable_filesize(filesize($db_path)),
            'free' => readable_filesize($page_size * $free_pages)
        ];
    }

    // the files used up to 2.3 - only offered for deletion once the
    // merged file is in use, never while the site still runs on them
    if (($se_db_state['mode'] ?? '') === 'single') {
        foreach (se_db_legacy_files_left($se_db_legacy_files) as $legacy_path) {
            $legacy_files[] = [
                'name' => basename($legacy_path),
                'size' => readable_filesize(filesize($legacy_path))
            ];
        }
    }
}

echo $twig->render('settings/database.twig', [
    'title' => $lang['nav_btn_settings'].' / '.$lang['nav_btn_database'],
    'title_icon' => $icon['gear'],
    'settings_nav' => se_settings_nav('database'),
    'writer_uri' => '/admin-xhr/settings/database/write/',
    // titles and help texts of the sections come from the docs
    'help' => se_docs_sections('08-00-settings.md', ['database-wal', 'database-vacuum', 'database-legacy']),
    'is_sqlite' => ($db_type === 'sqlite'),
    'wal_input' => se_print_form_input([
        "input_name" => "prefs_wal_mode",
        "input_value" => "yes",
        "label" => $lang['label_settings_wal_mode_toggle'],
        "type" => "checkbox",
        "status" => $wal_active ? 'checked' : ''
    ]),
    'db_files' => $db_files,
    'legacy_files' => $legacy_files
]);
