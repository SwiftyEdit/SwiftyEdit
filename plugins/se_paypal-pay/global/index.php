<?php

/**
 * PayPal
 * check if payment was successful
 */

use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\Exceptions\ApiException;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;

require_once SE_ROOT.'plugins/se_paypal-pay/global/functions.php';

$paypal_settings = pp_get_settings();

if($paypal_settings['paypal_mode'] == 'live') {
    $clientId = $paypal_settings['paypal_client_id'];
    $clientSecret = $paypal_settings['paypal_client_secret'];
    $paypal_url = parse_url($paypal_settings['paypal_return_url']);
    $environment = Environment::PRODUCTION;
} else {
    $clientId = $paypal_settings['paypal_sb_client_id'];
    $clientSecret = $paypal_settings['paypal_sb_client_secret'];
    $paypal_url = parse_url($paypal_settings['paypal_sb_return_url']);
    $environment = Environment::SANDBOX;
}

$paypal_url_str = substr($paypal_url['path'],1);
if(isset($_GET['token']) && $_REQUEST['query'] == $paypal_url_str){

    $client = PaypalServerSdkClientBuilder::init()
        ->clientCredentialsAuthCredentials(
            ClientCredentialsAuthCredentialsBuilder::init($clientId, $clientSecret)
        )
        ->environment($environment)
        ->build();

    $orderId = $_GET['token'] ?? null;
    if (!$orderId) {
        die('ERROR - No Order-ID');
    }

    $return_str = '';

    try {
        // capture the order at PayPal
        $response = $client->getOrdersController()->captureOrder([
            'id' => $orderId,
            'prefer' => 'return=representation'
        ]);

        $order = $response->getResult();

        if ($order->getStatus() === 'COMPLETED') {
            // order is paid

            $purchase_units = $order->getPurchaseUnits();
            $order_nbr = htmlspecialchars($purchase_units[0]->getReferenceId());

            $return_str .= '<h1>'.$lang['label_payment_completed'].'</h1>';
            $return_str .= '<p>'.$lang['msg_payment_completed'].'</p>';
            $return_str .= '<p>'.$lang['label_order_nbr'].': '. $order_nbr . '</p>';

            // update se_orders - order_status_payment -> 2
            $db_content->update("se_orders", [
                "order_status_payment" => "2"
            ],[
                "order_nbr" => $order_nbr,
                "user_id" => $_SESSION['user_id']
            ]);

        } else {
            $return_str .= "<h1>ERROR</h1>";
            $return_str .= "<p>Status: " . htmlspecialchars($order->getStatus()) . "</p>";
        }

    } catch (ApiException $ex) {
        // error handling
        $return_str .= "<h1>ERROR</h1>";
        $return_str .= "<pre>" . htmlspecialchars($ex->getMessage()) . "</pre>";
    }

    $page_contents['page_modul'] = 'se_paypal-pay';
    $page_contents['page_content'] = $return_str;
    $show_posts = false;
}
