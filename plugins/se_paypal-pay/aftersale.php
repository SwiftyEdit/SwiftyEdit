<?php

/**
 * PayPal Plugin
 * aftersale file
 *
 * @var array $order_data
 * @var array $se_settings
 * @var array $lang
 */

use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\Exceptions\ApiException;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;

require_once SE_ROOT.'plugins/se_paypal-pay/global/functions.php';

$order_value = se_sanitize_price($order_data['order_price_total']);
$order_value = str_replace(',', '.', $order_value);
$order_value = round($order_value,2);
$order_currency = $se_settings['posts_products_default_currency'];

$paypal_settings = pp_get_settings();

if($paypal_settings['paypal_mode'] == 'live') {
    $clientId = $paypal_settings['paypal_client_id'];
    $clientSecret = $paypal_settings['paypal_client_secret'];
    $paypal_return_url = $paypal_settings['paypal_return_url'];
    $paypal_cancel_url = $paypal_settings['paypal_cancel_url'];
    $environment = Environment::PRODUCTION;
} else {
    $clientId = $paypal_settings['paypal_sb_client_id'];
    $clientSecret = $paypal_settings['paypal_sb_client_secret'];
    $paypal_return_url = $paypal_settings['paypal_sb_return_url'];
    $paypal_cancel_url = $paypal_settings['paypal_sb_cancel_url'];
    $environment = Environment::SANDBOX;
}

$client = PaypalServerSdkClientBuilder::init()
    ->clientCredentialsAuthCredentials(
        ClientCredentialsAuthCredentialsBuilder::init($clientId, $clientSecret)
    )
    ->environment($environment)
    ->build();

$reference_id = $order_data['order_nbr'];

$response_string = '';

try {
    $response = $client->getOrdersController()->createOrder([
        'body' => [
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
                "landing_page" => "LOGIN",  // or "BILLING"
                "user_action" => "PAY_NOW"
            ]
        ],
        'prefer' => 'return=representation'
    ]);

    $order = $response->getResult();

    foreach ($order->getLinks() as $link) {
        if ($link->getRel() === 'approve') {
            $response_string = '<p><a class="btn btn-info w-100" href="' . $link->getHref() . '">'.$lang['btn_pay_with_paypal'].'</a></p>';
        }
    }
} catch (ApiException $ex) {
    $response_string = "ERROR: " . $ex->getMessage();
}

$cart_alert = $response_string;
