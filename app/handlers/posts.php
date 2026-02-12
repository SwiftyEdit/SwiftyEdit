<?php

/**
 * SwiftyEdit frontend
 *
 * variables
 * @var array $page_contents
 * @var string $mod_slug
 * @var string $swifty_slug
 *
 * global variables
 * @var array $se_settings
 * @var object $db_content medoo database object
 * @var object $db_posts medoo database object
 */

$time_string_now = time();
$display_mode = 'list_posts';
$status_404 = true;

// 1. Get the post ID from URL (.html format)
if(str_ends_with("$mod_slug", '.html')) {
    $mod_slug_array = explode("-", $mod_slug);
    $get_post_id = (int) basename(end($mod_slug_array));
    $display_mode = 'show_post';
}



if($page_contents['page_type_of_use'] == 'display_post' AND $get_post_id == '') {
	/* we are on the post display page but we have no post id
	 * get a blog page and redirect
	 */
	
	$target_page = $db_content->get("se_pages", "page_permalink", [
		"AND" => [
			"page_posts_categories[!]" => "",
			"page_language" => $page_contents['page_language']
		]
	]);

	
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /$target_page");
	header("Connection: close");
}

/* redirect to external link */
if(isset($_GET['goto'])) {
	
	$get_link_by_id = (int) $_GET['goto'];
	$target_post = $db_posts->get("se_posts", ["post_link","post_link_hits"], [
			"post_id" => $get_link_by_id
	]);
	
	$target_url = $target_post['post_link'];
	$upd_counter = ((int) $target_post['post_link_hits'])+1;
	
	$update_counter = $db_posts->update("se_posts", [
		"post_link_hits" => $upd_counter
	],[
		"post_id" => $get_link_by_id
	]);	
	
	$redirect = $target_url;		
	header("Location: $redirect");
	exit;
}

/* start post_attachment download */
if(isset($_POST['post_attachment'])) {
	
	if($_POST['post_attachment_external'] != '') {
		
		// external downloads
		
		$target_file = $db_posts->get("se_posts", "*", [
			"post_file_attachment_external" => $_POST['post_attachment_external']
		]);
		
		$counter = ((int) $target_file['post_file_attachment_hits'])+1;

		$update_file = $db_posts->update("se_posts", [
			"post_file_attachment_hits" => $counter
		],[
			"post_file_attachment_external" => $_POST['post_attachment_external']
		]);
		
		$redirect = $_POST['post_attachment_external'];		
		header("Location: $redirect");
		exit;
		
	} else {
		
		// file downloads fron /content/files/
		
		$post_attachment = basename($_POST['post_attachment']);
		$get_target_file = '/'.$post_attachment;
		
		$target_file = $db_posts->get("se_posts", "*", [
			"post_file_attachment" => $get_target_file
		]);

		
		$counter = ((int) $target_file['post_file_attachment_hits'])+1;
		
		$update_file = $db_posts->update("se_posts", [
			"post_file_attachment_hits" => $counter
		],[
			"post_file_attachment" => $get_target_file
		]);
		
		/* we take the filepath from the database, so we have no trouble if someone trying to inject evil filepath */
		$download_file = SE_PUBLIC.'/assets/files'.$target_file['post_file_attachment'];
	
		if(is_file($download_file)) {
			header('Content-Description: File Transfer');
			header('Content-Type: ' . mime_content_type($download_file));
			header('Content-Disposition: attachment; filename="'.basename($download_file).'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($download_file));
			readfile($download_file);
			exit;
		}
	}
}


switch ($display_mode) {
    case "list_posts_category":
    case "list_posts":
        include __DIR__.'/posts-list.php';
        break;
    case "show_post":
        include __DIR__.'/posts-display.php';
        break;
    default:
        include __DIR__.'/posts-list.php';
}