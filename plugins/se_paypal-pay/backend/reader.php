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

if($_GET['show'] == 'docs_nav') {

    $docs_root = SE_ROOT.'/plugins/se_paypal-pay/docs/en/*.md';
    if(is_dir(SE_ROOT.'/plugins/se_paypal-pay/docs/'.$languagePack)) {
        $docs_root = SE_ROOT.'/plugins/se_paypal-pay/docs/'.$languagePack;
    }

    $docsfiles = glob($docs_root.'/*.md');

    foreach($docsfiles as $doc) {
        // skip tooltips
        if (str_starts_with(basename($doc), 'tip-')) {
            continue;
        }

        $parsed_file = se_parse_docs_file($doc);
        $parsed_files[] = [
            "title" => $parsed_file['header']['title'],
            "priority" => $parsed_file['header']['priority'],
            "btn" => $parsed_file['header']['btn'],
            "file" => $doc
        ];
    }

    $sorted_parsed_files = se_array_multisort($parsed_files, 'priority', SORT_ASC);

    $list = '<div class="card mb-3">';
    $list .= '<div class="list-group list-group-flush">';
    foreach($sorted_parsed_files as $k => $v) {

        $active = '';
        if($doc_filepath == $sorted_parsed_files[$k]['file']) {
            $active = 'active';
        }

        $hx_get = '/admin/addons/plugin/se_paypal-pay/read/?show=docs_content&file='.basename($sorted_parsed_files[$k]['file']);
        $hx_target = '#docsContent';

        $list .= '<button class="list-group-item list-group-item-action '.$active.'" hx-get="'.$hx_get.'" hx-target="'.$hx_target.'">';
        $list .= $sorted_parsed_files[$k]['btn'];
        $list .= '</button>';

    }
    $list .= '</div>';
    $list .= '</div>';
    echo $list;
}

if($_GET['show'] == 'docs_content') {

    $df = isset($_GET['file']) ? basename($_GET['file']) : 'index.md';

    if(is_file(SE_ROOT.'/plugins/se_paypal-pay/docs/'.$languagePack.'/'.$df)) {
        $parsed_file = se_parse_docs_file(SE_ROOT.'/plugins/se_paypal-pay/docs/'.$languagePack.'/'.$df);
        echo $parsed_file['content'];
    }
}

