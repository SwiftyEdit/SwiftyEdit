<?php

/* get permalink for orders page */
$order_page = se_get_type_of_use_pages('orders');
if($order_page['page_permalink'] == '') {
	$order_page_uri = '/orders/';
} else {
	$order_page_uri = '/'.$order_page['page_permalink'];
}
	
$smarty->assign('order_page_uri_uri', $order_page_uri);


/* start purchased download */
if(isset($_POST['dl_p_file']) OR isset($_POST['dl_p_file_ext'])) {
	
	if(is_numeric($_POST['dl_p_file'])) {
		$product_id = (int) $_POST['dl_p_file'];
		$mode = 'internal_file';
	}
	if(is_numeric($_POST['dl_p_file_ext'])) {
        $product_id = (int) $_POST['dl_p_file_ext'];
		$mode = 'external_file_file';
	}
	
	$this_item = se_get_product_data($product_id);

	if($mode == 'internal_file') {
		//$download_file = str_replace('../content/','./content/',$this_item['file_attachment_as']);
        $download_file = SE_CONTENT.'/files'.$this_item['file_attachment_as'];
		$pathinfo = pathinfo($download_file);
		//print_r($pathinfo);
		$set_filename = $_POST['order_id'];

		if(is_file($download_file)) {
			header('Content-Description: File Transfer');
			header('Content-Type: ' . mime_content_type($download_file));
			header('Content-Disposition: attachment; filename="'.$set_filename.'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($download_file));
			readfile($download_file);
			exit;
		} else {
            echo '<div class="alert alert-warning">DOWNLOAD - FILE NOT FOUND</div>';
        }
		
	} else {
		$download = $this_item['file_attachment_external'];
		header("Location: $download");
		exit;
	}

}

/**
 * download customers uploads
 * we do not provide a preview but the customer can download his files
 */

if(isset($_POST['download_user_file'])) {

    $target_dir = SE_CONTENT.'/uploads/';
    $check_filename = $target_dir.$_POST['order'].'-'.$_POST['pos'].'-';
    $checkfile = glob("$check_filename*");
    if(is_array($checkfile) && $checkfile[0] != '') {

        $download_file = $checkfile[0];
        $path_parts = pathinfo($download_file);
        $extension = strtolower($path_parts['extension']);

        $set_filename = $_POST['order'].'-'.$_POST['pos'].'.'.$extension;
        if(is_file($download_file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: ' . mime_content_type($download_file));
            header('Content-Disposition: attachment; filename="'.$set_filename.'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($download_file));
            readfile($download_file);
            exit;
        }
    }
}

/**
 * @var array $se_upload_frontend_types from config.php
 */
if(isset($_POST['startUpload'])) {

    $start_upload = true;
    $upload_status = '';
    $target_dir = SE_CONTENT.'/uploads/';

    $get_name = $_FILES["upload_file"]["name"];
    $path_parts = pathinfo($get_name);
    $extension = strtolower($path_parts['extension']);

    if(!in_array("$extension",$se_upload_frontend_types)) {
        $start_upload = false;
    }

    /**
     * we don't want anyone to know what the file is named on the server
     * filename is order number + item position + time + extension
     */

    $check_filename = $target_dir.$_POST['order'].'-'.$_POST['pos'].'-';
    $checkfile = glob("$check_filename*");
    if(is_array($checkfile) && $checkfile[0] != '') {
        // there is already an upload for this, delete it first
        unlink($checkfile[0]);
    }

    $filename = $_POST['order'].'-'.$_POST['pos'].'-'.time().'.'.$extension;
    $target_file = $target_dir.$filename;
    if($start_upload == true) {
        if (move_uploaded_file($_FILES["upload_file"]["tmp_name"], $target_file)) {
            $upload_status = 'success';
        } else {
            $upload_status = 'failed';
        }
    }

    if($upload_status == 'success') {
        $smarty->assign('upload_message_class', 'success');
        $smarty->assign('upload_message', 'success message');
    } else {
        $smarty->assign('upload_message_class', 'danger');
        $smarty->assign('upload_message', 'error message');
    }

}


/**
 * show orders
 */

$user_id = (int) $_SESSION['user_id'];
$order_filter = array();
$order_filter['status_payment'] = [];
$order_filter['status_shipping'] = [];
$order_filter['status_order'] = [];

$order_sort['key'] = '';
$order_sort['direction'] = '';

$get_orders = se_get_orders($user_id,$order_filter,$order_sort);
$cnt_orders = count($get_orders);

for($i=0;$i<$cnt_orders;$i++) {
	
	$order_item[$i]['nbr'] = $get_orders[$i]['order_nbr'];
	$order_item[$i]['date'] = date("d.m.Y H:i",$get_orders[$i]['order_time']);
	$order_item[$i]['status'] = $get_orders[$i]['order_status'];
	$order_item[$i]['status_payment'] = $get_orders[$i]['order_status_payment'];
    $order_item[$i]['status_shipping'] = $get_orders[$i]['order_status_shipping'];
    $order_item[$i]['currency'] = $get_orders[$i]['order_currency'];

	$order_item[$i]['price'] = se_post_print_currency($get_orders[$i]['order_price_total']);
	
	$order_products = json_decode($get_orders[$i]['order_products'],true);
    $cnt_order_products = 0;
    if(is_array($order_products)) {
	    $cnt_order_products = count($order_products);
    }
	//print_r($order_products);
	
	$products_str = '';
    $products = array();
	/* loop through purchased items */
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
		
		if($items_download != '') {
			$products[$x]['dl_file'] = $items_download;
			
		}
		if($items_download_external != '') {
			$products[$x]['dl_file_ext'] = $items_download_external;
		}

		
	}
	
	$order_item[$i]['products'] = $products;
		
	//$order_item[$i]['products'] = $products_str;
		
}

$smarty->assign('order_page_uri', $order_page_uri);
$smarty->assign('orders', $order_item);


$orders_table = $smarty->fetch("orders.tpl",$cache_id);

$smarty->assign('page_content', $orders_table, true);