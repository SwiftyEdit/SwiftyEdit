<?php

/**
 * SwiftyEdit - shop and products main file
 *
 * global variables
 * @var $db_content object database
 * @var $smarty
 * @var $languagePack
 * @var $se_prefs array
 * @var $page_contents array
 * @var $swifty_slug string
 * @var $mod_slug
 *
 * possible urls for this module
 *
 * listing
 * /page/
 * /page/my-category/
 * /page/p/n/
 * /page/my-category/p/n/
 *
 * show product
 * /page/product-slug/
 * /page/product-title-id.html
 */

$time_string_now = time();
$display_mode = 'list_products';
$status_404 = true;

/* defaults */
$products_start = 0;
$products_limit = (int) $se_prefs['prefs_products_per_page'];
if($products_limit == '' || $products_limit < 1) {
    $products_limit = 10;
}
$products_order = 'id';
$products_direction = 'DESC';
$products_filter = array();

$str_status = '1';
if(isset($_SESSION['user_class']) && $_SESSION['user_class'] == 'administrator') {
    $str_status = '1-2';
}


if(!isset($_SESSION['custom_filter'])) {
    $_SESSION['custom_filter'] = array();
}

/* add filter to SESSION['custom_filter'] */
if(isset($_REQUEST['add_filter'])) {
    $get_filters = explode("-",$_REQUEST['add_filter']);
    foreach($get_filters as $filter) {
        $set_filter = (int) $filter;
        $key = array_search($set_filter,$_SESSION['custom_filter']);
        if($key === false) {
            array_push($_SESSION['custom_filter'],"$set_filter");
        }
    }
}

/* remove filter to SESSION['custom_filter'] */
if(isset($_REQUEST['remove_filter'])) {
    $get_filters = explode("-",$_REQUEST['remove_filter']);
    foreach($get_filters as $filter) {
        $remove_filter = (int) $filter;
        $key = array_search($remove_filter,$_SESSION['custom_filter']);
        if($key !== false) {
            unset($_SESSION['custom_filter'][$key]);
        }
    }
}

if(isset($_POST['set_custom_filters'])) {

    $sf_radios = $_POST['sf_radio'];
    // loop through all radios and unset them from session
    if(is_array($_POST['all_radios'])) {
        foreach($_POST['all_radios'] as $radios) {
            if (($key = array_search($radios, $_SESSION['custom_filter'])) !== false) {
                unset($_SESSION['custom_filter'][$key]);
            }
        }
    }

    if(is_array($sf_radios)) {
        foreach ($sf_radios as $radio) {
            if(is_numeric($radio[0])) {
                $_SESSION['custom_filter'][] = $radio[0];
            }
        }
    }

    foreach($_POST['all_checks'] as $checkboxes) {

        $sf_checkboxes = $_POST['sf_checkbox'];
        if(!is_array($sf_checkboxes)) {
            // no checkboxes are checked
            if (($key = array_search($checkboxes, $_SESSION['custom_filter'])) !== false) {
                unset($_SESSION['custom_filter'][$key]);
            }
            continue;
        }
        $key = array_search($checkboxes,$sf_checkboxes);
        if($key !== false) {
            $_SESSION['custom_filter'][] = $checkboxes;
        } else {
            if (($key = array_search($checkboxes, $_SESSION['custom_filter'])) !== false) {
                unset($_SESSION['custom_filter'][$key]);
            }
        }
    }

}

$_SESSION['custom_filter'] = array_unique($_SESSION['custom_filter']);
$_SESSION['custom_filter'] = array_values($_SESSION['custom_filter']);

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

/* get the default sorting */

if(!isset($_SESSION['products_sort_by'])) {
    if($se_prefs['prefs_product_sorting'] == 1) {
        $_SESSION['products_sort_by'] = '';
    } else if($se_prefs['prefs_product_sorting'] == 2) {
        $_SESSION['products_sort_by'] = 'ts';
    } else if($se_prefs['prefs_product_sorting'] == 3) {
        $_SESSION['products_sort_by'] = 'name';
    } else if($se_prefs['prefs_product_sorting'] == 4) {
        $_SESSION['products_sort_by'] = 'pasc';
    } else if($se_prefs['prefs_product_sorting'] == 5) {
        $_SESSION['products_sort_by'] = 'pdesc';
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
    $_SESSION['products_sort_by'] = '';
}

$products_filter['sort_by'] = $_SESSION['products_sort_by'];

/* get the product id from url */
if(substr("$mod_slug", -5) == '.html') {
    $file_parts = explode("-", $mod_slug);
    $get_product_id = (int) basename(end($file_parts));
    $product_data = se_get_product_data($get_product_id);

    if(is_array($product_data)){
        $status_404 = false;
    }

    $display_mode = 'show_product';
}

/* check if $mod_slug is a product slug */
$get_data_from_slug = se_get_product_data_by_slug($mod_slug);
if(is_array($get_data_from_slug)) {
    $get_product_id = (int) $get_data_from_slug['id'];
    $product_data = se_get_product_data($get_product_id);

    if(is_array($product_data)){
        $status_404 = false;
    }

    $display_mode = 'show_product';
}

$all_categories = se_get_categories();
$array_mod_slug = explode("/", $mod_slug);

if(!isset($array_mod_slug[0])) {
    $array_mod_slug[0] = '';
    $status_404 = false;
}
if(!isset($array_mod_slug[1])) {
    $array_mod_slug[1] = '';
    $status_404 = false;
}

$this_page_categories = explode(',',$page_contents['page_posts_categories']);
if($this_page_categories[0] == 'all') {
    foreach($all_categories as $cat) {
        $this_page_categories[] = $cat['cat_id'];
    }
}

/* get all filters */
$get_product_filter = se_get_product_filter($languagePack);

/* check which filters should be displayed on this page */
$product_filter = array();
foreach($get_product_filter as $k => $v) {

    $this_filters_array = explode(",",$v['categories']);

    foreach($this_page_categories as $c) {
        if(in_array("$c",$this_filters_array)) {
            $product_filter[] = $get_product_filter[$k];
            continue;
        }
        if(in_array("all",$this_filters_array)) {
            $product_filter[] = $get_product_filter[$k];
        }
    }
}
$product_filter = array_values(array_column($product_filter, null, 'title'));

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
        "cat_class" => $cat_class,
        "cat_hash" => $cats['cat_hash']
    );


    if($cats['cat_name_clean'] == $array_mod_slug[0]) {
        // show only posts from this category
        $products_filter['categories'] = $cats['cat_hash'];
        $display_mode = 'list_products_category';
        $status_404 = false;

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


/**
 * pagination
 * for example /my-page/p/3/ or /my-page/my-category/p/3/
 */
if($array_mod_slug[0] == 'p' OR $array_mod_slug[1] == 'p') {

    $status_404 = false;

    if(is_numeric($array_mod_slug[1])) {
        $products_start = $array_mod_slug[1];
    } else if(is_numeric($array_mod_slug[2])) {
        $products_start = $array_mod_slug[2];
    } else {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /$swifty_slug");
        header("Connection: close");
    }
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
