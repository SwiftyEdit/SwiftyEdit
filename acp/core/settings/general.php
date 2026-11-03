<?php

/**
 * general settings in /admin/settings/, markup in acp/templates/settings/general.twig
 * the sections are saved by the settings data-writer (save_settings=<section>)
 *
 * global variables
 * @var array $se_settings
 * @var array $icon
 * @var array $lang
 * @var string $se_branding_path
 * @var object $twig
 */

require_once __DIR__ . '/branding-widget.php';
require_once __DIR__ . '/functions.php';

$writer_uri = '/admin-xhr/settings/general/write/';
$branding_upload_uri = '/admin-xhr/settings/general/upload/';

// the settings are stored html-encoded, the form helpers print them as they are
$setting = static fn(string $key): string => (string) ($se_settings[$key] ?? '');

$inputs = [];

/* website */

$inputs['page_name'] = se_print_form_input([
    "input_name" => "prefs_pagename",
    "input_value" => $setting('pagename'),
    "label" => $lang['label_settings_page_name'],
    "type" => "text"
]);
$inputs['page_title'] = se_print_form_input([
    "input_name" => "prefs_pagetitle",
    "input_value" => $setting('pagetitle'),
    "label" => $lang['label_settings_page_title'],
    "type" => "text"
]);
$inputs['page_subtitle'] = se_print_form_input([
    "input_name" => "prefs_pagesubtitle",
    "input_value" => $setting('pagesubtitle'),
    "label" => $lang['label_settings_page_subtitle'],
    "type" => "text"
]);
$inputs['page_description'] = se_print_form_input([
    "input_name" => "prefs_pagedescription",
    "input_value" => $setting('pagedescription'),
    "label" => $lang['label_settings_page_description'],
    "type" => "textarea"
]);

/* organization (structured data) */

$settings_options = se_settings_options();

$inputs['org_type'] = se_print_form_input([
    "input_name" => "prefs_org_type",
    "input_value" => $setting('org_type') ?: 'Organization',
    "label" => $lang['label_type'],
    "options" => array_combine($settings_options['prefs_org_type'], $settings_options['prefs_org_type']),
    "type" => "select"
]);
foreach([
    'org_name' => $lang['label_name'],
    'org_street' => $lang['label_street'],
    'org_street_nbr' => $lang['label_nr'],
    'org_zip' => $lang['label_zip'],
    'org_city' => $lang['label_town'],
    'org_phone' => $lang['label_tel'],
    'org_email' => $lang['label_mail']
] as $key => $label) {
    $inputs[$key] = se_print_form_input([
        "input_name" => "prefs_$key",
        "input_value" => $setting($key),
        "label" => $label,
        "type" => "text"
    ]);
}
$inputs['org_country'] = se_print_form_input([
    "input_name" => "prefs_org_country",
    "input_value" => $setting('org_country'),
    "label" => $lang['label_country'],
    "options" => ['–' => ''] + array_flip(se_get_countries_options()),
    "type" => "select"
]);
$inputs['org_sameas'] = se_print_form_input([
    "input_name" => "prefs_org_sameas",
    "input_value" => $setting('org_sameas'),
    "label" => $lang['label_settings_org_sameas'],
    "type" => "textarea"
]);

/* page versions */

$inputs['nbr_page_versions'] = se_print_form_input([
    "input_name" => "prefs_nbr_page_versions",
    "input_value" => $setting('nbr_page_versions'),
    "label" => $lang['label_settings_nbr_page_versions'],
    "type" => "number"
]);

/* logo and icons, uploaded and deleted right away */

$branding_field = static function(string $target, string $label, string $value, int $max_width, int $max_height, array $extra = [], string $hint = '', string $unchanged = '') use ($se_branding_path, $branding_upload_uri, $writer_uri, $lang): string {
    return se_render_branding_field(
        $target,
        $label,
        $value,
        $se_branding_path,
        $branding_upload_uri,
        $writer_uri,
        $_SESSION['token'],
        $lang['btn_delete'],
        $lang['msg_confirm_delete_file'],
        $max_width,
        $max_height,
        $extra,
        $hint,
        $unchanged
    );
};

$branding_fields = [
    $branding_field('logo', 'Page Logo', $setting('pagelogo'), (int) $setting('maximagewidth'), (int) $setting('maximageheight'), [], '', $setting('uploads_remain_unchanged')),
    $branding_field('thumbnail', $lang['label_pages_thumbnail'], $setting('pagethumbnail'), (int) $setting('maximagewidth'), (int) $setting('maximageheight'), [], '', $setting('uploads_remain_unchanged')),
    $branding_field('favicon', 'Favicon', $setting('pagefavicon'), 0, 0, [
        'manifest_name' => $setting('pagetitle'),
        'manifest_short_name' => $setting('pagename')
    ], 'PNG, JPG or WebP, at least 512×512px. A full icon set (incl. Apple Touch Icon) is generated automatically.')
];

/* image sizes and uploads */

foreach([
    'max_img_width' => ['maximagewidth', 'Max Image Width'],
    'max_img_height' => ['maximageheight', 'Max Image Height'],
    'max_tmb_width' => ['maxtmbwidth', 'Max Thumbnail Width'],
    'max_tmb_height' => ['maxtmbheight', 'Max Thumbnail Height']
] as $name => [$key, $label]) {
    $inputs[$name] = se_print_form_input([
        "input_name" => "prefs_$key",
        "input_value" => $setting($key),
        "label" => $label,
        "input_group_end_text" => "px",
        "type" => "number"
    ]);
}

$inputs['max_filesize'] = se_print_form_input([
    "input_name" => "prefs_maxfilesize",
    "input_value" => $setting('maxfilesize'),
    "label" => $lang['label_settings_max_filesize'],
    "type" => "number"
]);
$inputs['uploads_unchanged'] = se_print_form_input([
    "input_name" => "prefs_uploads_remain_unchanged",
    "input_value" => "yes",
    "label" => $lang['label_settings_uploads_remain_unchanged'],
    "type" => "checkbox",
    "status" => $setting('uploads_remain_unchanged') == "yes" ? 'checked' : ''
]);

/* domain */

$inputs['cms_domain'] = se_print_form_input([
    "input_name" => "prefs_cms_domain",
    "input_value" => $setting('cms_domain'),
    "label" => $lang['label_settings_cms_domain'],
    "type" => "text"
]);
$inputs['cms_ssl_domain'] = se_print_form_input([
    "input_name" => "prefs_cms_ssl_domain",
    "input_value" => $setting('cms_ssl_domain'),
    "label" => $lang['label_settings_cms_domain_ssl'],
    "type" => "text"
]);
$inputs['cms_base'] = se_print_form_input([
    "input_name" => "prefs_cms_base",
    "input_value" => $setting('cms_base'),
    "label" => $lang['label_settings_cms_base'],
    "type" => "text"
]);
$inputs['login_slug'] = se_print_form_input([
    "input_name" => "prefs_login_slug",
    "input_value" => $setting('login_slug'),
    "label" => $lang['label_settings_login_slug'],
    "input_group_start_text" => "/admin/",
    "type" => "text"
]);

/* date and time, the options are checked again by the writer (se_settings_options()) */


$inputs['timezone'] = se_print_form_input([
    "input_name" => "prefs_timezone",
    "input_value" => $setting('timezone'),
    "label" => $lang['label_settings_timezone'],
    "options" => array_combine($settings_options['prefs_timezone'], $settings_options['prefs_timezone']),
    "type" => "select"
]);

$date_options = [];
foreach($settings_options['prefs_dateformat'] as $format) {
    $date_options[$format.' ('.date($format).')'] = $format;
}
$inputs['date_format'] = se_print_form_input([
    "input_name" => "prefs_dateformat",
    "input_value" => $setting('dateformat'),
    "label" => $lang['label_settings_date_format'],
    "options" => $date_options,
    "type" => "select"
]);

$time_options = [];
foreach($settings_options['prefs_timeformat'] as $format) {
    $time_options[$format.' ('.date($format).')'] = $format;
}
$inputs['time_format'] = se_print_form_input([
    "input_name" => "prefs_timeformat",
    "input_value" => $setting('timeformat'),
    "label" => $lang['label_settings_time_format'],
    "options" => $time_options,
    "type" => "select"
]);

/* themes */

$inputs['maintenance_code'] = se_print_form_input([
    "input_name" => "prefs_maintenance_code",
    "input_value" => $setting('maintenance_code'),
    "label" => $lang['label_settings_maintenance_code'],
    "type" => "text"
]);
$inputs['usertemplate'] = se_print_form_input([
    "input_name" => "prefs_usertemplate",
    "input_value" => $setting('usertemplate'),
    "radios" => [
        "label_settings_themes_userstyles_off" => "off",
        "label_settings_themes_userstyles_on" => "on",
        "label_settings_themes_userstyles_overwrite" => "overwrite"
    ],
    "type" => "radios"
]);
$inputs['smarty_compile_check'] = se_print_form_input([
    "input_name" => "prefs_smarty_compile_check",
    "input_value" => "1",
    "label" => 'Smarty Compile Check',
    "type" => "checkbox",
    "status" => $setting('smarty_compile_check') == "1" ? 'checked' : ''
]);

/* language */

$all_languages = get_all_languages();
$lang_options = [];
foreach($all_languages as $language) {
    $lang_options[$language['lang_desc']] = $language['lang_folder'];
}
$inputs['default_language'] = se_print_form_input([
    "input_name" => "prefs_default_language",
    "input_value" => $setting('default_language'),
    "label" => $lang['label_settings_default_language'],
    "options" => $lang_options,
    "type" => "select"
]);

$hidden_languages = (array) (json_decode(html_entity_decode($setting('deactivated_languages')), true) ?? []);
$languages = [];
foreach($all_languages as $language) {
    $languages[] = [
        'folder' => $language['lang_folder'],
        'sign' => $language['lang_sign'],
        'desc' => $language['lang_desc'],
        'hidden' => in_array($language['lang_folder'], $hidden_languages)
    ];
}

/* e-mail */

$inputs['mailer_name'] = se_print_form_input([
    "input_name" => "prefs_mailer_name",
    "input_value" => $setting('mailer_name'),
    "label" => $lang['label_settings_mailer_name'],
    "type" => "text"
]);
$inputs['mailer_address'] = se_print_form_input([
    "input_name" => "prefs_mailer_adr",
    "input_value" => $setting('mailer_adr'),
    "label" => $lang['label_settings_mailer_mail'],
    "type" => "text"
]);
$inputs['mailer_type'] = se_print_form_input([
    "input_name" => "prefs_mailer_type",
    "input_value" => $setting('mailer_type'),
    "radios" => [
        "label_settings_use_mail" => "mail",
        "label_settings_use_smtp" => "smtp"
    ],
    "type" => "radios"
]);
$inputs['notify_mail'] = se_print_form_input([
    "input_name" => "prefs_notify_mail",
    "input_value" => $setting('notify_mail'),
    "label" => $lang['label_settings_notify_mail'],
    "type" => "text"
]);

echo $twig->render('settings/general.twig', [
    'title' => $lang['nav_btn_settings'].' / '.$lang['nav_btn_general'],
    'title_icon' => $icon['gear'],
    'settings_nav' => se_settings_nav('general'),
    'tabs' => [
        ['id' => 'general', 'label' => $lang['nav_btn_general']],
        ['id' => 'system', 'label' => $lang['nav_btn_system']],
        ['id' => 'email', 'label' => 'E-Mail']
    ],
    'writer_uri' => $writer_uri,
    // titles and help texts of the sections come from the docs
    'help' => se_docs_sections('08-00-settings.md', [
        'general-website', 'general-organization', 'general-branding', 'general-images', 'general-uploads', 'general-page-versions',
        'general-domain', 'general-datetime', 'general-themes', 'general-language',
        'general-mail', 'general-mail-test'
    ]),
    'inputs' => $inputs,
    'branding_fields' => $branding_fields,
    'languages' => $languages,
    // the test mail goes to the notify e-mail, or to the sender
    'test_recipient' => html_entity_decode($setting('notify_mail') ?: $setting('mailer_adr'), ENT_QUOTES, 'UTF-8')
]);
