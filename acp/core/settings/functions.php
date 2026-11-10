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
        'prefs_deactivated_languages' => $languages,
        'prefs_required_fields_registration' => se_settings_registration_fields(),
        // 1 = approved by an admin, 2 = appear immediately, 3 = off
        'prefs_comments_mode' => ['1', '2', '3'],
        // 1 = off, 2 = registered users, 3 = all visitors
        'prefs_posts_default_votings' => ['1', '2', '3'],
        // 1 = default, 2 = topseller, 3 = name, 4 = price ascending, 5 = price descending
        'prefs_product_sorting' => ['1', '2', '3', '4', '5'],
        // 1 = on, 2 = off
        'prefs_products_cache' => ['1', '2'],
        // 1 = off, 2 = registered users, 3 = all visitors
        'prefs_posts_products_cart' => ['1', '2', '3'],
        // 1 = order, 2 = request, 3 = both
        'prefs_posts_order_mode' => ['1', '2', '3'],
        // 1 = gross, 2 = both, 3 = net
        'prefs_posts_price_mode' => ['1', '2', '3'],
        // 1 = all visitors, 2 = registered users
        'prefs_posts_price_visibility' => ['1', '2'],
        // 1 = off, 2 = registered users, 3 = all visitors
        'prefs_posts_default_guestlist' => ['1', '2', '3'],
        // 1 = flat rate, 2 = shipping categories
        'prefs_shipping_costs_mode' => ['1', '2'],
        'prefs_delivery_addons' => se_get_delivery_addons(),
        'prefs_payment_addons' => se_get_payment_addons(),
        // schema.org type of the organization in the structured data
        'prefs_org_type' => se_structured_data_org_types(),
        // ISO 3166 alpha-2, '' = not set
        'prefs_org_country' => array_merge([''], array_keys(se_get_countries_options()))
    ];
}

/**
 * columns of se_user that can be required on registration -
 * everything else is login data, internal state or set by the system
 *
 * @return array list of column names
 */
function se_settings_registration_fields(): array {

    // columns of se_user, the installer file sets $cols
    $cols = [];
    include SE_ROOT.'install/contents/se_user.php';

    $excluded_fields = [
        'user_nick', 'user_psw', 'user_mail', 'user_social_media',
        // filled by the system: on registration, an e-mail change or a deletion
        'user_uuid', 'user_mail_temp', 'user_deleted_at', 'user_deleted_nick',
        'user_id', 'user_class', 'user_psw_hash', 'user_failed_logins', 'user_failed_login_time', 'user_unlock_code', 'user_unlock_expires', 'user_locked_until',
        'user_groups', 'user_avatar', 'user_registerdate', 'user_verified', 'user_verified_by_admin', 'user_drm',
        'user_acp_settings', 'user_activationkey', 'user_reset_psw', 'user_reset_psw_expires',
        'user_2fa_method', 'user_2fa_secret', 'user_2fa_last_step', 'user_2fa_mail_code', 'user_2fa_mail_expires', 'user_2fa_mail_sent', 'user_2fa_recovery', 'user_2fa_since', 'user_2fa_failed', 'user_2fa_locked_until', 'user_2fa_mail_blocked'
    ];

    return array_values(array_diff(array_keys($cols), $excluded_fields));
}

/**
 * the fields of each section, a section is saved as a whole
 *   text     - stored html-encoded
 *   int      - stored as number
 *   days     - entered in days, stored in seconds
 *   hours    - entered in hours, stored in seconds
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
        // structured data (schema.org) of all pages, see app/functions/functions.structured-data.php
        'organization' => [
            'prefs_org_type' => 'option',
            'prefs_org_name' => 'text',
            'prefs_org_street' => 'text',
            'prefs_org_street_nbr' => 'text',
            'prefs_org_zip' => 'text',
            'prefs_org_city' => 'text',
            'prefs_org_country' => 'option',
            'prefs_org_phone' => 'text',
            'prefs_org_email' => 'text',
            'prefs_org_sameas' => 'text'
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
        ],
        'user-registration' => [
            'prefs_userregistration' => ['checkbox', 'yes', 'no'],
            'prefs_showloginform' => ['checkbox', 'yes', 'no'],
            'prefs_user_unlock_by_admin' => ['checkbox', 'yes', 'no']
        ],
        'user-required-fields' => [
            // posted as required_fields[]
            'prefs_required_fields_registration' => 'options'
        ],
        'user-blacklist' => [
            'prefs_blacklist_usernames' => 'text'
        ],
        'user-session' => [
            'prefs_acp_session_lifetime' => 'idle'
        ],
        'user-comments' => [
            'prefs_comments_mode' => 'option',
            'prefs_comments_autoclose' => 'days'
        ],
        'user-votings' => [
            'prefs_posts_default_votings' => 'option'
        ],
        'posts-entries' => [
            'prefs_posts_entries_per_page' => 'int'
        ],
        'events-list' => [
            'prefs_events_entries_per_page' => 'int',
            'prefs_posts_event_time_offset' => 'hours'
        ],
        'events-guestlist' => [
            'prefs_posts_default_guestlist' => 'option'
        ],
        'shop-list' => [
            'prefs_products_per_page' => 'int',
            'prefs_product_sorting' => 'option',
            'prefs_products_cache' => 'option'
        ],
        'shop-cart' => [
            'prefs_posts_products_cart' => 'option',
            'prefs_posts_order_mode' => 'option',
            // a price with decimal comma or point, see app/handlers/checkout.php
            'prefs_posts_max_order_value' => 'text'
        ],
        'shop-lists' => [
            'prefs_wishlist_enabled' => ['checkbox', 1, 0]
        ],
        'shop-guest-orders' => [
            'prefs_posts_guest_order_enable' => ['checkbox', 1, 0]
        ],
        'shop-withdrawal' => [
            'prefs_posts_order_withdrawal_days' => 'int'
        ],
        'shop-taxes' => [
            'prefs_posts_products_default_tax' => 'text',
            'prefs_posts_products_tax_alt1' => 'text',
            'prefs_posts_products_tax_alt2' => 'text',
            'prefs_posts_products_default_currency' => 'text',
            'prefs_posts_price_mode' => 'option',
            'prefs_posts_price_visibility' => 'option'
        ],
        'shop-shipping' => [
            'prefs_shipping_costs_mode' => 'option',
            'prefs_shipping_costs_flat' => 'text',
            'prefs_shipping_costs_cat1' => 'text',
            'prefs_shipping_costs_cat2' => 'text',
            'prefs_shipping_costs_cat3' => 'text'
        ],
        'shop-delivery-plugins' => [
            // posted as delivery_addons[]
            'prefs_delivery_addons' => 'options'
        ],
        'shop-payment-plugins' => [
            // posted as payment_addons[]
            'prefs_payment_addons' => 'options'
        ],
        'shop-business' => [
            'prefs_business_address' => 'text',
            'prefs_business_taxnumber' => 'text'
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
        'prefs_deactivated_languages' => 'hide_langs',
        'prefs_required_fields_registration' => 'required_fields',
        'prefs_delivery_addons' => 'delivery_addons',
        'prefs_payment_addons' => 'payment_addons'
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
        } else if($type == 'idle') {
            // ACP idle timeout in seconds, kept within its bounds
            $data[$key] = se_acp_idle_clamp($value);
        } else if($type == 'days') {
            $data[$key] = max(0, (int) $value) * 86400;
        } else if($type == 'hours') {
            $data[$key] = max(0, (int) $value) * 3600;
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
