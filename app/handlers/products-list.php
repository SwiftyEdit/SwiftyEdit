<?php
/**
 * SwiftyEdit - list products
 *
 * @var array $se_settings
 * @var array $page_contents
 * @var string $languagePack
 * @var string $swifty_slug
 * @var array $cached_url_data
 * @var array $lang
 * @var string $cache_id
 * @var object $smarty
 * @var string $se_base_url
 */


/* defaults */
$products_start = 0;
$products_limit = (int) $se_settings['products_per_page'];
if($products_limit == '' || $products_limit < 1) {
    $products_limit = 10;
}
$canonical_url = '';

$str_status = '1';
if(isset($_SESSION['user_class']) && $_SESSION['user_class'] == 'administrator') {
    $str_status = '1-2';
}

// Determine current base URL (with category if applicable)
$filter_base_url = '/' . $swifty_slug;

// If we're in a category, add it
if (!empty($mod_slug)) {
    // Remove pagination parts (e.g., '/p/2')
    $mod_slug_clean = preg_replace('#/p/\d+/?#', '', $mod_slug);
    if (!empty($mod_slug_clean)) {
        $filter_base_url .= $mod_slug_clean;
    }
}

// Ensure trailing slash
if (substr($filter_base_url, -1) !== '/') {
    $filter_base_url .= '/';
}


// Load filters from DB
$get_product_filter = se_get_product_filter($languagePack);

$this_page_categories = explode(',', $page_contents['page_posts_categories']);
if($this_page_categories[0] == 'all') {
    $all_categories = se_get_categories();
    foreach($all_categories as $cat) {
        $this_page_categories[] = $cat['cat_id'];
    }
}

$display_product_filter = array();
foreach($get_product_filter as $k => $v) {
    $this_filters_array = explode(",", $v['categories']);

    foreach($this_page_categories as $c) {
        if(in_array("$c", $this_filters_array)) {
            $display_product_filter[] = $get_product_filter[$k];
            continue;
        }
        if(in_array("all", $this_filters_array)) {
            $display_product_filter[] = $get_product_filter[$k];
        }
    }
}
$display_product_filter = array_values(array_column($display_product_filter, null, 'title'));

// Parse URL parameters and convert slugs to IDs
$parsed_filters = se_parse_shop_url_filters($display_product_filter, $_GET);

// Set checked status in filter array for Smarty
$product_filter = se_set_filter_checked_status($display_product_filter, $parsed_filters['active_filters']);


// Build filter URLs for each item
foreach ($product_filter as $group_key => $filter_group) {

    // Check if this filter group has any active filter
    $has_active_filter = false;
    foreach ($filter_group['items'] as $item) {
        if ($item['checked']) {
            $has_active_filter = true;
            break;
        }
    }
    $product_filter[$group_key]['has_active'] = $has_active_filter;

    // Build "clear filter" URL for this group (for "All" option)
    $clear_filter_query = se_remove_filter_from_url($filter_group['slug'], null, $_GET);
    $product_filter[$group_key]['clear_url'] = $filter_base_url . $clear_filter_query;

    // Build URLs for each item
    foreach ($product_filter[$group_key]['items'] as $item_key => $item) {
        if ($item['slug'] !== '') {
            $filter_query = se_build_filter_url(
                $filter_group['slug'],
                $item['slug'],
                $filter_group['input_type'],
                $_GET
            );
            $product_filter[$group_key]['items'][$item_key]['filter_url'] = $filter_base_url . $filter_query;
        } else {
            $product_filter[$group_key]['items'][$item_key]['filter_url'] = '#';
        }
    }

    // For range filters: add min/max values
    if ($filter_group['input_type'] == 3) {
        $values = array_map(function($item) {
            return (float)$item['title'];
        }, $filter_group['items']);

        $product_filter[$group_key]['range_min'] = min($values);
        $product_filter[$group_key]['range_max'] = max($values);

        if (isset($parsed_filters['active_filters'][$filter_group['slug']])) {
            $active_range = $parsed_filters['active_filters'][$filter_group['slug']];
            $product_filter[$group_key]['current_min'] = $active_range['min'];
            $product_filter[$group_key]['current_max'] = $active_range['max'];
        } else {
            $product_filter[$group_key]['current_min'] = min($values);
            $product_filter[$group_key]['current_max'] = max($values);
        }
    }
}

// Generate active filter tags for display ("reset-buttons")
$active_filter_tags = se_get_active_filter_tags($parsed_filters['active_filters'], $filter_base_url);

// Set canonical URL if there are active filters
$canonical_url = '';
if(is_array($active_filter_tags) && count($active_filter_tags) > 0) {
    $canonical_url = rtrim($se_base_url,"/").$filter_base_url;
    $smarty->assign('page_canonical_url', $canonical_url);
    $smarty->assign('page_meta_robots', "noindex, follow");
}

// Assign to Smarty
$smarty->assign('product_filter', $product_filter);


// Session: Save current URL for user comfort
$current_query_string = $_SERVER['QUERY_STRING'] ?? '';
if ($current_query_string !== '') {
    $_SESSION['last_shop_url'] = $_SERVER['REQUEST_URI'];
} else {
    // User came without filters - check if we have a saved URL
    if (isset($_SESSION['last_shop_url']) && $_SESSION['last_shop_url'] !== $_SERVER['REQUEST_URI']) {
        // Redirect to last filter state
        header("Location: " . $_SESSION['last_shop_url']);
        exit;
    }
}

// Prepare filter array for se_get_products()
$products_filter = array();
$products_filter['languages'] = $page_contents['page_language'];
$products_filter['types'] = $page_contents['page_posts_types'];
$products_filter['status'] = $str_status;
$products_filter['categories'] = $page_contents['page_posts_categories'];
$products_filter['custom_filter_groups'] = $parsed_filters['custom_filter_groups'];
$products_filter['custom_range_filter'] = $parsed_filters['custom_range_filter'];


// Sorting from URL (instead of session)
$sort_by = '';
if (isset($_GET['sort'])) {
    $allowed_sorts = ['name', 'ts', 'pasc', 'pdesc'];
    if (in_array($_GET['sort'], $allowed_sorts)) {
        $sort_by = $_GET['sort'];
    }
} else {
    // Default sorting from preferences
    if($se_settings['product_sorting'] == 2) {
        $sort_by = 'ts';
    } else if($se_settings['product_sorting'] == 3) {
        $sort_by = 'name';
    } else if($se_settings['product_sorting'] == 4) {
        $sort_by = 'pasc';
    } else if($se_settings['product_sorting'] == 5) {
        $sort_by = 'pdesc';
    }
}

$products_filter['sort_by'] = $sort_by;

// Set active class for sort buttons
if($sort_by == 'name') {
    $smarty->assign('class_sort_name', "active");
} else if($sort_by == 'ts') {
    $smarty->assign('class_sort_topseller', "active");
} else if($sort_by == 'pasc') {
    $smarty->assign('class_sort_price_asc', "active");
} else if($sort_by == 'pdesc') {
    $smarty->assign('class_sort_price_desc', "active");
}

// Build sort URLs with current filters preserved
$sort_urls = [
    'default' => $filter_base_url . se_build_sort_url('', $_GET),
    'name' => $filter_base_url . se_build_sort_url('name', $_GET),
    'ts' => $filter_base_url . se_build_sort_url('ts', $_GET),
    'pasc' => $filter_base_url . se_build_sort_url('pasc', $_GET),
    'pdesc' => $filter_base_url . se_build_sort_url('pdesc', $_GET)
];

$smarty->assign('sort_urls', $sort_urls);

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

/* check which filters should be displayed on this page */
$display_product_filter = array();
foreach($get_product_filter as $k => $v) {
    $this_filters_array = explode(",",$v['categories']);
    foreach($this_page_categories as $c) {
        if(in_array("$c",$this_filters_array)) {
            $display_product_filter[] = $get_product_filter[$k];
            continue;
        }
        if(in_array("all",$this_filters_array)) {
            $display_product_filter[] = $get_product_filter[$k];
        }
    }
}
$display_product_filter = array_values(array_column($display_product_filter, null, 'title'));

// Build category navigation WITH current filters preserved
$categories = array();
foreach($all_categories as $cats) {
    if($page_contents['page_posts_categories'] != 'all') {
        if (!in_array($cats['cat_hash'], $this_page_categories)) {
            continue;
        }
    }

    $show_category_title = $cats['cat_description'];
    $show_category_name = $cats['cat_name'];

    // Build category URL with filters preserved
    $cat_href = se_build_category_url($cats['cat_name_clean'], $swifty_slug, $_GET);

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
        $products_filter['categories'] = $cats['cat_hash'];
        $display_mode = 'list_products_category';
        $status_404 = false;

        if($array_mod_slug[1] == 'p') {
            if(is_numeric($array_mod_slug[2])) {
                $products_start = $array_mod_slug[2];
            } else {
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: /$swifty_slug");
                header("Connection: close");
            }
        }
    }
}

// Pagination

if($array_mod_slug[0] == 'p' OR $array_mod_slug[1] == 'p' OR isset($_GET['page'])) {
    $status_404 = false;

    if(isset($_GET['page'])) {
        $products_start = (int) $_GET['page'];
    } else if(is_numeric($array_mod_slug[1])) {
        $products_start = $array_mod_slug[1];
    } else if(is_numeric($array_mod_slug[2])) {
        $products_start = $array_mod_slug[2];
    } else {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: /$swifty_slug");
        header("Connection: close");
        exit;
    }
}

// Get the product-page by 'type_of_use' and $languagePack
foreach ($cached_url_data as $page) {
    if ($page['page_language'] === $page_contents['page_language'] && $page['page_type_of_use'] === 'display_product') {
        $get_target_page = $page['page_permalink'];
        break;
    }
}

if(!isset($get_target_page) OR $get_target_page == '') {
    $get_target_page = $swifty_slug;
}

$sql_start = ($products_start * $products_limit) - $products_limit;
if ($sql_start < 0) {
    $sql_start = 0;
}

// Get products
$get_products = se_get_products($sql_start, $products_limit, $products_filter);
$cnt_filter_products = $get_products[0]['cnt_products_match'] ?? 0;
$cnt_get_products = count($get_products);

$show_products_list = true;
if($cnt_filter_products < 1) {
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

        $pagination_link = se_set_shop_pagination_query($set_start, $_GET);

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

    // Prev/Next links with filters
    $older_link_query = se_set_shop_pagination_query($nextstart, $_GET);
    $newer_link_query = se_set_shop_pagination_query($prevstart, $_GET);

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

// Shopping cart check
if($se_settings['posts_products_cart'] == 2 OR $se_settings['posts_products_cart'] == 3) {
    $show_shopping_cart = true;
    if($se_settings['posts_products_cart'] == 2 && $_SESSION['user_nick'] == '') {
        $show_shopping_cart = false;
    }
} else {
    $show_shopping_cart = false;
}

// Product loop

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
    $get_products[$k]['product_href'] = SE_INCLUDE_PATH . "/" . $get_target_page . "$post_filename-" . $get_products[$k]['id'] . ".html";
    if($get_products[$k]['slug'] != '') {
        $get_products[$k]['product_href'] = SE_INCLUDE_PATH . "/" . $get_target_page . $get_products[$k]['slug'];
    }

    $post_releasedate = date($se_settings['dateformat'], $get_products[$k]['releasedate']);
    $post_releasedate_year = date('Y', $get_products[$k]['releasedate']);
    $post_releasedate_month = date('m', $get_products[$k]['releasedate']);
    $post_releasedate_day = date('d', $get_products[$k]['releasedate']);
    $post_releasedate_time = date($se_settings['timeformat'], $get_products[$k]['releasedate']);

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

    // tax
    if ($product_tax == '1') {
        $tax = $se_settings['posts_products_default_tax'];
    } else if ($product_tax == '2') {
        $tax = $se_settings['posts_products_tax_alt1'];
    } else {
        $tax = $se_settings['posts_products_tax_alt2'];
    }

    $get_products[$k]['price_tag_label_from'] = '';

    // check if we have lower price from variants
    $product_get_lowest_price = se_get_product_lowest_price($get_products[$k]['id']);
    if($product_get_lowest_price !== null) {
        if(se_commaToFloat($product_get_lowest_price) < se_commaToFloat($product_price_net)) {
            $get_products[$k]['price_tag_label_from'] = $lang['price_tag_label_from'];
        }
        $product_price_net = $product_get_lowest_price;
    }

    $post_prices = se_posts_calc_price($product_price_net, $tax);
    $post_price_net = $post_prices['net'];
    $post_price_gross = $post_prices['gross'];


    $get_products[$k]['product_price_gross'] = $post_price_gross;
    $get_products[$k]['product_price_net'] = $post_price_net;
    $get_products[$k]['product_price_tax'] = $tax;
    $get_products[$k]['product_id'] = $get_products[$k]['id'];

    if ($se_settings['posts_price_mode'] == 1) {
        // gross prices
        $get_products[$k]['price_tag'] = $get_products[$k]['product_price_gross'];
    } else if($se_settings['posts_price_mode'] == 2) {
        // gross and net prices
        $get_products[$k]['price_tag'] = $get_products[$k]['product_price_net']. '/'. $get_products[$k]['product_price_gross'];
    } else {
        // net only (b2b mode)
        $get_products[$k]['price_tag'] = $get_products[$k]['product_price_net'];
    }



    $get_products[$k]['product_author'] = $get_products[$k]['author'];

    /* item status */
    if (isset($get_products[$k]['status']) AND $get_products[$k]['status'] == '2') {
        $get_products[$k]['draft_message'] = '<div class="alert alert-draft"><small>' . $lang['post_is_draft'] . '</small></div>';
        $get_products[$k]['product_css_classes'] = 'draft';
    }

    /* show shopping cart button */
    if ($se_settings['posts_products_cart'] == 2 or $se_settings['posts_products_cart'] == 3) {
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
    header("HTTP/1.0 404 Not Found");
    header("Status: 404 Not Found");
} else {
    $form_action = '/' . $swifty_slug . $mod_slug;

    $smarty->assign('filter_base_url', $filter_base_url);
    $smarty->assign('form_action', $form_action);
    $smarty->assign('page_slug', $swifty_slug);
    $smarty->assign('product_cnt', $cnt_filter_products);
    $smarty->assign('products', $get_products);
    $smarty->assign('show_products_list', $show_products_list);

    $smarty->assign('active_filter_tags', $active_filter_tags);
    $smarty->assign('has_active_filters', !empty($active_filter_tags));

    $smarty->assign('nbr_products', $cnt_filter_products);
    $smarty->assign('show_pagination', $show_pagination);
    $smarty->assign('disable_prev_link', $disable_prev_link);
    $smarty->assign('disable_next_link', $disable_next_link);

    if (isset($pagination)) {
        $smarty->assign('pagination', $pagination);
    }

    $smarty->assign('show_shopping_cart', $show_shopping_cart);
    $smarty->assign('btn_add_to_cart', $lang['btn_add_to_cart']);
    $smarty->assign('btn_read_more', $lang['btn_open_product']);

    $products_page = $smarty->fetch("products-list.tpl", $cache_id);
    $smarty->assign('page_content', $products_page, true);
    $smarty->assign('categories', $categories);
    $smarty->assign('cat_hashes', $page_contents['page_posts_categories']);
}