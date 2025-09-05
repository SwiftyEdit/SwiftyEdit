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
 * /page/product-slug/?values
 * /page/product-title-id.html
 */

$time_string_now = time();
$display_mode = 'list_products';
$status_404 = true;


// 1. get the product id from url
if(substr("$mod_slug", -5) == '.html') {
    $file_parts = explode("-", $mod_slug);
    $get_product_id = (int) basename(end($file_parts));
    $product_data = se_get_product_data($get_product_id);

    if(is_array($product_data)){
        $status_404 = false;
    }

    $display_mode = 'show_product'; // change display mode
}

// 2. check if we have to display a variant
if(isset($_REQUEST['v']) && (is_numeric($_REQUEST['v']))) {
    $get_product_id = (int) $_REQUEST['v'];
    $product_data = se_get_product_data($get_product_id);
    if(is_array($product_data)){
        $status_404 = false;
    }
    $display_mode = 'show_product'; // change display mode
}

// 3. check if $mod_slug is a product slug
if($mod_slug != '' && $display_mode == 'list_products') {
    $get_data_from_slug = se_get_product_data_by_slug($mod_slug);
    if (is_array($get_data_from_slug)) {
        $get_product_id = (int)$get_data_from_slug['id'];
        $product_data = $get_data_from_slug;

        if (is_array($product_data)) {
            $status_404 = false;
        }
        $display_mode = 'show_product';
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
