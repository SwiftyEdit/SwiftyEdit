<?php

error_reporting(E_ALL ^E_WARNING ^E_NOTICE ^E_DEPRECATED);
echo '<div class="subHeader d-flex align-items-center">'.$icon['gear'].' '.$lang['nav_btn_settings'].' '.$lang['nav_btn_shop'].'</div>';

$writer_uri = '/admin/xhr/settings/general/write/';

$input_entries_per_page = [
    "input_name" => "prefs_products_per_page",
    "input_value" => $se_settings['products_per_page'],
    "label" => $lang['label_entries_per_page'],
    "type" => "text"
];

$input_select_sorting = [
    "input_name" => "prefs_product_sorting",
    "input_value" => $se_settings['product_sorting'],
    "label" => $lang['label_default_sorting'],
    "options" => [
        $lang['label_product_sorting_default'] => 1,
        $lang['label_product_sorting_topseller'] => 2,
        $lang['label_product_sorting_name'] => 3,
        $lang['label_product_sorting_price'].' / '.$lang['ascending'] => 4,
        $lang['label_product_sorting_price'].' / '.$lang['descending'] => 5
    ],
    "type" => "select"
];

$input_select_mode_cart = [
    "input_name" => "prefs_posts_products_cart",
    "input_value" => $se_settings['posts_products_cart'],
    "label" => $lang['label_carts'],
    "options" => [
        $lang['product_cart_mode_off'] => 1,
        $lang['product_cart_mode_registered'] => 2,
        $lang['product_cart_mode_all_user'] => 3
    ],
    "type" => "select"
];

$input_select_mode_order = [
    "input_name" => "prefs_posts_order_mode",
    "input_value" => $se_settings['posts_order_mode'],
    "label" => $lang['label_orders'],
    "options" => [
        $lang['product_order_mode_on'] => 1,
        $lang['product_order_mode_request'] => 2,
        $lang['product_order_mode_both'] => 3
    ],
    "type" => "select"
];

$input_max_order_value = [
    "input_name" => "prefs_posts_max_order_value",
    "input_value" => $se_settings['posts_max_order_value'],
    "label" => $lang['label_max_order_value'],
    "type" => "text"
];

$input_tax1 = [
    "input_name" => "prefs_posts_products_default_tax",
    "input_value" => $se_settings['posts_products_default_tax'],
    "label" => $lang['label_product_tax'].' #1',
    "type" => "text"
];

$input_tax2 = [
    "input_name" => "prefs_posts_products_tax_alt1",
    "input_value" => $se_settings['posts_products_tax_alt1'],
    "label" => $lang['label_product_tax'].' #2',
    "type" => "text"
];

$input_tax3 = [
    "input_name" => "prefs_posts_products_tax_alt2",
    "input_value" => $se_settings['posts_products_tax_alt2'],
    "label" => $lang['label_product_tax'].' #3',
    "type" => "text"
];

$input_currency = [
    "input_name" => "prefs_posts_products_default_currency",
    "input_value" => $se_settings['posts_products_default_currency'],
    "label" => $lang['label_product_currency'],
    "type" => "text"
];

$input_select_mode_price = [
    "input_name" => "prefs_posts_price_mode",
    "input_value" => $se_settings['posts_price_mode'],
    "label" => $lang['label_orders'],
    "options" => [
        $lang['product_show_price_gross'] => 1,
        $lang['product_show_price_both'] => 2,
        $lang['product_show_price_net'] => 3
    ],
    "type" => "select"
];

$input_select_price_visibility = [
    "input_name" => "prefs_posts_price_visibility",
    "input_value" => $se_settings['posts_price_visibility'],
    "label" => $lang['label_product_pricetag_mod'],
    "options" => [
        $lang['product_show_prices_to_all'] => 1,
        $lang['product_show_prices_to_registered'] => 2
    ],
    "type" => "select"
];

$input_select_shipping_mode = [
    "input_name" => "prefs_shipping_costs_mode",
    "input_value" => $se_settings['shipping_costs_mode'],
    "label" => $lang['label_shipping_mode'],
    "options" => [
        $lang['label_shipping_mode_flat'] => 1,
        $lang['label_shipping_mode_cats'] => 2
    ],
    "type" => "select"
];

$input_shipping_costs_flat = [
    "input_name" => "prefs_shipping_costs_flat",
    "input_value" => $se_settings['shipping_costs_flat'],
    "label" => $lang['label_shipping_costs_flat'],
    "type" => "text"
];

$input_shipping_costs_cat1 = [
    "input_name" => "prefs_shipping_costs_cat1",
    "input_value" => $se_settings['shipping_costs_cat1'],
    "label" => $lang['label_shipping_costs_cat1'],
    "type" => "text"
];

$input_shipping_costs_cat2 = [
    "input_name" => "prefs_shipping_costs_cat2",
    "input_value" => $se_settings['shipping_costs_cat2'],
    "label" => $lang['label_shipping_costs_cat2'],
    "type" => "text"
];

$input_shipping_costs_cat3 = [
    "input_name" => "prefs_shipping_costs_cat3",
    "input_value" => $se_settings['shipping_costs_cat3'],
    "label" => $lang['label_shipping_costs_cat3'],
    "type" => "text"
];


$input_bd_address = [
    "input_name" => "prefs_business_address",
    "input_value" => $se_settings['business_address'],
    "label" => $lang['label_business_address'],
    "type" => "textarea"
];

$input_bd_taxnumber = [
    "input_name" => "prefs_business_taxnumber",
    "input_value" => $se_settings['business_taxnumber'],
    "label" => $lang['label_business_tax_number'],
    "type" => "text"
];


echo '<div class="card">';
echo '<div class="card-header">';
echo '<ul class="nav nav-tabs card-header-tabs">';
echo '<li class="nav-item"><button class="nav-link active" id="general" data-bs-toggle="tab" data-bs-target="#shop-general">'.$lang['nav_btn_general'].'</button></li>';
echo '<li class="nav-item"><button class="nav-link" id="system" data-bs-toggle="tab" data-bs-target="#shop-shipping">'.$lang['nav_btn_payment_shipping'].'</button></li>';
echo '<li class="nav-item"><button class="nav-link" id="email" data-bs-toggle="tab" data-bs-target="#shop-delivery">'.$lang['nav_btn_delivery_areas'].'</button></li>';
echo '<li class="nav-item"><button class="nav-link" id="user" data-bs-toggle="tab" data-bs-target="#shop-business-details">'.$lang['nav_btn_business_details'].'</button></li>';
echo '</ul>';
echo '</div>';
echo '<div class="card-body">';
echo '<div class="tab-content" id="myTabContent">';
echo '<div class="tab-pane fade show active" id="shop-general" role="tabpanel" tabindex="0">';

echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';

echo se_print_form_input($input_entries_per_page);
echo se_print_form_input($input_select_sorting);
echo '<button type="submit" class="btn btn-primary" name="update_shop_settings" value="update">'.$lang['btn_update'].'</button>';
echo '</form>';

echo '<h5 class="heading-line">' . $lang['label_product_cart_mode'] . '</h5>';

echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';
$input_modes = [
    se_print_form_input($input_select_mode_cart),
    se_print_form_input($input_select_mode_order),
    se_print_form_input($input_max_order_value)
];

echo str_replace(['{col1}','{col2}','{col3}'],$input_modes,$bs_row_col3);
echo '<button type="submit" class="btn btn-primary" name="update_shop_settings" value="update">'.$lang['btn_update'].'</button>';
echo '</form>';

echo '<h5 class="heading-line">'.$lang['label_product_tax'].' / '.$lang['label_product_currency'].'</h5>';

echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';

$input_group = [
    se_print_form_input($input_tax1),
    se_print_form_input($input_tax2),
    se_print_form_input($input_tax3)
];

echo str_replace(['{col1}','{col2}','{col3}'],$input_group,$bs_row_col3);


$input_group = [
    se_print_form_input($input_currency),
    se_print_form_input($input_select_mode_price),
    se_print_form_input($input_select_price_visibility)
];

echo str_replace(['{col1}','{col2}','{col3}'],$input_group,$bs_row_col3);

echo '<button type="submit" class="btn btn-primary" name="update_shop_settings" value="update">'.$lang['btn_update'].'</button>';
echo '</form>';

echo '</div>';
echo '<div class="tab-pane fade" id="shop-shipping" role="tabpanel" tabindex="0">';

echo '<h5 class="heading-line">'.$lang['label_shipping'].'</h5>';

echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';

echo se_print_form_input($input_select_shipping_mode);
echo se_print_form_input($input_shipping_costs_flat);

$input_group = [
    se_print_form_input($input_shipping_costs_cat1),
    se_print_form_input($input_shipping_costs_cat2),
    se_print_form_input($input_shipping_costs_cat3)
];

echo str_replace(['{col1}','{col2}','{col3}'],$input_group,$bs_row_col3);

echo '<button type="submit" class="btn btn-primary" name="update_shop_settings" value="update">'.$lang['btn_update'].'</button>';
echo '</form>';

echo '<div class="card mt-3">';
echo '<div class="card-header">Plugins</div>';
echo '<div class="card-body">';

$get_delivery_addons = se_get_delivery_addons();
// get stored delivery addons from $prefs_delivery_addons (json)
$active_delivery_addons = json_decode($se_settings['delivery_addons'],true);
if(!is_array($active_delivery_addons)) {
    $active_delivery_addons = array();
}

if(count($get_delivery_addons) > 0){

    echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';

    echo '<table class="table">';
    echo '<tr>';
    echo '<td>'.$lang['label_status'].' / '.$lang['label_name'].'</td>';
    echo '<td>'.$lang['label_description'].'</td>';
    echo '</tr>';

    foreach ($get_delivery_addons as $delivery_addon) {
        echo '<tr>';

        $addon_dir = SE_ROOT . '/plugins/' . $delivery_addon;
        $addon_info_file = $addon_dir . '/info.json';
        $mod = array();
        if (is_file("$addon_info_file")) {
            $info_json = file_get_contents("$addon_info_file");
        }
        $mod = json_decode($info_json, true);
        $addon_link = '/admin/addons/plugin/'.$delivery_addon.'/';
        $addon_id = basename($delivery_addon,"-pay");

        $check = '';
        if(in_array("$delivery_addon",$active_delivery_addons)) {
            $check = 'checked';
        }

        echo '<td>';
        echo '<div class="form-check">';
        echo '<input class="form-check-input" type="checkbox" name="delivery_addons[]" value="'.$delivery_addon.'" id="delivery_'.$addon_id.'" '.$check.'>';
        echo '<label for="delivery_'.$addon_id.'">'.$addon_id.'</label>';
        echo '</div>';
        echo '</td>';
        echo '<td><a href="'.$addon_link.'">'.$mod['addon']['name'].'</a> '.$mod['addon']['description'].'</td>';
        echo '</tr>';
    }

    echo '</table>';

    echo '<button type="submit" class="btn btn-primary" name="update_shipping_plugins" value="update">'.$lang['btn_update'].'</button>';
    echo '</form>';

} else {
    echo '<p>No delivery Plugins</p>';
}

echo '</div>'; // card-body
echo '</div>'; // card


// payment addons

echo '<h5 class="heading-line">'.$lang['label_payment_method'].'</h5>';

echo '<div class="card">';
echo '<div class="card-header">Plugin</div>';
echo '<div class="card-body">';

$get_payment_addons = se_get_payment_addons();

// get stored payment addons from $prefs_payment_addons (json)
$active_payment_addons = json_decode($se_settings['payment_addons'],true);
if(!is_array($active_payment_addons)) {
    $active_payment_addons = array();
}

echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';

echo '<table class="table">';
echo '<tr>';
echo '<td>'.$lang['label_status'].' / '.$lang['label_name'].'</td>';
echo '<td>'.$lang['label_description'].'</td>';
echo '</tr>';

foreach ($get_payment_addons as $payment_addon) {
    echo '<tr>';

    $addon_dir = SE_ROOT . '/plugins/' . $payment_addon;
    $addon_info_file = $addon_dir . '/info.json';
    $mod = array();
    if (is_file("$addon_info_file")) {
        $info_json = file_get_contents("$addon_info_file");
    }
    $mod = json_decode($info_json, true);
    $addon_link = '/admin/addons/plugin/'.$payment_addon.'/';
    $addon_id = basename($payment_addon,"-pay");

    $check = '';
    if(in_array("$payment_addon",$active_payment_addons)) {
        $check = 'checked';
    }

    echo '<td>';
    echo '<div class="form-check">';
    echo '<input class="form-check-input" type="checkbox" name="payment_addons[]" value="'.$payment_addon.'" id="payment_'.$addon_id.'" '.$check.'>';
    echo '<label for="payment_'.$addon_id.'">'.$addon_id.'</label>';
    echo '</div>';
    echo '</td>';
    echo '<td><a href="'.$addon_link.'">'.$mod['addon']['name'].'</a> '.$mod['addon']['description'].'</td>';
    echo '</tr>';
}

echo '</table>';

echo '<button type="submit" class="btn btn-primary" name="update_payment_plugins" value="update">'.$lang['btn_update'].'</button>';
echo '</form>';

echo '</div>'; // card-body
echo '</div>'; // card


echo '</div>';
echo '<div class="tab-pane fade" id="shop-delivery" role="tabpanel" tabindex="0">';

echo '<div class="row">';
echo '<div class="col-md-6">';
echo '<div id="listDeliveryCountries" hx-get="/admin/xhr/settings/read/?show=deliveryCountriesForm" hx-trigger="load, update_deliveryCountries_list from:body">Load form ...</div>';
echo '</div>';
echo '<div class="col-md-6">';
echo '<div id="listDeliveryCountries" hx-get="/admin/xhr/settings/read/?action=deliveryCountries" hx-trigger="load, update_deliveryCountries_list from:body">Load ...</div>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="tab-pane fade" id="shop-business-details" role="tabpanel" tabindex="0">';

echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';
echo se_print_form_input($input_bd_address);
echo se_print_form_input($input_bd_taxnumber);
echo '<button type="submit" class="btn btn-primary" name="update_shop_settings" value="update">'.$lang['btn_update'].'</button>';
echo '</form>';

echo '</div>'; // card-body
echo '</div>'; // card

echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';