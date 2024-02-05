<?php
//error_reporting(E_ALL ^E_NOTICE ^E_WARNING);
/**
 * SwiftyEdit frontend
 * - show shopping cart
 * - send order
 *
 * global variables
 * @var array $se_prefs global project variable
 * @var array $lang global project variable
 * @var string $languagePack global project variable
 * @var object $smarty Smarty template engine
 * @var int $cache_id Smarty cache id
 */

$price_all_net = 0; // reset price net
$price_all_gross = 0; // reset price gross
$shipping_costs = 0; // reset shipping costs
$shipping_products = 0; // number of products which will be shipped
$store_shipping_cat = 0; // reset shipping category
$checkout_error = '';

if(isset($_POST['remove_from_cart'])) {
	$id = (int) $_POST['remove_from_cart'];
	se_remove_from_cart($id);
}

if(isset($_POST['cart_product_amount'])) {
    $new_amount = (int) $_POST['cart_product_amount'];
    $item_key = $_POST['cart_item_key'];
    se_update_cart_item_amount($item_key,$new_amount);
}


$get_cart_items = se_return_my_cart();
$cnt_cart_items = count($get_cart_items);

$payment_methods = se_get_payment_methods();
$payment_costs = '0.00';

if($_SESSION['set_payment'] == '') {
	$_SESSION['set_payment'] = array_key_first($payment_methods);
}

if(isset($_POST['set_payment'])) {
	$_SESSION['set_payment'] = clean_filename($_POST['set_payment']);
}

$payment_key = $_SESSION['set_payment'];

$payment_addon = $payment_methods[$payment_key]['addon'];
$payment_costs = $payment_methods[$payment_key]['cost'];
$payment_message = $payment_methods[$payment_key]['snippet'];



// check the radio for payment
// example $checked_invoicepay
$check_pm_radio = 'checked_'.$_SESSION['set_payment'];
$smarty->assign("$check_pm_radio", 'checked');

$get_cd = get_my_userdata();
$client_data = '';
if($get_cd['user_company'] != '') {
	$client_data .= $get_cd['user_company'].'<br>';
}

/**
 * check if we have all mandatory information
 * firstname, lastname, street, street nbr, zip and city
 */

if($get_cd['user_firstname'] == '' ||
    $get_cd['user_lastname'] == '' ||
    $get_cd['user_street'] == '' ||
    $get_cd['user_street_nbr'] == '' ||
    $get_cd['user_zip'] == '' ||
    $get_cd['user_city'] == '') {
    $checkout_error = 'missing_mandatory_informations';
}

if($se_prefs['prefs_user_unlock_by_admin'] == 'yes' AND $get_cd['user_verified_by_admin'] != 'yes') {
    $checkout_error = 'missing_approval';
}

$client_data .= $get_cd['user_firstname']. ' '.$get_cd['user_lastname'].'<br>';
$client_data .= $get_cd['user_street']. ' '.$get_cd['user_street_nbr'].'<br>';
$client_data .= $get_cd['user_zip']. ' '.$get_cd['user_city'].'<br>';


for($i=0;$i<$cnt_cart_items;$i++) {
	
	$this_item = se_get_product_data($get_cart_items[$i]['cart_product_id']);

	$cart_item[$i]['nbr'] = $i+1;
	$cart_item[$i]['title'] = $this_item['title'];
    $cart_item[$i]['options'] = $get_cart_items[$i]['cart_product_options'];
    $cart_item[$i]['options_comment'] = $get_cart_items[$i]['cart_product_options_comment'];
    $cart_item[$i]['options_comment_label'] = $this_item['product_options_comment_label'];
	$cart_item[$i]['product_number'] = $this_item['product_number'];
	$cart_item[$i]['amount'] = $get_cart_items[$i]['cart_product_amount'];
	$cart_item[$i]['cart_id'] = $get_cart_items[$i]['cart_id'];
	$cart_item[$i]['post_id'] = $get_cart_items[$i]['cart_product_id'];
	
	/* will the product be delivered? */
	if($this_item['product_shipping_mode'] == 2) {
		$shipping_products++;
		
		if($this_item['product_shipping_cat'] > $store_shipping_cat) {
			$store_shipping_cat = $this_item['product_shipping_cat'];
		}
	}

    /**
     * check, if we need a file from customer
     */

    $cart_item[$i]['need_upload'] = '';
    if($this_item['file_attachment_user'] == 2) {
        $cart_item[$i]['need_upload'] = 'true';
    }


    // get price from price groups or from products data
    if($this_item['product_price_group'] != '' AND $this_item['product_price_group'] != 'null') {
        $price_data = se_get_price_group_data($this_item['product_price_group']);
        $product_tax = $price_data['tax'];
        $product_price_net = $price_data['price_net'];
        $product_volume_discounts_json = $price_data['price_volume_discount'];
    } else {
        $product_tax = $this_item['product_tax'];
        $product_price_net = $this_item['product_price_net'];
        $product_volume_discounts_json = $this_item['product_price_volume_discounts'];
    }

	if($product_tax == '1') {
		$tax = $se_prefs['prefs_posts_products_default_tax'];
	} else if($product_tax == '2') {
		$tax = $se_prefs['prefs_posts_products_tax_alt1'];
	} else {
		$tax = $se_prefs['prefs_posts_products_tax_alt2'];
	}
	
	$cart_item[$i]['tax'] = $tax;

    // check if we have to calculate volume discounts
    if($product_volume_discounts_json != '') {
        $volume_discounts = json_decode($product_volume_discounts_json,true);
        if(is_array($volume_discounts)) {
            // we sort this by amount
            $amounts = array();
            foreach ($volume_discounts as $k => $v) {
                $amounts[$k] = $v['amount'];
            }
            array_multisort($amounts, SORT_ASC, $volume_discounts);

            // now we loop through this amounts and check which price we can serve
            // if $cart_item[$i]['amount'] is bigger or the same, we have a new price

            foreach ($volume_discounts as $k => $v) {
                if ($cart_item[$i]['amount'] >= $v['amount']) {
                    // overwrite product_price_net with volume discount
                    $product_price_net = $v['price'];
                }
            }
        }
    }


	$post_prices = se_posts_calc_price($product_price_net,$tax,$cart_item[$i]['amount']);
    $cart_item[$i]['price_net_single_format'] = $post_prices['net_single'];
    $cart_item[$i]['price_gross_single_format'] = $post_prices['gross_single'];
    $cart_item[$i]['price_net_format'] = $post_prices['net'];
	$cart_item[$i]['price_gross_format'] = $post_prices['gross'];
	$cart_item[$i]['price_net_raw'] = $post_prices['net_raw'];
	$cart_item[$i]['price_gross_raw'] = $post_prices['gross_raw'];
	$cart_item[$i]['price_net'] = $this_item['product_price_net'];
	
	$price_all_net = $price_all_net+round($post_prices['net_raw'],2);
    $all_items_subtotal = $all_items_subtotal+$cart_item[$i]['price_gross_raw'];
}


$smarty->assign('cart_items', $cart_item);

/* check if we have products for shipping */
if($shipping_products > 0) {
	
	if($se_prefs['prefs_shipping_costs_mode'] == 1) {
		/* flatrate shipping */
		$shipping_type = '';
		$shipping_costs = str_replace(',','.',$se_prefs['prefs_shipping_costs_flat']);
	}
	
	
	if($se_prefs['prefs_shipping_costs_mode'] == 2) {
		/* we need to determine the highest shipping category */
		/* it's stored in $store_shipping_cat */
		if($store_shipping_cat == 1) {
			$shipping_costs = str_replace(',','.',$se_prefs['prefs_shipping_costs_cat1']);
		} else if($store_shipping_cat == 2) {
			$shipping_costs = str_replace(',','.',$se_prefs['prefs_shipping_costs_cat2']);
		} else {
			$shipping_costs = str_replace(',','.',$se_prefs['prefs_shipping_costs_cat3']);
		}
		
	}
	
}

$smarty->assign('payment_methods', $payment_methods);
$smarty->assign('payment_message', $payment_message);
$smarty->assign('client_data', $client_data);

$cart_agree_term = se_get_textlib('cart_agree_term',$languagePack,'content');
$smarty->assign('cart_agree_term', $cart_agree_term);


/* calculate subtotal and total */
$cart_price_subtotal = $all_items_subtotal;
$cart_price_total = $cart_price_subtotal + $payment_costs + $shipping_costs;

/**
 * check prefs_posts_order_mode
 * 1 - order mode is active
 * 2 - buyer can only send a request
 * 3 - buyer can order or send a request
 * */

if($se_prefs['prefs_posts_order_mode'] == 2 OR $se_prefs['prefs_posts_order_mode'] == 3) {
    $smarty->assign('show_request_form', 1);

    // if this is a registered user, fill in form details
    if(isset($_SESSION['user_nick'])) {
        $smarty->assign('buyer_mail', $_SESSION['user_mail']);
        $smarty->assign('buyer_name', $_SESSION['user_nick']);
        $smarty->assign('readonly', "readonly");
    }

} else {
    $smarty->assign('show_request_form', 0);
}

if($se_prefs['prefs_posts_order_mode'] == 1 OR $se_prefs['prefs_posts_order_mode'] == 3) {
    $smarty->assign('show_submit_order_form', 1);
} else {
    $smarty->assign('show_submit_order_form', 0);
}

/**
 * client has sent a request
 * send vial mail to admin
 * reset shopping cart if data is sent
 */

if($_POST['send_request'] == 'send') {

    $send_request = false;

    /* build table from cart items */
    $table = '<table cellpadding="5">';
    for($i=0;$i<$cnt_cart_items;$i++) {
        $table .= '<tr>';
        $table .= '<td valign="top">'.$lang['label_product_info'].'</td>';
        $table .= '<td valign="top"><h5>'.$cart_item[$i]['title'].'</h5>'.$cart_item[$i]['options'].'</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<td valign="top">'.$lang['label_price'].'</td>';
        $table .= '<td valign="top">'.$cart_item[$i]['amount'].' x '.$cart_item[$i]['price_gross_single_format'].' ('.$lang['label_gross'].')</td>';
        $table .= '</tr>';
    }
    $table .= '</table>';

    $recipient['name'] = sanitizeUserInputs($_POST['buyer_name']);
    $recipient['mail'] = sanitizeUserInputs($_POST['buyer_mail']);
    $comment = sanitizeUserInputs($_POST['buyer_comment']);
    $subject = 'Order request';

    $mail_content = '<p>'.$subject.'</p>';
    $mail_content .= '<p>'.$recipient['name'].' '.$recipient['mail'].'</p>';
    $mail_content .= '<hr>';
    $mail_content .= $table;
    $mail_content .= '<hr>';
    $mail_content .= $comment;

    if($recipient['name'] != '' AND $recipient['mail'] != '') {
        $send_request = true;
    } else {
        $send_request = false;
        $send_request_msg = 'Name and E-Mail';
        $smarty->assign('send_request_msg', $send_request_msg);
        $smarty->assign('request_msg_class', 'danger');
    }

    if($send_request === true) {
        $send = se_send_mail($recipient,$subject,$mail_content);
        if($send == 1) {
            $send_request_msg = $lang['msg_request_send'];
            $smarty->assign('send_request_msg', $send_request_msg);
            $smarty->assign('request_msg_class', 'success');
            /* remove items from se_carts */
            se_clear_cart($get_cd['user_id']);
            $cnt_cart_items = 0;
        }
    }
}

/**
 * client has sent the order
 * store data in se_orders
 * reset shopping cart if data is saved
 */

if($_POST['order'] == 'send') {
	
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
		$order_data['order_products'] = $cart_items_str;
		$order_data['order_price_total'] = $cart_price_total;
		$order_data['order_shipping_type'] = $shipping_type;
		$order_data['order_shipping_costs'] = $shipping_costs;
		$order_data['order_payment_type'] = $payment_addon;
		$order_data['order_payment_costs'] = $payment_costs;
        $order_data['order_comment'] = $_POST['cart_comment'];
		
		$order_id = se_send_order($order_data);

        se_recalculate_stock_sales($cart_items);
		
		if($order_id > 0) {

            $cart_alert = se_get_textlib('cart_order_sent',$languagePack,'content');
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
            $send_mail = se_send_order_status($recipient,$order_id,$reason);

            // include after sale script from payment addon
            $aftersale_script = SE_CONTENT.'/modules/'.basename($payment_addon).'/aftersale.php';
            if(is_file($aftersale_script)) {
                include $aftersale_script;
            }

            $smarty->assign("cart_alert_success",$cart_alert,true);

		}
	}
	
}

if($checkout_error == 'missing_mandatory_informations') {
    $checkout_error_msg = $lang['msg_missing_mandatory_informations'];
}

if($checkout_error == 'missing_approval') {
    $checkout_error_msg = $lang['msg_missing_user_approval'];
}

$smarty->assign("checkout_error_msg",$checkout_error_msg,true);
$smarty->assign("cnt_items",$cnt_cart_items,true);
$smarty->assign('cart_shipping_costs', se_post_print_currency($shipping_costs), true);
$smarty->assign('cart_payment_costs', se_post_print_currency($payment_costs), true);
$smarty->assign('cart_price_subtotal', se_post_print_currency($cart_price_subtotal), true);
$smarty->assign('cart_price_total', se_post_print_currency($cart_price_total), true);
$smarty->assign('currency', $se_prefs['prefs_posts_products_default_currency'], true);
$smarty->assign('price_mode', $se_prefs['prefs_posts_price_mode'], true);

$cart_table = $smarty->fetch("shopping_cart.tpl",$cache_id);

$smarty->assign('page_content', $cart_table, true);