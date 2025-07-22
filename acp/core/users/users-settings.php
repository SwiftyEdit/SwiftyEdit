<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['users'].' / '.$lang['nav_btn_user']. ' / '.$lang['nav_btn_settings']. ' / '.$_SESSION['user_nick'];
echo '</div>';

$my_user_presets = se_get_my_presets();

$status_options = [
    $lang['status_public'] => 'p',
    $lang['status_draft'] => 'd'
];

$select_status = [
    "input_name" => "preset_status",
    "input_value" => $my_user_presets['status'],
    "label" => $lang['label_status'],
    "options" => $status_options,
    "type" => "select"
];

$product_type_options = [
    $lang['label_shipping_mode_digital'] => 'digital',
    $lang['label_shipping_mode_deliver'] => 'deliver'
];

$select_product_type = [
    "input_name" => "preset_product_type",
    "input_value" => $my_user_presets['product_type'],
    "label" => $lang['label_shipping_mode'],
    "options" => $product_type_options,
    "type" => "select"
];

echo '<div class="card">';

echo '<div id="response"></div>';

echo '<form hx-post="/admin-xhr/users/write/" hx-target="#response">';

echo '<div class="card">';
echo '<div class="card-header">'.$lang['label_settings'].'</div>';
echo '<div class="card-body">';

echo '<h5 class="heading-line">'.$lang['label_status'].'</h5>';
echo se_print_form_input($select_status);
echo '<h5 class="heading-line">'.$lang['label_products'].'</h5>';
echo se_print_form_input($select_product_type);

echo '</div>';
echo '<div class="card-footer">';
echo '<button class="btn btn-default" name="save_my_settings">'.$lang['btn_save'].'</button>';
echo '</div>';
echo '</div>';

echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</form>';

echo '</div>';