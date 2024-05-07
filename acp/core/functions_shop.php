<?php
/**
 * prohibit unauthorized access
 */
if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])){ 
	die ('<h2>Direct File Access Prohibited</h2>');
}


/**
 * update order
 * $id (int) id of the order
 * $data (array) order data
 * returns the number of rows affected 1 = success
 */

function se_update_order($data) {
	
	global $db_content;
	
	$id = (int) $data['open_order'];
	
	$status_order = (int) $data['status_order'];
	$status_payment = (int) $data['status_payment'];
	$status_shipping = (int) $data['status_shipping'];
	
	$update = $db_content->update("se_orders", [
		"order_status" => $status_order,
		"order_status_shipping" => $status_shipping,
		"order_status_payment" => $status_payment
	],[
		"id" => $id
	]);


	return $update->rowCount();
}

/**
 * get all products
 * @return mixed
 */
function se_get_all_products() {

    global $db_posts;

    $products = $db_posts->select("se_products",["id","title","product_lang"]);
    return $products;
}