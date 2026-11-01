<?php

/**
 * shop settings in /admin/settings/shop/, markup in acp/templates/settings/shop.twig
 * the sections are saved by the settings data-writer (save_settings=<section>),
 * the delivery areas are loaded and saved by the settings data-reader/-writer
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

/* product list */

$inputs['products_per_page'] = se_print_form_input([
    "input_name" => "prefs_products_per_page",
    "input_value" => $setting('products_per_page'),
    "label" => $lang['label_entries_per_page'],
    "type" => "number"
]);
$inputs['product_sorting'] = se_print_form_input([
    "input_name" => "prefs_product_sorting",
    "input_value" => $setting('product_sorting'),
    "label" => $lang['label_default_sorting'],
    "options" => [
        $lang['label_product_sorting_default'] => '1',
        $lang['label_product_sorting_topseller'] => '2',
        $lang['label_product_sorting_name'] => '3',
        $lang['label_product_sorting_price'].' / '.$lang['ascending'] => '4',
        $lang['label_product_sorting_price'].' / '.$lang['descending'] => '5'
    ],
    "type" => "select"
]);
$inputs['products_cache'] = se_print_form_input([
    "input_name" => "prefs_products_cache",
    "input_value" => $setting('products_cache'),
    "label" => "Cache",
    "options" => [
        $lang['status_on'] => '1',
        $lang['status_off'] => '2'
    ],
    "type" => "select"
]);

/* shopping carts and orders */

$inputs['cart_mode'] = se_print_form_input([
    "input_name" => "prefs_posts_products_cart",
    "input_value" => $setting('posts_products_cart'),
    "label" => $lang['label_carts'],
    "options" => [
        $lang['product_cart_mode_off'] => '1',
        $lang['product_cart_mode_registered'] => '2',
        $lang['product_cart_mode_all_user'] => '3'
    ],
    "type" => "select"
]);
$inputs['order_mode'] = se_print_form_input([
    "input_name" => "prefs_posts_order_mode",
    "input_value" => $setting('posts_order_mode'),
    "label" => $lang['label_orders'],
    "options" => [
        $lang['product_order_mode_on'] => '1',
        $lang['product_order_mode_request'] => '2',
        $lang['product_order_mode_both'] => '3'
    ],
    "type" => "select"
]);
$inputs['max_order_value'] = se_print_form_input([
    "input_name" => "prefs_posts_max_order_value",
    "input_value" => $setting('posts_max_order_value'),
    "label" => $lang['label_max_order_value'],
    "type" => "text"
]);
$inputs['wishlist_enabled'] = se_print_form_input([
    "input_name" => "prefs_wishlist_enabled",
    "input_value" => "1",
    "label" => $lang['label_wishlist_enable'],
    "type" => "checkbox",
    "status" => $setting('wishlist_enabled') == "1" ? 'checked' : ''
]);
$inputs['guest_orders'] = se_print_form_input([
    "input_name" => "prefs_posts_guest_order_enable",
    "input_value" => "1",
    "label" => $lang['label_orders_guest_order_enable'],
    "type" => "checkbox",
    "status" => $setting('posts_guest_order_enable') == "1" ? 'checked' : ''
]);
$inputs['withdrawal_days'] = se_print_form_input([
    "input_name" => "prefs_posts_order_withdrawal_days",
    "input_value" => $setting('posts_order_withdrawal_days'),
    "label" => $lang['label_orders_withdrawal_days'],
    "type" => "number"
]);

/* taxes and currency */

foreach([
    'tax1' => ['posts_products_default_tax', ' #1'],
    'tax2' => ['posts_products_tax_alt1', ' #2'],
    'tax3' => ['posts_products_tax_alt2', ' #3']
] as $name => [$key, $suffix]) {
    $inputs[$name] = se_print_form_input([
        "input_name" => "prefs_$key",
        "input_value" => $setting($key),
        "label" => $lang['label_product_tax'].$suffix,
        "input_group_end_text" => "%",
        "type" => "text"
    ]);
}
$inputs['currency'] = se_print_form_input([
    "input_name" => "prefs_posts_products_default_currency",
    "input_value" => $setting('posts_products_default_currency'),
    "label" => $lang['label_product_currency'],
    "type" => "text"
]);
$inputs['price_mode'] = se_print_form_input([
    "input_name" => "prefs_posts_price_mode",
    "input_value" => $setting('posts_price_mode'),
    "label" => $lang['label_products'].' / '.$lang['label_carts'],
    "options" => [
        $lang['product_show_price_gross'] => '1',
        $lang['product_show_price_both'] => '2',
        $lang['product_show_price_net'] => '3'
    ],
    "type" => "select"
]);
$inputs['price_visibility'] = se_print_form_input([
    "input_name" => "prefs_posts_price_visibility",
    "input_value" => $setting('posts_price_visibility'),
    "label" => $lang['label_product_pricetag_mod'],
    "options" => [
        $lang['product_show_prices_to_all'] => '1',
        $lang['product_show_prices_to_registered'] => '2'
    ],
    "type" => "select"
]);

/* shipping */

$inputs['shipping_mode'] = se_print_form_input([
    "input_name" => "prefs_shipping_costs_mode",
    "input_value" => $setting('shipping_costs_mode'),
    "label" => $lang['label_shipping_mode'],
    "options" => [
        $lang['label_shipping_mode_flat'] => '1',
        $lang['label_shipping_mode_cats'] => '2'
    ],
    "type" => "select"
]);
foreach(['flat', 'cat1', 'cat2', 'cat3'] as $name) {
    $inputs['shipping_'.$name] = se_print_form_input([
        "input_name" => "prefs_shipping_costs_$name",
        "input_value" => $setting('shipping_costs_'.$name),
        "label" => $lang['label_shipping_costs_'.$name],
        "type" => "text"
    ]);
}

/* payment and delivery plugins, the stored lists are json */

$plugin_list = static function(array $addons, string $stored): array {
    $active = json_decode(html_entity_decode($stored), true);
    $active = is_array($active) ? $active : [];
    $plugins = [];
    foreach($addons as $addon) {
        $info = [];
        $info_file = SE_ROOT.'plugins/'.$addon.'/info.json';
        if(is_file($info_file)) {
            $info = json_decode((string) file_get_contents($info_file), true) ?: [];
        }
        $plugins[] = [
            'folder' => $addon,
            'name' => $info['addon']['name'] ?? $addon,
            'description' => $info['addon']['description'] ?? '',
            'active' => in_array($addon, $active)
        ];
    }
    return $plugins;
};

/* business details */

$inputs['business_address'] = se_print_form_input([
    "input_name" => "prefs_business_address",
    "input_value" => $setting('business_address'),
    "label" => $lang['label_business_address'],
    "type" => "textarea"
]);
$inputs['business_taxnumber'] = se_print_form_input([
    "input_name" => "prefs_business_taxnumber",
    "input_value" => $setting('business_taxnumber'),
    "label" => $lang['label_business_tax_number'],
    "type" => "text"
]);

echo $twig->render('settings/shop.twig', [
    'title' => $lang['nav_btn_settings'].' / '.$lang['nav_btn_shop'],
    'title_icon' => $icon['gear'],
    'settings_nav' => se_settings_nav('shop'),
    'tabs' => [
        ['id' => 'general', 'label' => $lang['nav_btn_general']],
        ['id' => 'shipping', 'label' => $lang['nav_btn_payment_shipping']],
        ['id' => 'delivery', 'label' => $lang['nav_btn_delivery_areas']],
        ['id' => 'business', 'label' => $lang['nav_btn_business_details']]
    ],
    'writer_uri' => '/admin-xhr/settings/general/write/',
    'reader_uri' => '/admin-xhr/settings/read/',
    // titles and help texts of the sections come from the docs
    'help' => se_docs_sections('08-00-settings.md', [
        'shop-list', 'shop-cart', 'shop-lists', 'shop-guest-orders', 'shop-withdrawal', 'shop-taxes',
        'shop-shipping', 'shop-delivery-plugins', 'shop-payment-plugins', 'shop-delivery-areas', 'shop-business'
    ]),
    'inputs' => $inputs,
    'delivery_plugins' => $plugin_list(se_get_delivery_addons(), $setting('delivery_addons')),
    'payment_plugins' => $plugin_list(se_get_payment_addons(), $setting('payment_addons'))
]);
