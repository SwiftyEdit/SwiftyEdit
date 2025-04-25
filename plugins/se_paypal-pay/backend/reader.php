<?php

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Core\ProductionEnvironment;


require_once SE_ROOT.'plugins/se_paypal-pay/global/functions.php';


if($_GET['show'] == 'paypal') {
    $get_orders = $db_content->select("se_orders", "*", [
        "order_payment_type" => "se_paypal-pay",
        "ORDER" => ["order_time" => "DESC"],
        "LIMIT" => 5
    ]);

    $cnt_orders = $db_content->count("se_orders", [
        "order_payment_type" => "se_paypal-pay"
    ]);

    echo '<div class="card p-3">';
    echo '<p>This Payment Method is used <code>' . $cnt_orders . '</code> times. Here the latest orders used this payment method.</p>';

    foreach ($get_orders as $order) {
        echo '<div class="card p-3 mb-3">';
        echo '<div class="row">';
        echo '<div class="col-md-2">' . date("Y-m-d", $order['order_time']) . '</div>';
        echo '<div class="col-md-2">' . $order['order_price_total'] . '</div>';
        echo '<div class="col-md-8">' . str_replace("<br>", " ", $order['order_invoice_address']) . '</div>';
        echo '</div>';
        echo '</div>';
    }

    echo '<div class="btn-group">';
    echo '<a class="btn btn-default" href="/admin/shop/orders/">' . $lang['nav_btn_orders'] . '</a>';
    echo '<a class="btn btn-default" href="/admin/addons/plugin/se_paypal-pay/settings/">' . $lang['nav_btn_settings'] . '</a>';
    echo '</div>';

    echo '</div>';
}

if($_GET['show'] == 'paypal_tests') {

    $paypal_settings = pp_get_settings();

    if($paypal_settings['paypal_mode'] == 'live') {
        $clientId = $paypal_settings['paypal_client_id'];
        $clientSecret = $paypal_settings['paypal_client_secret'];
        $environment = new ProductionEnvironment($clientId, $clientSecret);
    } else {
        $clientId = $paypal_settings['paypal_sb_client_id'];
        $clientSecret = $paypal_settings['paypal_sb_client_secret'];
        $environment = new SandboxEnvironment($clientId, $clientSecret);
    }


    $client = new PayPalHttpClient($environment);

    echo '<form hx-post="/admin/addons/plugin/se_paypal-pay/read/?show=paypal_tests" method="post">';


    echo '<div class="input-group mb-3">';
    echo '<input type="text" class="form-control" name="order_value" value="1.23">';
    echo '<span class="input-group-text" id="basic-addon2">'.$se_settings['posts_products_default_currency'].'</span>';
    echo '</div>';

    echo '<div class="mb-3">';
    echo '<input type="submit" class="btn btn-info" name="submit_test_order" value="Send Test Order">';
    echo '</div>';

    echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
    echo '<input type="hidden" name="order_currency" value="'.$se_settings['posts_products_default_currency'].'">';
    echo '</form>';

    if(isset($_POST['submit_test_order'])) {

        $order_value = se_sanitize_price($_POST['order_value']);
        $order_value = str_replace(',', '.', $order_value);
        $order_currency = sanitizeUserInputs($_POST['order_currency']);

        $demo_order = [
            "order_id" => "123456789-demo",
            "order_value" => "$order_value",
            "order_currency" => "EUR"
        ];

        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => $demo_order['order_currency'],
                    "value" => $demo_order['order_value']
                ]
            ]]
        ];

        try {
            $response = $client->execute($request);
            echo '<div class="alert alert-success">';
            echo "Order ID: " . $response->result->id;
            echo '</div>';
        } catch (HttpException $ex) {
            echo "ERROR: " . $ex->getMessage();
        }

        foreach ($response->result->links as $link) {
            if ($link->rel === 'approve') {
                echo '<p><a class="btn btn-info w-100" href="' . $link->href . '">Pay with PayPal</a></p>';
            }
        }

    }

}