<?php
/**
 * Products Handler - Shop System
 * SwiftyEdit CMS
 *
 * Handles product listing and display
 *
 * Possible URLs:
 * - Listing: /page/, /page/my-category/, /page/p/n/, /page/my-category/p/n/
 * - Show product: /page/product-slug/, /page/product-slug/?values, /page/product-title-id.html
 */

$time_string_now = time();
$display_mode = 'list_products';
$status_404 = true;

// 1. Get the product ID from URL (.html format)
if(substr("$mod_slug", -5) == '.html') {
    $file_parts = explode("-", $mod_slug);
    $get_product_id = (int) basename(end($file_parts));
    $product_data = se_get_product_data($get_product_id);

    if(is_array($product_data)){
        $status_404 = false;
    }

    $display_mode = 'show_product';
}

// 2. Check if we have to display a variant (?v= parameter)
if(isset($_REQUEST['v']) && (is_numeric($_REQUEST['v']))) {
    $get_product_id = (int) $_REQUEST['v'];
    $product_data = se_get_product_data($get_product_id);
    if(is_array($product_data)){
        $status_404 = false;
    }
    $display_mode = 'show_product';
}

// 3. Check if $mod_slug is a product slug
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

// Handle redirect if on display_product page but no product ID
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
    exit;
}

// Route to appropriate handler
switch ($display_mode) {
    case "list_products_category":
    case "list_products":
        include __DIR__.'/products-list.php';
        break;
    case "show_product":
        include __DIR__.'/products-display.php';
        break;
    default:
        include __DIR__.'/products-list.php';
}