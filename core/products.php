<?php

$time_string_now = time();
$display_mode = 'list_products';

/* defaults */
$products_start = 0;
$products_limit = (int) $se_prefs['prefs_posts_entries_per_page'];
if($products_limit == '') {
    $products_limit = 10;
}
$products_order = 'id';
$products_direction = 'DESC';
$products_filter = array();

$str_status = '1';
if($_SESSION['user_class'] == 'administrator') {
    $str_status = '1-2';
}


if(!isset($_SESSION['custom_filter'])) {
    $_SESSION['custom_filter'] = array();
}

if(isset($_REQUEST['set_filter'])) {
    $set_filter = (int) $_REQUEST['set_filter'];

    $key = array_search($set_filter,$_SESSION['custom_filter']);
    if($key === false) {
        array_push($_SESSION['custom_filter'],"$set_filter");
    } else {
        unset($_SESSION['custom_filter'][$key]);
    }


}


$custom_filter = $_SESSION['custom_filter'];

$products_filter['languages'] = $page_contents['page_language'];
$products_filter['types'] = $page_contents['page_posts_types'];
$products_filter['status'] = $str_status;
$products_filter['categories'] = $page_contents['page_posts_categories'];
$products_filter['custom_filter'] = $custom_filter;

if(isset($_POST['sort_by'])) {
    if($_POST['sort_by'] == 'ts') {
        $_SESSION['products_sort_by'] = 'ts';
    } else if($_POST['sort_by'] == 'name') {
        $_SESSION['products_sort_by'] = 'name';
    } else if($_POST['sort_by'] == 'pasc') {
        $_SESSION['products_sort_by'] = 'pasc';
    } else if($_POST['sort_by'] == 'pdesc') {
        $_SESSION['products_sort_by'] = 'pdesc';
    } else {
        $_SESSION['products_sort_by'] = 'name';
    }
}

if($_SESSION['products_sort_by'] == 'name') {
    $smarty->assign('class_sort_name', "active");
} else if($_SESSION['products_sort_by'] == 'ts') {
    $smarty->assign('class_sort_topseller', "active");
} else if($_SESSION['products_sort_by'] == 'pasc') {
    $smarty->assign('class_sort_price_asc', "active");
} else if($_SESSION['products_sort_by'] == 'pdesc') {
    $smarty->assign('class_sort_price_desc', "active");
} else {
    $_SESSION['products_sort_by'] = 'name';
    $smarty->assign('class_sort_name', "active");
}

$products_filter['sort_by'] = $_SESSION['products_sort_by'];


if(substr("$mod_slug", -5) == '.html') {
    $get_product_id = (int) basename(end(explode("-", $mod_slug)));
    $display_mode = 'show_product';
}

$product_filter = se_get_product_filter($languagePack);


$all_categories = se_get_categories();
$array_mod_slug = explode("/", $mod_slug);

$this_page_categories = explode(',',$page_contents['page_posts_categories']);

foreach($all_categories as $cats) {

    if($page_contents['page_posts_categories'] != 'all') {
        if (!in_array($cats['cat_id'], $this_page_categories)) {
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
        $products_filter['categories'] = $cats['cat_id'];
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
if($array_mod_slug[0] == 'p' OR $array_mod_slug[1] == 'p') {

    if(is_numeric($array_mod_slug[1])) {
        $products_start = $array_mod_slug[1];
    } else if(is_numeric($array_mod_slug[2])) {
        $products_start = $array_mod_slug[2];
    } else {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /$swifty_slug");
        header("Connection: close");	}
}

/* we are on the product display page but we have no post id
 * get a shop page and redirect */

if($page_contents['page_type_of_use'] == 'display_product' AND $get_product_id == '') {
    
    $target_page = $db_content->get("se_pages", "page_permalink", [
        "AND" => [
            "page_posts_types" => "p",
            "page_language" => $page_contents['page_language']
        ]
    ]);

    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /$target_page");
    header("Connection: close");
}


switch ($display_mode) {
    case "list_products_category":
    case "list_products":
        include 'products-list.php';
        break;
    case "show_product":
        include 'products-display.php';
        break;
    default:
        include 'products-list.php';
}
