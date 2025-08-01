<?php

/**
 * show all orders
 * or show order details
 *
 * @var object $smarty
 *
 */

foreach($lang as $key => $val) {
    $smarty->assign("lang_$key", $val);
}

$order_page = se_get_type_of_use_pages('orders');
if($order_page['page_permalink'] == '') {
    $order_page_uri = '/orders/';
} else {
    $order_page_uri = '/'.$order_page['page_permalink'];
}


// show order by id
if(isset($_GET['id'])) {

    $get_order_id = (int) $_GET['id'];
    $get_order = se_get_order_details($get_order_id);

    if($get_order['user_id'] !== $_SESSION['user_id']) {
        exit;
    }

    // products in this order
    $order_products = json_decode($get_order['order_products'],true);
    $cnt_order_products = is_array($order_products) ? count($order_products) : 0;
    $products = [];
    for($x=0;$x<$cnt_order_products;$x++) {
        unset($this_item);
        $post_id = $order_products[$x]['post_id'];
        $this_item = se_get_product_data($post_id);

        $this_item_price_gross = se_post_print_currency($order_products[$x]['price_gross_raw']);

        $products[$x]['pos'] = $x+1;
        $products[$x]['title'] = $order_products[$x]['title'];
        $products[$x]['options'] = $order_products[$x]['options'];
        $products[$x]['options_comment'] = $order_products[$x]['options_comment'];
        $products[$x]['options_comment_label'] = $order_products[$x]['options_comment_label'];
        $products[$x]['product_nbr'] = $order_products[$x]['product_number'];
        $products[$x]['amount'] = $order_products[$x]['amount'];
        $products[$x]['price_gross'] = $this_item_price_gross;
        $products[$x]['post_id'] = $post_id;

        // check if item needs an upload
        if($order_products[$x]['need_upload'] == 'true') {
            $products[$x]['need_upload'] = $order_products[$x]['need_upload'];
            /* filename for this upload order number + pos + time() */
            $check_dir = SE_CONTENT.'/uploads/';
            $check_filename = $check_dir.$order_item[$i]['nbr'].'-'.$products[$x]['pos'].'-';
            $checkfile = glob("$check_filename*");

            $products[$x]['user_upload'] = '';
            $products[$x]['user_upload_status'] = '';
            if(is_array($checkfile) && $checkfile[0] != '') {
                $products[$x]['user_upload'] = $checkfile[0];
                $products[$x]['user_upload_status'] = 'uploaded';
            }
        }

        // check if this item has an attachment
        $items_download = $this_item['file_attachment'];
        $items_download_external = $this_item['file_attachment_external'];

        // File that is only available after payment
        $products[$x]['file_attachment_as'] = $this_item['file_attachment_as'];

        if($items_download_external != '') {
            $products[$x]['dl_file_ext'] = $items_download_external;
        }
    }

    $smarty->assign('products', $products);

    // payment plugin
    $payment_plugin = '';
    $pm_plugin_str = '';
    if($get_order['order_status_payment'] == '1' AND $get_order['order_price_total'] > 0) {
        // unpaid order
        $order_data['order_nbr'] = $get_order['order_nbr'];
        $order_data['order_price_total'] = $get_order['order_price_total'];

        $payment_plugin = basename($get_order['order_payment_type']);
        $payment_plugin_file = SE_ROOT.'plugins/'.$payment_plugin.'/aftersale_listing.php';
        if(is_file($payment_plugin_file)) {
            include $payment_plugin_file;
        }
        $get_order['payment_plugin_str'] = $pm_plugin_str;
    }



    $smarty->assign('order_time', date("d.m.Y H:i",$get_order['order_time']));
    $smarty->assign('order_nbr', $get_order['order_nbr']);
    $smarty->assign('order_currency', $get_order['order_currency']);
    $smarty->assign('order_price_total', se_post_print_currency($get_order['order_price_total']));
    $smarty->assign('payment_plugin_str', $get_order['payment_plugin_str']);
    $smarty->assign('order_billing_address', $get_order['order_invoice_address']);
    $smarty->assign('order_shipping_address', $get_order['order_shipping_address']);
    $smarty->assign('order_status', $get_order['order_status']);
    $smarty->assign('order_status_payment', $get_order['order_status_payment']);
    $smarty->assign('order_status_shipping', $get_order['order_status_shipping']);
    $smarty->assign('order_page_uri', $order_page_uri);

    $smarty->display('order-item.tpl');
    exit;
}

// list orders

$user_id = (int) $_SESSION['user_id'];
$order_filter = array();
$order_filter['status_payment'] = [];
$order_filter['status_shipping'] = [];
$order_filter['status_order'] = [];

$order_sort['key'] = '';
$order_sort['direction'] = '';

$items_limit = 10;
$items_start = 0;
$currentPage = 1;

$cnt_all_orders = $db_content->count("se_orders", [
    "user_id" => $user_id
]);

if(isset($_GET['next_page'])) {
    $currentPage = (int) $_GET['next_page'];
}
if(isset($_GET['prev_page'])) {
    $currentPage = (int) $_GET['prev_page'];
}

if($currentPage < 1) {
    $currentPage = 1;
}

$nextPage = $currentPage + 1;
$prevPage = $currentPage - 1;

$cnt_pages = ceil($cnt_all_orders / $items_limit);

if($currentPage >= $cnt_pages) {
    $currentPage = $cnt_pages;
    $nextPage = $currentPage;
}

if($cnt_all_orders > $items_limit) {
    // show products pagination
    $smarty->assign('show_order_pagination', true);
    $smarty->assign('next_page_nbr', "$nextPage");
    $smarty->assign('prev_page_nbr', "$prevPage");
}

$items_start = (int) $items_limit*($currentPage-1);


$get_orders = se_get_orders($user_id,$order_filter,$order_sort,$items_start,$items_limit);
$cnt_orders = count($get_orders);

for($i=0;$i<$cnt_orders;$i++) {

    $order_item[$i]['id'] = $get_orders[$i]['id'];
    $order_item[$i]['nbr'] = $get_orders[$i]['order_nbr'];
    $order_item[$i]['date'] = date("d.m.Y H:i",$get_orders[$i]['order_time']);
    $order_item[$i]['status'] = $get_orders[$i]['order_status'];
    $order_item[$i]['status_payment'] = $get_orders[$i]['order_status_payment'];

    $order_item[$i]['price'] = se_post_print_currency($get_orders[$i]['order_price_total']);

}

$smarty->assign('orders', $order_item);
$smarty->display('orders-list.tpl');
exit;