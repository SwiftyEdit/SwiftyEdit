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
 * @var array $se_prefs
 * @var object $db_content medoo database object
 * @var object $db_posts medoo database object
 */

$time_string_now = time();
$display_mode = 'list_posts';

/* defaults */
$posts_start = 0;
$posts_limit = (int) $se_prefs['prefs_posts_entries_per_page'];
if($posts_limit == '' || $posts_limit < 1) {
	$posts_limit = 10;
}
$posts_order = 'id';
$posts_direction = 'DESC';
$posts_filter = array();

$str_status = '1';
if(isset($_SESSION['user_class']) AND $_SESSION['user_class'] == 'administrator') {
	$str_status = '1-2';
}

$posts_filter['languages'] = $page_contents['page_language'];
$posts_filter['types'] = str_replace(",","-",$page_contents['page_posts_types']);
$posts_filter['status'] = $str_status;
$posts_filter['categories'] = $page_contents['page_posts_categories'];


if(substr("$mod_slug", -5) == '.html') {
    $mod_slug_array = explode("-", $mod_slug);
	$get_post_id = (int) basename(end($mod_slug_array));
	$display_mode = 'show_post';	
}

$all_categories = se_get_categories();
$array_mod_slug = explode("/", $mod_slug);

$this_page_categories = explode(',',$page_contents['page_posts_categories']);

foreach($all_categories as $cats) {

    if($page_contents['page_posts_categories'] != 'all') {
        if (!in_array($cats['cat_hash'], $this_page_categories)) {
            // skip this category
            continue;
        }
    }
	
	//$this_nav_cat_item = $tpl_nav_cats_item;
	$show_category_title = $cats['cat_description'];
	$show_category_name = $cats['cat_name'];
    $cat_href = '/'.$swifty_slug.$cats['cat_name_clean'].'/';

    /* show only categories that match the language */
    if($page_contents['page_language'] !== $cats['cat_lang']) {
        continue;
    }
    $cat_class = '';
    if($cats['cat_name_clean'] == $array_mod_slug[0]) {
        $cat_class = 'active';
    }

    $categories[] = array(
        "cat_href" => $cat_href,
        "cat_title" => $show_category_title,
        "cat_name" => $show_category_name,
        "cat_class" => $cat_class
    );


	if($cats['cat_name_clean'] == $array_mod_slug[0]) {
		// show only posts from this category
		$posts_filter['categories'] = $cats['cat_hash'];
		$display_mode = 'list_posts_category';
		
		if($array_mod_slug[1] == 'p') {
			if(is_numeric($array_mod_slug[2])) {
				$posts_start = $array_mod_slug[2];
			} else {
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: /$swifty_slug");
				header("Connection: close");
			}				
		}
	}
}


/* pagination f.e. /p/2/ or /p/3/ .... */
if($array_mod_slug[0] == 'p') {
	if(is_numeric($array_mod_slug[1])) {
		$posts_start = $array_mod_slug[1];
	} else {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: /$swifty_slug");
		header("Connection: close");	}
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
		$get_target_file = '../content/files/'.$post_attachment;
		
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
		$download_file = str_replace('../content/','./content/',$target_file['post_file_attachment']);
	
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
        include 'posts-list.php';
        break;
    case "show_post":
        include 'posts-display.php';
        break;
    default:
        include 'posts-list.php';
}