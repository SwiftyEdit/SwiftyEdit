<?php

$send_order = true;

if($_POST['check_cart_terms'] != 'check') {
    $send_order = false;
    $smarty->assign("cart_alert_error",$lang['msg_accept_terms'],true);
}

foreach ($cart_item as $key => $array) {
    unset($array['price_net_format'],$array['price_gross_format'],$array['price_net']);
    $cart_items[$key] = $array;
}


/* store the order */
if($send_order == true) {

    $cart_items_str = json_encode($cart_items, JSON_FORCE_OBJECT);

    $order_data['user_id'] = $get_cd['user_id'];
    $order_data['user_mail'] = $get_cd['user_mail'];
    $order_data['order_invoice_address'] = $client_data;
    $order_data['order_shipping_address'] = $client_shipping_address;
    $order_data['order_products'] = $cart_items_str;
    $order_data['order_price_total'] = $cart_price_total;
    $order_data['included_taxes'] = $cart_included_taxes;
    $order_data['order_shipping_type'] = $shipping_type;
    $order_data['order_shipping_costs'] = $shipping_costs;
    $order_data['order_payment_type'] = $payment_addon;
    $order_data['order_payment_costs'] = $payment_costs;
    $order_data['order_comment'] = $_POST['cart_comment'];
    $order_data['order_nbr'] = $get_cd['user_id'].'-'.uniqid();

    $order_id = se_send_order($order_data);

    se_recalculate_stock_sales($cart_items);

    if($order_id > 0) {

        $cart_alert = se_get_snippet('cart_order_sent',$languagePack,'content');
        if($cart_alert == '') {
            $cart_alert = $lang['msg_order_send'];
        }


        /* remove items from se_carts */
        se_clear_cart($order_data['user_id']);
        $cnt_cart_items = 0;

        $recipient['name'] = $get_cd['user_firstname'].' '.$get_cd['user_lastname'];
        $recipient['mail'] = $get_cd['user_mail'];
        $recipient['type'] = 'client';
        $reason = 'order_confirmation';

        // include after sale script from payment addon
        $aftersale_script = SE_ROOT.'/plugins/'.basename($payment_addon).'/aftersale.php';
        if(is_file($aftersale_script)) {
            include $aftersale_script;
        }
        $smarty->assign("cart_alert_success",$cart_alert,true);

        $send_mail = se_send_order_status($recipient,$order_id,$reason);
    }
}