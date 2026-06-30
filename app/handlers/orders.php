<?php

/* get permalink for orders page */
$order_page = se_get_type_of_use_pages('orders');
if($order_page['page_permalink'] == '') {
	$order_page_uri = '/orders/';
} else {
	$order_page_uri = '/'.$order_page['page_permalink'];
}



/* start purchased download */
if(isset($_POST['dl_p_file']) OR isset($_POST['dl_p_file_ext'])) {
	
	$product_id = 0;
	$mode = '';
	if(isset($_POST['dl_p_file']) && is_numeric($_POST['dl_p_file'])) {
		$product_id = (int) $_POST['dl_p_file'];
		$mode = 'internal_file';
	}
	if(isset($_POST['dl_p_file_ext']) && is_numeric($_POST['dl_p_file_ext'])) {
		$product_id = (int) $_POST['dl_p_file_ext'];
		$mode = 'external_file_file';
	}

	// authorize: the customer must own a paid order that contains this product
	$current_user_id = (int) ($_SESSION['user_id'] ?? 0);
	$this_order = se_get_owned_order($_POST['order_id'] ?? '', $current_user_id);
	if($product_id < 1
		|| $this_order === null
		|| $this_order['order_status_payment'] != '2'
		|| !se_order_contains_product($this_order, $product_id)) {
		http_response_code(403);
		exit;
	}
	
	$this_item = se_get_product_data($product_id);

	if($mode == 'internal_file') {
		$download_file = se_resolve_within(SE_PUBLIC.'/assets/files', $this_item['file_attachment_as']);
		$set_filename = basename($_POST['order_id']);

		if($download_file !== false && is_file($download_file)) {
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
            http_response_code(404);
            exit;
        }
		
	} else {
		$download = $this_item['file_attachment_external'];
		header("Location: $download");
		exit;
	}

}

/**
 * download customers' uploads
 * we do not provide a preview, but the customer can download his files
 */

if(isset($_POST['download_user_file'])) {

    // authorize: the customer must own this order
    if(se_get_owned_order($_POST['order'] ?? '', (int) ($_SESSION['user_id'] ?? 0)) === null) {
        http_response_code(403);
        exit;
    }

    $target_dir = SE_CONTENT.'/uploads/';
    // confine the lookup prefix to the uploads directory (no traversal via order/pos)
    $check_prefix = se_resolve_within($target_dir, $_POST['order'].'-'.$_POST['pos'].'-');
    if($check_prefix === false) {
        http_response_code(400);
        exit;
    }
    $checkfile = glob($check_prefix.'*');
    if(is_array($checkfile) && !empty($checkfile) && $checkfile[0] != '') {

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

    // authorize: the customer must own this order
    if(se_get_owned_order($_POST['order'] ?? '', (int) ($_SESSION['user_id'] ?? 0)) === null) {
        http_response_code(403);
        exit;
    }

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

    // confine the lookup prefix to the uploads directory (no traversal via order/pos)
    $check_prefix = se_resolve_within($target_dir, $_POST['order'].'-'.$_POST['pos'].'-');
    if($check_prefix === false) {
        $start_upload = false;
    } else {
        $checkfile = glob($check_prefix.'*');
        if(is_array($checkfile) && !empty($checkfile) && $checkfile[0] != '') {
            // there is already an upload for this, delete it first
            unlink($checkfile[0]);
        }
    }

    $filename = $_POST['order'].'-'.$_POST['pos'].'-'.time().'.'.$extension;
    // confine the upload target so order/pos cannot write outside the uploads dir
    $target_file = se_resolve_within($target_dir, $filename);
    if($target_file === false) {
        $start_upload = false;
    }
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


$smarty->assign('order_page_uri', $order_page_uri);


$orders_table = $smarty->fetch("orders.tpl",$cache_id);

$smarty->assign('page_content', $orders_table, true);