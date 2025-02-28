<?php

if($_GET['show'] == 'cash') {
    $get_orders = $db_content->select("se_orders", "*", [
        "order_payment_type" => "se_cash-pay",
        "ORDER" => ["order_time" => "DESC"],
        "LIMIT" => 5
    ]);

    $cnt_orders = $db_content->count("se_orders", [
        "order_payment_type" => "se_cash-pay"
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

    echo '<a class="btn btn-default" href="/admin/shop/orders/">' . $lang['nav_btn_orders'] . '</a>';

    echo '</div>';
}