<?php

/**
 * sections of the settings pages and the allowed values of their select fields,
 * used by the pages (acp/core/settings/*.php) and the data-writer (save_settings=<section>)
 */

/**
 * pages of the settings for the sidebar of layout/settings-page.twig
 * (the same pages as the settings group of layout/nav-sidebar.twig)
 *
 * @param string $active key of the current page
 * @return array list of {href, label, active}
 */
function se_settings_nav(string $active): array {

    global $lang, $db_type;

    $pages = [
        'general' => ['/admin/settings/', $lang['nav_btn_general']],
        'user' => ['/admin/settings/user/', $lang['nav_btn_user']],
        'posts' => ['/admin/settings/posts/', $lang['nav_btn_posts']],
        'shop' => ['/admin/settings/shop/', $lang['nav_btn_shop']],
        'events' => ['/admin/settings/events/', $lang['nav_btn_events']],
        'labels' => ['/admin/settings/labels/', $lang['labels']],
        'api-keys' => ['/admin/settings/api-keys/', $lang['api_keys_nav']]
    ];
    // the database page is only available for SQLite
    if($db_type == 'sqlite') {
        $pages['database'] = ['/admin/settings/database/', $lang['nav_btn_database']];
    }

    $nav = [];
    foreach($pages as $key => [$href, $label]) {
        $nav[] = ['href' => $href, 'label' => $label, 'active' => ($key == $active)];
    }
    return $nav;
}

/**
 * allowed values of the settings that are chosen from a list
 *
 * @return array<string, array> option key => list of values
 */
function se_settings_options(): array {

    $languages = array_column(get_all_languages(), 'lang_folder');

    return [
        'prefs_timezone' => DateTimeZone::listIdentifiers(DateTimeZone::ALL),
        'prefs_dateformat' => ['Y-m-d', 'd.m.Y', 'd/m/Y', 'm/d/Y'],
        'prefs_timeformat' => ['H:i', 'g:i a', 'g:i A'],
        'prefs_usertemplate' => ['off', 'on', 'overwrite'],
        'prefs_mailer_type' => ['mail', 'smtp'],
        'prefs_default_language' => $languages,
        'prefs_deactivated_languages' => $languages
    ];
}

/**
 * the fields of each section, a section is saved as a whole
 *   text     - stored html-encoded
 *   int      - stored as number
 *   option   - one of se_settings_options(), other values are not stored
 *   options  - a list of se_settings_options() (array input), stored as json, empty as ''
 *   [checkbox, on, off] - value if checked / not checked
 *
 * @return array<string, array<string, string|array>> section => option key => type
 */
function se_settings_sections(): array {

    return [
        'website' => [
            'prefs_pagename' => 'text',
            'prefs_pagetitle' => 'text',
            'prefs_pagesubtitle' => 'text',
            'prefs_pagedescription' => 'text'
        ],
        'pages' => [
            'prefs_nbr_page_versions' => 'int'
        ],
        'images' => [
            'prefs_maximagewidth' => 'int',
            'prefs_maximageheight' => 'int',
            'prefs_maxtmbwidth' => 'int',
            'prefs_maxtmbheight' => 'int'
        ],
        'uploads' => [
            'prefs_maxfilesize' => 'int',
            'prefs_uploads_remain_unchanged' => ['checkbox', 'yes', '']
        ],
        'domain' => [
            'prefs_cms_domain' => 'text',
            'prefs_cms_ssl_domain' => 'text',
            'prefs_cms_base' => 'text',
            'prefs_login_slug' => 'text'
        ],
        'datetime' => [
            'prefs_timezone' => 'option',
            'prefs_dateformat' => 'option',
            'prefs_timeformat' => 'option'
        ],
        'themes' => [
            'prefs_maintenance_code' => 'text',
            'prefs_usertemplate' => 'option',
            'prefs_smarty_compile_check' => ['checkbox', 1, 0]
        ],
        'language' => [
            'prefs_default_language' => 'option',
            // posted as hide_langs[]
            'prefs_deactivated_languages' => 'options'
        ],
        'mail' => [
            'prefs_mailer_name' => 'text',
            'prefs_mailer_adr' => 'text',
            'prefs_mailer_type' => 'option',
            'prefs_notify_mail' => 'text'
        ]
    ];
}

/**
 * the values of a section from the posted data, ready for se_write_option()
 *
 * @param string $section key of se_settings_sections()
 * @param array $post the posted data
 * @return array|null option key => value, null for an unknown section
 */
function se_settings_section_values(string $section, array $post): ?array {

    $sections = se_settings_sections();
    if(!isset($sections[$section])) {
        return null;
    }

    $options = se_settings_options();

    // inputs whose name differs from the option key
    $post_names = [
        'prefs_deactivated_languages' => 'hide_langs'
    ];

    $data = [];
    foreach($sections[$section] as $key => $type) {
        $value = $post[$post_names[$key] ?? $key] ?? null;

        if(is_array($type)) {
            // [checkbox, on, off]
            $data[$key] = ($value !== null) ? $type[1] : $type[2];
        } else if($type == 'text') {
            $data[$key] = htmlentities(trim(is_string($value) ? $value : ''), ENT_QUOTES, 'UTF-8');
        } else if($type == 'int') {
            $data[$key] = (int) $value;
        } else if($type == 'option') {
            if(is_string($value) && in_array($value, $options[$key], true)) {
                $data[$key] = $value;
            }
        } else if($type == 'options') {
            $values = array_values(array_intersect(is_array($value) ? $value : [], $options[$key]));
            $data[$key] = $values ? json_encode($values) : '';
        }
    }

    return $data;
}
