<?php

/**
 * SwiftyEdit - list products
 *
 * global variables
 * @var array $se_prefs global preferences
 * @var string $se_base_url the base url
 * @var array $page_contents
 * @var string $page_content
 * @var string $swifty_slug
 * @var object $smarty smarty templates
 * @var string $img_path
 * @var array $lang translations
 * @var string $mod_slug is set in core/products.php
 * @var string $cache_id smarty cache id
 *
 * variables from parent file
 * @var integer $products_start
 * @var integer $products_limit
 * @var array $product_filter
 * @var array $products_filter
 * @var string $display_mode
 * @var array $all_categories
 * @var array $categories
 *
 */

// get the product-page by 'type_of_use' and $languagePack
$target_page = $db_content->select("se_pages", "page_permalink", [
    "AND" => [
        "page_type_of_use" => "display_product",
        "page_language" => $page_contents['page_language']
    ]
]);

if(!isset($target_page[0]) OR $target_page[0] == '') {
    $target_page[0] = $swifty_slug;
}

$sql_start = ($products_start * $products_limit) - $products_limit;
if ($sql_start < 0) {
    $sql_start = 0;
}

$get_products = se_get_products($sql_start, $products_limit, $products_filter);
$cnt_filter_products = $get_products[0]['cnt_products_match'];
$cnt_get_products = count($get_products);

$show_products_list = true;
if($get_products[0]['cnt_products_match'] < 1) {
    // we have no products to show
    $show_products_list = false;
}

$nextPage = $products_start + $products_limit;
$prevPage = $products_start - $products_limit;
$cnt_pages = ceil($cnt_filter_products / $products_limit);

if ($cnt_pages > 1) {
    $show_pagination = true;
    $pagination = array();

    for ($i = 0; $i < $cnt_pages; $i++) {

        $active_class = '';
        $set_start = $i + 1;

        if ($i == 0 && $products_start < 1) {
            $set_start = 1;
            $active_class = 'active';
            $current_page = 1;
        }

        if ($set_start == $products_start) {
            $active_class = 'active';
            $current_page = $set_start;
        }

        $pagination_link = se_set_pagination_query($display_mode, $set_start);

        $pagination[] = array(
            "href" => $pagination_link,
            "nbr" => $set_start,
            "active_class" => $active_class
        );
    }

    $pag_start = $current_page - 4;

    if ($pag_start < 0) {
        $pag_start = 0;
    }
    $pagination = array_slice($pagination, $pag_start, 5);

    $nextstart = $products_start + 1;
    $prevstart = $products_start - 1;
    if($nextstart < 2) {
        $nextstart = 2;
    }

    $older_link_query = se_set_pagination_query($display_mode, $nextstart);
    $newer_link_query = se_set_pagination_query($display_mode, $prevstart);

    if ($prevstart < 1) {
        $prevstart = 1;
        $disable_prev_link = true;
        $newer_link_query = '#';
    } else {
        $disable_prev_link = false;
    }

    if ($nextstart > $cnt_pages) {
        $disable_next_link = true;
        $older_link_query = '#';
    }

    $smarty->assign('pag_prev_href', $newer_link_query);
    $smarty->assign('pag_next_href', $older_link_query);

} else {
    $show_pagination = false;
}


$show_start = $sql_start + 1;
$show_end = $show_start + ($products_limit - 1);

if ($show_end > $cnt_filter_products) {
    $show_end = $cnt_filter_products;
}

/**
 * check if we hide the shopping cart button
 * @var $show_shopping_cart true|false
 * if $show_shopping_cart = true, check item's settings
 */
if($se_prefs['prefs_posts_products_cart'] == 2 OR $se_prefs['prefs_posts_products_cart'] == 3) {
    $show_shopping_cart = true; // show it
    if($se_prefs['prefs_posts_products_cart'] == 2 && $_SESSION['user_nick'] == '') {
        $show_shopping_cart = false; // show it only for registered users
    }
} else {
    $show_shopping_cart = false; // hide it
}

//eol pagination

$posts_list = '';
foreach ($get_products as $k => $post) {

    if(!isset($get_products[$k]['id'])) {
        continue;
    }

    if(!isset($get_products[$k]['author'])) {
        $get_products[$k]['author'] = '';
    }

    /* build data for template */

    $get_products[$k]['product_title'] = $get_products[$k]['title'];
    $get_products[$k]['product_teaser'] = htmlspecialchars_decode($get_products[$k]['teaser']);
    $get_products[$k]['product_text'] = htmlspecialchars_decode($get_products[$k]['text']);

    /* post images */
    $first_post_image = '';
    $post_images = explode("<->", $get_products[$k]['images']);
    if (isset($post_images[1]) AND $post_images[1] != "") {
        $get_products[$k]['product_img_src'] = $post_images[1];
    } else {
        $get_products[$k]['product_img_src'] = '';
    }

    $post_filename = basename($get_products[$k]['slug']);
    $get_products[$k]['product_href'] = SE_INCLUDE_PATH . "/" . $target_page[0] . "$post_filename-" . $get_products[$k]['id'] . ".html";
    if($get_products[$k]['slug'] != '') {
        $get_products[$k]['product_href'] = SE_INCLUDE_PATH . "/" . $target_page[0] . $get_products[$k]['slug'];
    }

    $post_releasedate = date($se_prefs['prefs_dateformat'], $get_products[$k]['releasedate']);
    $post_releasedate_year = date('Y', $get_products[$k]['releasedate']);
    $post_releasedate_month = date('m', $get_products[$k]['releasedate']);
    $post_releasedate_day = date('d', $get_products[$k]['releasedate']);
    $post_releasedate_time = date($se_prefs['prefs_timeformat'], $get_products[$k]['releasedate']);

    $get_products[$k]['releasedate'] = $post_releasedate;

    /* entry date */
    $entrydate_year = date('Y', $get_products[$k]['date']);


    /* product categories */
    $post_categories = explode('<->', $get_products[$k]['categories']);
    $category = array();
    foreach ($all_categories as $cats) {
        if (in_array($cats['cat_hash'], $post_categories)) {
            $cat_href = '/' . $swifty_slug . $cats['cat_name_clean'] . '/';
            $category[] = array(
                "cat_href" => $cat_href,
                "cat_title" => $cats['cat_name']
            );
        }
    }
    $get_products[$k]['product_categories'] = $category;

    /* vote up or down this product */
    if ($get_products[$k]['votings'] == 2 || $get_products[$k]['votings'] == 3) {
        $get_products[$k]['show_voting'] = true;
        $voter_data = false;
        $voting_type = array("upv", "dnv");
        if ($get_products[$k]['votings'] == 2) {
            if ($_SESSION['user_nick'] == '') {
                $voter_data = false;
            } else {
                $voter_data = se_check_user_legitimacy($get_products[$k]['id'], $_SESSION['user_nick'], $voting_type);
            }
        }

        if ($get_products[$k]['votings'] == 3) {
            if (!isset($_SESSION['user_nick']) OR $_SESSION['user_nick'] == '') {
                $voter_name = se_generate_anonymous_voter();
                $voter_data = se_check_user_legitimacy($get_products[$k]['id'], $voter_name, $voting_type);
            } else {
                $voter_data = se_check_user_legitimacy($get_products[$k]['id'], $_SESSION['user_nick'], $voting_type);
            }
        }

        if ($voter_data == true) {
            // user can vote
            $get_products[$k]['votes_status_up'] = '';
            $get_products[$k]['votes_status_dn'] = '';
        } else {
            $get_products[$k]['votes_status_up'] = 'disabled';
            $get_products[$k]['votes_status_dn'] = 'disabled';
        }


        $votes = se_get_voting_data('post', $get_products[$k]['id']);

        $get_products[$k]['votes_up'] = (int) $votes['upv'];
        $get_products[$k]['votes_dn'] = (int) $votes['dnv'];

    } else {
        $get_products[$k]['show_voting'] = false;
    }

    /* check for variants */
    $variants = [];
    $variants = se_get_product_variants($get_products[$k]['id']);
    $cnt_variants = count($variants);
    if($cnt_variants > 1) {
        $get_products[$k]['variants_alert'] = sprintf($lang['label_nbr_of_product_variants'],$cnt_variants);
    }


    // tax
    if ($product_tax == '1') {
        $tax = $se_prefs['prefs_posts_products_default_tax'];
    } else if ($product_tax == '2') {
        $tax = $se_prefs['prefs_posts_products_tax_alt1'];
    } else {
        $tax = $se_prefs['prefs_posts_products_tax_alt2'];
    }

    // price
    if($get_products[$k]['product_price_group'] != '' AND $get_products[$k]['product_price_group'] != 'null') {

        $price_data = se_get_price_group_data($get_products[$k]['product_price_group']);

        $product_tax = $price_data['tax'];
        $product_price_net = $price_data['price_net'];
        $product_volume_discounts = $price_data['price_volume_discount'];
    } else {
        $product_tax = $get_products[$k]['product_tax'];
        $product_price_net = $get_products[$k]['product_price_net'];
        $product_volume_discounts = $get_products[$k]['product_price_volume_discount'];
    }

    $get_products[$k]['price_tag_label_from'] = '';
    if($product_volume_discounts != 'null') {
        // if we have volume discounts, show the cheapest
        $product_volume_discounts_array = json_decode($product_volume_discounts, true);
        if(is_array($product_volume_discounts_array)) {
            $priceValues = array_map(fn($p) => (float)str_replace(',', '.', $p['price']), $product_volume_discounts_array);
            $product_price_net = min($priceValues);
            $product_price_net = str_replace('.', ',', $product_price_net);
            $get_products[$k]['price_tag_label_from'] = $lang['price_tag_label_from'];
        }
    }

    // check if we have cheaper price from variants
    // check volume discounts, also
    $variant_prices = [];
    if($cnt_variants > 1) {
        foreach($variants as $variant) {
            $variant_prices[] = $db_posts->get("se_products",["product_price_net","product_price_volume_discount"],[
               "id" => $variant['id']
            ]);
        }

        $allPrices = [];
        foreach ($variant_prices as $product) {
            // Convert base price to float
            $basePrice = floatval(str_replace(',', '.', $product['product_price_net']));
            $allPrices[] = $basePrice;

            // If there are volume discounts, decode JSON and collect prices
            if (!empty($product['product_price_volume_discount'])) {
                $discounts = json_decode($product['product_price_volume_discount'], true);
                if (is_array($discounts)) {
                    foreach ($discounts as $entry) {
                        $discountPrice = floatval(str_replace(',', '.', $entry['price']));
                        $allPrices[] = $discountPrice;
                    }
                }
            }
        }

        $product_price_net = min($allPrices);
        $product_price_net = str_replace('.', ',', $product_price_net);
        $get_products[$k]['price_tag_label_from'] = $lang['price_tag_label_from'];
    }

    $post_prices = se_posts_calc_price($product_price_net, $tax);
    $post_price_net = $post_prices['net'];
    $post_price_gross = $post_prices['gross'];


    $get_products[$k]['product_price_gross'] = $post_price_gross;
    $get_products[$k]['product_price_net'] = $post_price_net;
    $get_products[$k]['product_price_tax'] = $tax;
    $get_products[$k]['product_id'] = $get_products[$k]['id'];

    if ($se_prefs['prefs_posts_price_mode'] == 1) {
        // gross prices
        $get_products[$k]['price_tag'] = $get_products[$k]['product_price_gross'];
    } else if($se_prefs['prefs_posts_price_mode'] == 2) {
        // gross and net prices
        $get_products[$k]['price_tag'] = $get_products[$k]['product_price_net']. '/'. $get_products[$k]['product_price_gross'];
    } else {
        // net only (b2b mode)
        $get_products[$k]['price_tag'] = $get_products[$k]['product_price_net'];
    }





    $get_products[$k]['product_author'] = $get_products[$k]['author'];

    /* item status */
    if (isset($get_products[$k]['post_status']) AND $get_products[$k]['post_status'] == '2') {
        $get_products[$k]['draft_message'] = '<div class="alert alert-draft"><small>' . $lang['post_is_draft'] . '</small></div>';
        $get_products[$k]['product_css_classes'] = 'draft';
    }

    /* show shopping cart button */
    if ($se_prefs['prefs_posts_products_cart'] == 2 or $se_prefs['prefs_posts_products_cart'] == 3) {
        $show_cart_btn = true;
    }

    /* show shopping cart button */
    if($show_shopping_cart) {
        $get_products[$k]['show_shopping_cart'] = true;

        /* hide cart btn if item is sold out */
        if ($get_products[$k]['product_stock_mode'] == 2 && $get_products[$k]['product_nbr_stock'] < 1) {
            $get_products[$k]['show_shopping_cart'] = false;

        }

        /* hide cart btn if we have options for this item */
        if($get_products[$k]['product_options'] != 'null' AND $get_products[$k]['product_options'] != '') {
            $get_products[$k]['show_shopping_cart'] = false;
        }

        if($get_products[$k]['product_cart_mode'] == 2) {
            $get_products[$k]['show_shopping_cart'] = false;
        }
    }

    // add helpers for admins
    if(isset($_SESSION['user_class']) && $_SESSION['user_class'] == 'administrator') {
        se_store_admin_helper("prod", $get_products[$k]['id']);
    }

}

if($status_404 == true) {
    $show_404 = "true";
}

$form_action = '/' . $swifty_slug . $mod_slug;
$smarty->assign('form_action', $form_action);
$smarty->assign('product_cnt', $cnt_filter_products);
$smarty->assign('products', $get_products);
$smarty->assign('show_products_list', $show_products_list);
$smarty->assign('product_filter', $product_filter);

$smarty->assign('show_pagination', $show_pagination);
$smarty->assign('disable_prev_link', $disable_prev_link);
$smarty->assign('disable_next_link', $disable_next_link);
if(isset($pagination)) {
    $smarty->assign('pagination', $pagination);
}

$smarty->assign('show_shopping_cart', $show_shopping_cart);
$smarty->assign('btn_add_to_cart', $lang['btn_add_to_cart']);
$smarty->assign('btn_read_more', $lang['btn_open_product']);

$products_page = $smarty->fetch("products-list.tpl", $cache_id);
$smarty->assign('page_content', $products_page, true);
$smarty->assign('categories', $categories);