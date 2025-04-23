<?php

use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;

require_once SE_ROOT.'plugins/se_paypal-pay/global/functions.php';

$addon_payment_prefs = pp_get_settings();

$addon_image_src = SE_ROOT.'plugins/se_paypal-pay/poster.png';
$get_image = base64_encode(file_get_contents($addon_image_src));

echo '<div id="response"></div>';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<form hx-post="/admin/addons/plugin/se_paypal-pay/write/" hx-target="#response" method="POST">';

echo '<div class="mb-3">';
echo '<label class="form-label">Additional Costs</label>';
echo '<input class="form-control" type="text" name="paypal_additional_costs" value="'.$addon_payment_prefs['addon_additional_costs'].'">';
echo '</div>';

echo '<div class="mb-3">';
echo '<label class="form-label">Snippet for Shopping Cart</label>';
echo '<select class="form-control" name="paypal_snippet">';
echo '<option>No, nothing</option>';
$get_payment_snippets = se_get_all_snippets();
foreach($get_payment_snippets as $snippet) {
    if(str_starts_with($snippet['snippet_name'],'cart_pm_')) {

        $sel = '';
        if($addon_payment_prefs['addon_snippet_cart'] == $snippet['snippet_name']) {
            $sel = 'selected';
        }

        echo '<option value="'.$snippet['snippet_name'].'" '.$sel.'>'.$snippet['snippet_name'].'</option>';
    }
}
echo '</select>';
echo '</div>';


$mode_options = [
    "Sandbox" => "sandbox",
    "Live Account" => "live"
];

$input_select_mode = [
    "input_name" => "paypal_mode",
    "input_value" => $addon_payment_prefs['paypal_mode'],
    "label" => "Mode",
    "options" => $mode_options,
    "type" => "select"
];

echo se_print_form_input($input_select_mode);



echo '<hr class="shadow-line">';

echo '<div class="row">';
echo '<div class="col-md-6">';

echo '<div class="card">';
echo '<div class="card-header">PayPal API <span class="badge text-bg-info position-absolute top-0 end-0 translate-middle">Live Account</span></div>';
echo '<div class="card-body">';

echo '<div class="mb-3">';
echo '<label class="form-label">Client-ID</label>';
echo '<input class="form-control" type="text" name="paypal_client_id" value="'.$addon_payment_prefs['paypal_client_id'].'">';
echo '</div>';

echo '<div class="mb-3">';
echo '<label class="form-label">Client-Secret</label>';
echo '<input class="form-control" type="text" name="paypal_client_secret" value="'.$addon_payment_prefs['paypal_client_secret'].'">';
echo '</div>';

echo '<div class="mb-3">';
echo '<label class="form-label">Cancel URL</label>';
echo '<input class="form-control" type="text" name="paypal_cancel_url" value="'.$addon_payment_prefs['paypal_cancel_url'].'">';
echo '</div>';

echo '<div class="mb-3">';
echo '<label class="form-label">Return URL</label>';
echo '<input class="form-control" type="text" name="paypal_return_url" value="'.$addon_payment_prefs['paypal_return_url'].'">';
echo '</div>';

echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="col-md-6">';

echo '<div class="card">';
echo '<div class="card-header">PayPal API <span class="badge text-bg-info position-absolute top-0 end-0 translate-middle">Sandbox</span></div>';
echo '<div class="card-body">';

echo '<div class="mb-3">';
echo '<label class="form-label">Client-ID</label>';
echo '<input class="form-control" type="text" name="paypal_sb_client_id" value="'.$addon_payment_prefs['paypal_sb_client_id'].'">';
echo '</div>';

echo '<div class="mb-3">';
echo '<label class="form-label">Client-Secret</label>';
echo '<input class="form-control" type="text" name="paypal_sb_client_secret" value="'.$addon_payment_prefs['paypal_sb_client_secret'].'">';
echo '</div>';

echo '<div class="mb-3">';
echo '<label class="form-label">Cancel URL</label>';
echo '<input class="form-control" type="text" name="paypal_sb_cancel_url" value="'.$addon_payment_prefs['paypal_sb_cancel_url'].'">';
echo '</div>';

echo '<div class="mb-3">';
echo '<label class="form-label">Return URL</label>';
echo '<input class="form-control" type="text" name="paypal_sb_return_url" value="'.$addon_payment_prefs['paypal_sb_return_url'].'">';
echo '</div>';

echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';

echo '<div class="my-3">';
echo '<button type="submit" class="btn btn-default text-success" name="save_paypal_prefs">'.$lang['btn_save'].'</button>';
echo '</div>';

echo $hidden_csrf_token;
echo '</form>';
echo '</div>';
echo '<div class="col-md-3">';

echo '<div class="card p-3 mb-3">';
echo '<img src="data:image/png;base64,'.$get_image.'" class="img-fluid rounded">';
echo '</div>';

// sandbox testing
echo '<button 
        class="btn btn-default" type="button"
        data-bs-toggle="collapse" data-bs-target="#collapseTesting" aria-expanded="false" 
        aria-controls="collapseTesting">Sandbox Testing</button>';

echo '<div class="collapse" id="collapseTesting">';

$hx_vals = [
    "csrf_token"=> $_SESSION['token']
];

echo '<div id="paypalTests" class="my-3 alert alert-info" hx-vals=\''.json_encode($hx_vals).'\' hx-post="/admin/addons/plugin/se_paypal-pay/read/?show=paypal_tests" hx-trigger="load">Loading data ...</div>';

echo '</div>'; // collapseTesting

echo '</div>';
echo '</div>';



