<?php

/**
 * user settings in /admin/settings/user/, markup in acp/templates/settings/user.twig
 * the sections are saved by the settings data-writer (save_settings=<section>)
 *
 * global variables
 * @var array $se_settings
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

require_once __DIR__ . '/functions.php';

// the settings are stored html-encoded, the form helpers print them as they are
$setting = static fn(string $key): string => (string) ($se_settings[$key] ?? '');

$inputs = [];

/* registration and login */

foreach([
    'userregistration' => 'label_settings_allow_registration',
    'showloginform' => 'label_settings_show_login',
    'user_unlock_by_admin' => 'label_settings_new_user_unlock_by_admin'
] as $key => $label) {
    $inputs[$key] = se_print_form_input([
        "input_name" => "prefs_$key",
        "input_value" => "yes",
        "label" => $lang[$label],
        "type" => "checkbox",
        "status" => $setting($key) == "yes" ? 'checked' : ''
    ]);
}

// required fields, stored as json list of se_user columns
$required_fields = json_decode(html_entity_decode($setting('required_fields_registration')), true);
$required_fields = is_array($required_fields) ? $required_fields : [];
$registration_fields = [];
foreach(se_settings_registration_fields() as $field) {
    $registration_fields[] = [
        'name' => $field,
        'checked' => in_array($field, $required_fields)
    ];
}

/* blacklist and session */

$inputs['blacklist_usernames'] = se_print_form_input([
    "input_name" => "prefs_blacklist_usernames",
    "input_value" => $setting('blacklist_usernames'),
    "label" => 'Blacklist (user names)',
    "type" => "textarea"
]);

$inputs['acp_session_lifetime'] = se_print_form_input([
    "input_name" => "prefs_acp_session_lifetime",
    "input_value" => $setting('acp_session_lifetime'),
    "label" => $lang['label_settings_acp_session_lifetime'],
    "type" => "number",
    "min" => SE_ACP_IDLE_MIN,
    "max" => SE_ACP_IDLE_MAX,
    "form_text" => SE_ACP_IDLE_MIN.' - '.SE_ACP_IDLE_MAX
]);

/* comments and votings */

$inputs['comments_mode'] = se_print_form_input([
    "input_name" => "prefs_comments_mode",
    "input_value" => $setting('comments_mode'),
    "radios" => [
        "label_settings_comments_mode_1" => '1',
        "label_settings_comments_mode_2" => '2',
        "label_settings_comments_mode_3" => '3'
    ],
    "type" => "radios"
]);

// stored in seconds, entered in days (see the "days" type in se_settings_sections())
$inputs['comments_autoclose'] = se_print_form_input([
    "input_name" => "prefs_comments_autoclose",
    "input_value" => (string) (int) round((int) $setting('comments_autoclose') / 86400),
    "label" => $lang['label_settings_comments_autoclose_days'],
    "type" => "number"
]);

$inputs['default_votings'] = se_print_form_input([
    "input_name" => "prefs_posts_default_votings",
    "input_value" => $setting('posts_default_votings'),
    "label" => $lang['label_votings'],
    "options" => [
        $lang['label_settings_votings_off'] => '1',
        $lang['label_settings_votings_on_registered'] => '2',
        $lang['label_settings_votings_on_global'] => '3'
    ],
    "type" => "select"
]);

echo $twig->render('settings/user.twig', [
    'title' => $lang['nav_btn_settings'].' / '.$lang['nav_btn_user'],
    'title_icon' => $icon['gear'],
    'settings_nav' => se_settings_nav('user'),
    'writer_uri' => '/admin-xhr/settings/general/write/',
    // titles and help texts of the sections come from the docs
    'help' => se_docs_sections('08-00-settings.md', [
        'user-registration', 'user-required-fields', 'user-blacklist', 'user-session',
        'user-comments', 'user-votings'
    ]),
    'inputs' => $inputs,
    'registration_fields' => $registration_fields
]);
