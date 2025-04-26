<?php

/**
 * PayPal Plugin
 * aftersale file
 *
 * @var array $order_data
 * @var array $se_settings
 * @var array $lang
 */

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;



require_once SE_ROOT.'plugins/se_paypal-pay/global/functions.php';

$order_value = se_sanitize_price($order_data['order_price_total']);
$order_value = str_replace(',', '.', $order_value);
$order_currency = $se_settings['posts_products_default_currency'];

$paypal_settings = pp_get_settings();

if($paypal_settings['paypal_mode'] == 'live') {
    $clientId = $paypal_settings['paypal_client_id'];
    $clientSecret = $paypal_settings['paypal_client_secret'];
    $paypal_return_url = $paypal_settings['paypal_return_url'];
    $paypal_cancel_url = $paypal_settings['paypal_cancel_url'];
    $environment = new ProductionEnvironment($clientId, $clientSecret);
} else {
    $clientId = $paypal_settings['paypal_sb_client_id'];
    $clientSecret = $paypal_settings['paypal_sb_client_secret'];
    $paypal_return_url = $paypal_settings['paypal_sb_return_url'];
    $paypal_cancel_url = $paypal_settings['paypal_sb_cancel_url'];
    $environment = new SandboxEnvironment($clientId, $clientSecret);
}

$client = new PayPalHttpClient($environment);


$cart_test = print_r($order_data, true);
$reference_id = $order_data['order_nbr'];

$request = new OrdersCreateRequest();
$request->prefer('return=representation');
$request->body = [
    "intent" => "CAPTURE",
    "purchase_units" => [[
        "reference_id" => $reference_id,
        "amount" => [
            "currency_code" => $order_currency,
            "value" => $order_value
        ]
    ]],
    "application_context" => [
        "return_url" => $paypal_return_url,
        "cancel_url" => $paypal_cancel_url,
        "brand_name" => $se_settings['pagename'],
        "landing_page" => "LOGIN",  // oder "BILLING"
        "user_action" => "PAY_NOW"
    ]
];

try {
    $response = $client->execute($request);
} catch (HttpException $ex) {
    $response_string = "ERROR: " . $ex->getMessage();
}

foreach ($response->result->links as $link) {
    if ($link->rel === 'approve') {
        $response_string = '<p><a class="btn btn-info w-100" href="' . $link->href . '">'.$lang['btn_pay_with_paypal'].'</a></p>';
    }
}

$cart_alert = $response_string;