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
