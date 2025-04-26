<?php

/**
 * PayPal
 * check if payment was successful
 */

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalHttp\HttpException;

require_once SE_ROOT.'plugins/se_paypal-pay/global/functions.php';

$paypal_settings = pp_get_settings();

if($paypal_settings['paypal_mode'] == 'live') {
    $clientId = $paypal_settings['paypal_client_id'];
    $clientSecret = $paypal_settings['paypal_client_secret'];
    $paypal_url = parse_url($paypal_settings['paypal_return_url']);
    $environment = new ProductionEnvironment($clientId, $clientSecret);
} else {
    $clientId = $paypal_settings['paypal_sb_client_id'];
    $clientSecret = $paypal_settings['paypal_sb_client_secret'];
    $paypal_url = parse_url($paypal_settings['paypal_sb_return_url']);
    $environment = new SandboxEnvironment($clientId, $clientSecret);
}

$paypal_url_str = substr($paypal_url['path'],1);
if(isset($_GET['token']) && $_REQUEST['query'] == $paypal_url_str){

    $client = new PayPalHttpClient($environment);

    $orderId = $_GET['token'] ?? null;
    if (!$orderId) {
        die('ERROR - No Order-ID');
    }

    $return_str = '';

    try {
        // get data from PayPal
        $request = new OrdersCaptureRequest($orderId);
        $request->prefer('return=representation');

        $response = $client->execute($request);

        if ($response->result->status === 'COMPLETED') {
            // order is paid

            $order_nbr = htmlspecialchars($response->result->purchase_units[0]->reference_id);

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
            $return_str .= "<p>Status: " . htmlspecialchars($response->result->status) . "</p>";
        }

    } catch (HttpException $ex) {
        // error handling
        $return_str .= "<h1>ERROR</h1>";
        $return_str .= "<pre>" . htmlspecialchars($ex->getMessage()) . "</pre>";
    }

    $page_contents['page_modul'] = 'se_paypal-pay';
    $page_contents['page_content'] = $return_str;
    $show_posts = false;
}