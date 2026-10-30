<?php

/**
 * SwiftyEdit /admin-xhr/shop/read/
 * products: active search keywords, keywords, categories, the list (acp/templates/shop/list-items.twig)
 * and the checks in the sidebar - and the lists of orders, price groups, features, options and filters
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $db_content
 * @var object $db_posts
 * @var string $hidden_csrf_token
 * @var array $se_settings
 * @var object $twig
 */

$list_writer_uri = '/admin-xhr/shop/write/';

// text fields are stored html-encoded (shop data-writer), Twig escapes on output
$decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

$global_filter_languages = json_decode($_SESSION['global_filter_languages'] ?? '',true);

include_once __DIR__.'/../templates.php';
global $lang_codes;

/**
 * list active keywords from search input
 * used in the sidebar
 */
if($_REQUEST['action'] == 'list_active_searches') {
    $keywords = array_filter(explode(" ", $_SESSION['products_text_filter'] ?? ''));
    echo $twig->render('components/list/active-searches.twig', [
        'keywords' => array_map($decode, array_values($keywords)),
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

if($_REQUEST['action'] == 'list_active_searches_orders') {
    $keywords = array_filter(explode(" ", $_SESSION['orders_text_filter'] ?? ''));
    echo $twig->render('components/list/active-searches.twig', [
        'keywords' => array_map($decode, array_values($keywords)),
        'remove_name' => 'rmkey_orders',
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

// keywords of all products, used in the sidebar
if($_REQUEST['action'] == 'list_keyword_btn') {
    $active_keywords = explode(",", $_SESSION['products_keyword_filter'] ?? '');
    $get_keywords = se_get_products_keywords();
    arsort($get_keywords);
    $keywords = [];
    foreach($get_keywords as $keyword => $count) {
        $keyword = trim((string) $keyword);
        $keywords[] = [
            // the value is posted back and compared with the stored (encoded) keywords
            'name' => $decode($keyword),
            'count' => $count,
            'active' => in_array($keyword, $active_keywords)
        ];
    }
    echo $twig->render('shop/keyword-buttons.twig', [
        'keywords' => $keywords,
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

// list products
if($_REQUEST['action'] == 'list_products') {

    $nbr_show_items = 10;
    $active_page = (int) ($_SESSION['pagination_products_page'] ?? 0);

    // sorting, only these columns
    $order_key = $_SESSION['sorting_products'] ?? 'lastedit';
    if(!in_array($order_key, ['priority', 'date', 'lastedit', 'product_price_net'])) {
        $order_key = 'lastedit';
    }
    $order_direction = (($_SESSION['sorting_products_direction'] ?? '') == 'ASC') ? 'ASC' : 'DESC';

    // variants are listed below their product
    $db_where = [
        "type" => 'p'
    ];

    // search: every keyword has to be part of one of the texts
    $search_columns = ['title', 'teaser', 'text', 'text_additional1', 'text_additional2', 'text_additional3', 'text_additional4', 'text_additional5'];
    $search = array_values(array_filter(explode(" ", $_SESSION['products_text_filter'] ?? '')));
    foreach($search as $i => $keyword) {
        $db_where["OR #search$i"] = array_fill_keys(array_map(static fn($column) => $column.'[~]', $search_columns), "%$keyword%");
    }

    // keywords of the sidebar: every selected keyword has to be part of the tags
    $tags = array_values(array_filter(explode(",", $_SESSION['products_keyword_filter'] ?? '')));
    foreach($tags as $i => $tag) {
        $db_where["AND #tag$i"] = ["tags[~]" => "%$tag%"];
    }

    // categories: products in one of the selected categories
    $cat_filter = array_values(array_filter(explode(" ", $_SESSION['filter_prod_categories'] ?? '')));
    if($cat_filter) {
        $db_where["categories[~]"] = $cat_filter;
    }

    // global filter (top navigation), the status ghost is 3 in products
    $db_where += se_get_global_filter_where('product_lang', 'status', 'labels', [1 => '1', 2 => '2', 4 => '3']);

    $products_data_cnt = $db_posts->count("se_products", ["AND" => $db_where]);
    $nbr_pages = (int) ceil($products_data_cnt/$nbr_show_items);

    $products_data = $db_posts->select("se_products", "*", [
        "AND" => $db_where,
        "ORDER" => [$order_key => $order_direction],
        "LIMIT" => [$active_page * $nbr_show_items, $nbr_show_items]
    ]);

    // categories by hash, for the category names in the rows
    $categories_by_hash = [];
    foreach(se_get_categories() as $cat) {
        $categories_by_hash[$cat['cat_hash']] = [
            'name' => $decode($cat['cat_name']),
            'description' => $decode($cat['cat_description'] ?? '')
        ];
    }

    $taxes = [
        '1' => $se_settings['posts_products_default_tax'],
        '2' => $se_settings['posts_products_tax_alt1'],
        '3' => $se_settings['posts_products_tax_alt2']
    ];

    $products = [];
    foreach($products_data as $product) {

        $product_id = (int) $product['id'];

        $teaser_plain = strip_tags(htmlspecialchars_decode((string) $product['teaser']));

        // images are stored as <->img1<->img2<->, the first one is the thumbnail
        $prod_image = explode("<->", (string) $product['images']);
        $thumb = '';
        if(($prod_image[1] ?? '') != '') {
            $thumb = str_replace(['../content/', '../images/'], ['/', '/images/'], $prod_image[1]);
        }

        $categories = [];
        foreach(array_filter(explode('<->', (string) $product['categories'])) as $hash) {
            if(isset($categories_by_hash[$hash])) {
                $categories[] = $categories_by_hash[$hash];
            }
        }

        $labels = array_filter(
            se_get_label_checklist(explode(',', (string) $product['labels'])),
            static fn($label) => $label['checked']
        );

        // price from the product or from its price group
        if($product['product_price_group'] != '' && $product['product_price_group'] != 'null') {
            $price_data = se_get_price_group_data($product['product_price_group']);
            $product_tax = $price_data['tax'];
            $product_price_net = $price_data['price_net'];
        } else {
            $product_tax = $product['product_tax'];
            $product_price_net = $product['product_price_net'];
        }
        $tax = $taxes[(string) $product_tax] ?? $taxes['3'];

        // prices are stored formatted, e.g. 1.234,56
        $price_net = (float) str_replace(['.', ','], ['', '.'], (string) ($product_price_net ?: 0));
        $price_gross = $price_net * ((float) $tax + 100) / 100;

        // variants, without the product itself
        $variants = [];
        foreach(se_get_product_variants($product_id) as $variant) {
            if($variant['id'] == $product_id) {
                continue;
            }
            $description = (string) ($variant['product_variant_description'] ?: $variant['teaser']);
            $variants[] = [
                'id' => (int) $variant['id'],
                'title' => $decode($variant['product_variant_title'] ?: $variant['title']),
                'description' => $decode(se_return_first_chars($description, 100, false)),
                'description_trimmed' => (strlen(strip_tags(htmlspecialchars_decode($description))) > 100)
            ];
        }

        $status_class = '';
        $status_label = '';
        if($product['status'] == '2') {
            $status_class = 'item_is_draft';
            $status_label = $lang['status_draft'];
        }
        if($product['status'] == '3') {
            $status_class = 'item_is_ghost';
            $status_label = $lang['status_ghost'];
        }

        $products[] = [
            'id' => $product_id,
            'title' => $decode($product['title']),
            'teaser' => $decode(se_return_first_chars($product['teaser'], 100, false)),
            'teaser_trimmed' => (strlen($teaser_plain) > 100),
            'lang' => $product['product_lang'],
            'lang_flag' => return_language_flag_src($product['product_lang']),
            'status_class' => $status_class,
            'status_label' => $status_label,
            'fixed' => ($product['fixed'] == '1'),
            'priority' => $product['priority'],
            'date' => se_format_datetime($product['date']),
            'lastedit' => ($product['lastedit'] != '') ? se_format_datetime($product['lastedit']) : '',
            'releasedate' => se_format_datetime($product['releasedate']),
            'main_catalog_slug' => $product['main_catalog_slug'],
            'slug' => $product['slug'],
            'thumb' => $thumb,
            'labels' => $labels,
            'categories' => $categories,
            'currency' => $product['product_currency'],
            'tax' => $tax,
            'price_net' => se_post_print_currency($price_net),
            'price_gross' => se_post_print_currency($price_gross),
            'variants' => $variants
        ];
    }

    echo $twig->render('shop/list-items.twig', [
        'products' => $products,
        'pagination' => se_print_pagination($list_writer_uri, $nbr_pages, $active_page),
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

// list of products that have been assigned to a specific filter value
if(($_REQUEST['show'] ?? '') == 'products_by_filter') {
    $filter_id = (int) $_REQUEST['filter_id'];
    // products store their values as json, e.g. {"0":"2","1":"5"}
    $get_products = $db_posts->select("se_products", ["id", "title"], [
        "filter[~]" => ':"'.$filter_id.'"',
        "ORDER" => ["lastedit" => "DESC"],
        "LIMIT" => 50,
    ]);
    echo $twig->render('shop/filter-products.twig', [
        'products' => array_map(static fn($product) => ['id' => $product['id'], 'title' => $decode($product['title'])], $get_products)
    ]);
}

// list price groups
if($_REQUEST['action'] == 'list_price_groups') {

    $tax_options = [
        1 => $se_settings['posts_products_default_tax'],
        2 => $se_settings['posts_products_tax_alt1'],
        3 => $se_settings['posts_products_tax_alt2'],
    ];

    $groups = [];
    foreach(se_get_price_groups() as $group) {
        $tax = $tax_options[$group['tax']] ?? $tax_options[1];
        $group_prices = se_posts_calc_price($group['price_net'], $tax);
        $groups[] = [
            'id' => (int) $group['id'],
            'title' => $decode($group['title']),
            'net' => $group_prices['net_single'],
            'gross' => $group_prices['gross_single'],
            // older versions stored "null" for no volume discounts
            'volume_discounts' => !in_array($group['price_volume_discount'], ['', 'null', null], true)
        ];
    }

    echo $twig->render('shop/price-groups-items.twig', [
        'groups' => $groups,
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

// the price group form, empty (new) or with an opened group
$show_form = false;

if($_REQUEST['action'] == 'show_price_groups_form') {
    $show_form = true;
}

if(isset($_REQUEST['open_price_group'])) {
    $price_group = $db_posts->get("se_prices","*", [
        "id" => (int) $_REQUEST['open_price_group']
    ]);
    $show_form = true;
}

// form for price groups
if($show_form) {

    $price_group = $price_group ?? [];

    $tax_options = [
        1 => $se_settings['posts_products_default_tax'],
        2 => $se_settings['posts_products_tax_alt1'],
        3 => $se_settings['posts_products_tax_alt2'],
    ];

    // stored volume discounts plus five empty rows for new entries
    $volume_discounts = json_decode($price_group['price_volume_discount'] ?? '', true);
    $volume_discounts = is_array($volume_discounts) ? array_values($volume_discounts) : [];
    $empty_rows = max(count($volume_discounts), 1) + 5 - count($volume_discounts);
    for($i=0;$i<$empty_rows;$i++) {
        $volume_discounts[] = ['amount' => '', 'price' => ''];
    }

    echo $twig->render('shop/price-group-form.twig', [
        'group' => [
            'id' => $price_group['id'] ?? 'new',
            'title' => $decode($price_group['title'] ?? ''),
            'amount' => $price_group['amount'] ?? '',
            'unit' => $decode($price_group['unit'] ?? ''),
            'unit_content' => $decode($price_group['unit_content'] ?? ''),
            'price_net' => $decode($price_group['price_net'] ?? ''),
            'tax' => $price_group['tax'] ?? null,
            'volume_discounts' => $volume_discounts
        ],
        'tax_options' => $tax_options
    ]);
}

// filter by category in the sidebar
if($_REQUEST['action'] == 'list_categories') {
    $active_categories = explode(" ", $_SESSION['filter_prod_categories'] ?? '');
    $items = [];
    foreach(se_get_categories() as $category) {
        $items[] = [
            'value' => $category['cat_hash'],
            'label' => $decode($category['cat_name']),
            'active' => in_array($category['cat_hash'], $active_categories),
            'flag' => return_language_flag_src($category['cat_lang'])
        ];
    }
    echo $twig->render('components/list/filter-buttons.twig', [
        'name' => 'set_filter_cat',
        'items' => $items,
        'writer_uri' => $list_writer_uri
    ]);
}

// active search keywords of the features, used in the sidebar
if($_REQUEST['action'] == 'list_active_searches_features') {
    $keywords = array_filter(explode(" ", $_SESSION['features_text_filter'] ?? ''));
    echo $twig->render('components/list/active-searches.twig', [
        'keywords' => array_map($decode, array_values($keywords)),
        'remove_name' => 'rmkey_features',
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

// list all features
if($_REQUEST['action'] == 'list_features') {

    // global filter (top navigation): features only have a language
    $filter_languages = array_filter((array) (json_decode($_SESSION['global_filter_languages'] ?? '', true) ?? []));

    // search: every keyword has to be part of the title or the text
    $keywords = array_filter(explode(" ", $decode($_SESSION['features_text_filter'] ?? '')));

    $features = [];
    foreach(se_get_posts_features() as $data) {
        if($filter_languages && !in_array($data['snippet_lang'], $filter_languages)) {
            continue;
        }
        $haystack = $decode($data['snippet_title']).' '.strip_tags($decode($data['snippet_content']));
        foreach($keywords as $keyword) {
            if(mb_stripos($haystack, $keyword) === false) {
                continue 2;
            }
        }
        $features[] = [
            'id' => (int) $data['snippet_id'],
            'priority' => $data['snippet_priority'],
            'lang' => $data['snippet_lang'],
            'lang_flag' => return_language_flag_src($data['snippet_lang']),
            'title' => $decode($data['snippet_title']),
            'text' => $data['snippet_content']
        ];
    }

    echo $twig->render('shop/features-items.twig', [
        'features' => $features
    ]);
}

// active search keywords of the options, used in the sidebar
if($_REQUEST['action'] == 'list_active_searches_options') {
    $keywords = array_filter(explode(" ", $_SESSION['options_text_filter'] ?? ''));
    echo $twig->render('components/list/active-searches.twig', [
        'keywords' => array_map($decode, array_values($keywords)),
        'remove_name' => 'rmkey_options',
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

// list all options
if($_REQUEST['action'] == 'list_options') {

    // global filter (top navigation): options only have a language
    $filter_languages = array_filter((array) (json_decode($_SESSION['global_filter_languages'] ?? '', true) ?? []));

    // search: every keyword has to be part of the title or a value
    $keywords = array_filter(explode(" ", $decode($_SESSION['options_text_filter'] ?? '')));

    $options = [];
    foreach(se_get_posts_options() as $data) {
        if($filter_languages && !in_array($data['snippet_lang'], $filter_languages)) {
            continue;
        }
        // values are stored as entered (json), Twig escapes on output
        $values = json_decode((string) $data['snippet_content'], true);
        $values = is_array($values) ? array_values($values) : [];

        $title = $decode($data['snippet_title']);
        $haystack = $title.' '.implode(' ', $values);
        foreach($keywords as $keyword) {
            if(mb_stripos($haystack, $keyword) === false) {
                continue 2;
            }
        }

        $options[] = [
            'id' => (int) $data['snippet_id'],
            'priority' => $data['snippet_priority'],
            'lang' => $data['snippet_lang'],
            'lang_flag' => return_language_flag_src($data['snippet_lang']),
            'title' => $title,
            'values' => $values
        ];
    }

    echo $twig->render('shop/options-items.twig', [
        'options' => $options
    ]);
}

// list filters (groups with their values)
if($_REQUEST['action'] == 'list_filters') {

    $db_where = [
        "filter_type" => 1
    ];

    // categories of the sidebar (shared with the products list)
    $cat_filter = array_values(array_filter(explode(" ", $_SESSION['filter_prod_categories'] ?? '')));
    if($cat_filter) {
        $db_where["filter_categories[~]"] = $cat_filter;
    }

    // global filter (top navigation): filter groups only have a language
    $filter_languages = array_values(array_filter((array) ($global_filter_languages ?? [])));
    if($filter_languages) {
        $db_where["filter_lang"] = $filter_languages;
    }

    $filters = $db_content->select("se_filter", "*", [
        "AND" => $db_where,
        "ORDER" => ["filter_priority" => "DESC"]
    ]);

    $category_names = [];
    foreach(se_get_categories() as $cat) {
        $category_names[$cat['cat_hash']] = $decode($cat['cat_name']);
    }

    $type_icons = [
        '1' => $icon['ui_radios'],
        '2' => $icon['ui_checks']
    ];

    $groups = [];
    foreach($filters as $group) {

        // the frontend builds the filter urls from the slugs - add missing ones
        if($group['filter_slug'] == '') {
            $db_content->update("se_filter", ["filter_slug" => se_generate_slug($group['filter_title'])], ["filter_id" => $group['filter_id']]);
        }

        $values = [];
        foreach(se_get_product_filter_values($group['filter_id']) as $item) {
            if($item['filter_slug'] == '') {
                $db_content->update("se_filter", ["filter_slug" => se_generate_slug($item['filter_title'])], ["filter_id" => $item['filter_id']]);
            }
            $values[] = [
                'id' => (int) $item['filter_id'],
                'title' => $decode($item['filter_title']),
                'priority' => $item['filter_priority']
            ];
        }

        $group_categories = explode(",", (string) $group['filter_categories']);
        $groups[] = [
            'id' => (int) $group['filter_id'],
            'title' => $decode($group['filter_title']),
            'priority' => $group['filter_priority'],
            'lang' => $group['filter_lang'],
            'lang_flag' => return_language_flag_src($group['filter_lang']),
            'type_icon' => $type_icons[(string) $group['filter_input_type']] ?? $icon['sliders'],
            'all_categories' => in_array('all', $group_categories),
            'categories' => array_values(array_intersect_key($category_names, array_flip($group_categories))),
            'values' => $values
        ];
    }

    echo $twig->render('shop/filters-items.twig', [
        'groups' => $groups
    ]);
}

/**
 * the invoice address of an order as plain text lines - it is stored as
 * html: the sanitized address fields, joined with <br> (see app/handlers/checkout.php)
 *
 * @param string $address
 * @return array
 */
$order_address_lines = static function(string $address) use ($decode): array {
    $lines = preg_split('/<br\s*\/?>/i', $address) ?: [];
    return array_values(array_filter(array_map(static fn($line) => trim($decode(strip_tags($line))), $lines)));
};

/**
 * the options of an ordered product as plain text - stored as <span>option</span>...
 * by se_add_to_cart(), older orders may contain any markup
 *
 * @param string $options
 * @return array
 */
$order_option_lines = static function(string $options) use ($decode): array {
    if(preg_match_all('#<span>(.*?)</span>#s', $options, $matches) > 0) {
        $options = $matches[1];
    } else {
        $options = [$options];
    }
    return array_values(array_filter(array_map(static fn($option) => trim($decode(strip_tags($option))), $options)));
};

// list orders
if($_REQUEST['action'] == 'list_orders') {

    $nbr_show_items = 25;
    $active_page = (int) ($_SESSION['pagination_orders'] ?? 0);

    // search: every keyword has to be part of the address, mail, products or comments
    $search_columns = ['order_invoice_mail', 'order_invoice_address', 'order_products', 'order_user_comment', 'order_admin_comment'];
    $db_where = [
        "id[>]" => 0
    ];
    foreach(array_values(array_filter(explode(" ", $_SESSION['orders_text_filter'] ?? ''))) as $i => $keyword) {
        $db_where["OR #search$i"] = array_fill_keys(array_map(static fn($column) => $column.'[~]', $search_columns), "%$keyword%");
    }

    $orders_data_cnt = $db_content->count("se_orders", ["AND" => $db_where]);
    $nbr_pages = (int) ceil($orders_data_cnt/$nbr_show_items);

    $orders_data = $db_content->select("se_orders", "*", [
        "AND" => $db_where,
        "ORDER" => ["order_time" => "DESC"],
        "LIMIT" => [$active_page * $nbr_show_items, $nbr_show_items]
    ]);

    $orders = [];
    foreach($orders_data as $order) {
        $orders[] = [
            'id' => (int) $order['id'],
            'time' => se_format_datetime($order['order_time']),
            'nbr' => $order['order_nbr'],
            'address' => $order_address_lines((string) $order['order_invoice_address']),
            'payment_type' => $order['order_payment_type'],
            'mail' => $order['order_invoice_mail'],
            'price' => se_post_print_currency($order['order_price_total']),
            'payment_status' => (string) $order['order_status_payment'],
            'status' => (string) $order['order_status'],
            'withdrawal_requested' => ($order['order_withdrawal_requested'] > 0)
        ];
    }

    echo $twig->render('shop/orders-items.twig', [
        'orders' => $orders,
        'pagination' => se_print_pagination($list_writer_uri, $nbr_pages, $active_page, '10', '', 'pagination_orders'),
        'writer_uri' => $list_writer_uri
    ]);
}

// details of an order, the modal of the orders list
if(isset($_REQUEST['show_order'])) {

    $get_order = se_get_order_details((int) $_REQUEST['show_order']) ?? [];

    $products = [];
    foreach(json_decode((string) ($get_order['order_products'] ?? ''), true) ?: [] as $order_product) {
        $products[] = [
            'amount' => $order_product['amount'] ?? '',
            'title' => $decode($order_product['title'] ?? ''),
            'options' => $order_option_lines((string) ($order_product['options'] ?? '')),
            'options_comment_label' => $decode($order_product['options_comment_label'] ?? ''),
            'options_comment' => $decode($order_product['options_comment'] ?? ''),
            'price_net' => se_post_print_currency($order_product['price_net_raw'] ?? 0),
            'tax' => $order_product['tax'] ?? '',
            'price_gross' => se_post_print_currency($order_product['price_gross_raw'] ?? 0)
        ];
    }

    echo $twig->render('shop/order-details.twig', [
        'order' => [
            'nbr' => $get_order['order_nbr'] ?? '',
            'time' => date('d.m.Y H:i', (int) ($get_order['order_time'] ?? 0)),
            'currency' => $get_order['order_currency'] ?? '',
            'price_total' => se_post_print_currency($get_order['order_price_total'] ?? 0),
            'shipping_costs' => se_post_print_currency($get_order['order_shipping_costs'] ?? 0),
            'payment_status' => (string) ($get_order['order_status_payment'] ?? ''),
            'address' => $order_address_lines((string) ($get_order['order_invoice_address'] ?? '')),
            'products' => $products
        ]
    ]);
}

if(isset($_REQUEST['calc_orders_canceled'])) {

    $result = 0;
    $orders = $db_content->select("se_orders","*", [
        "order_status" => 3
    ]);

    foreach($orders as $order) {
       $result = $result+$order['order_price_total'];
    }

    echo se_post_print_currency($result);
    exit;
}

if(isset($_REQUEST['calc_orders_all'])) {
    $result = 0;
    $orders = $db_content->select("se_orders","*");

    foreach($orders as $order) {
        $result = $result+$order['order_price_total'];
    }

    echo se_post_print_currency($result);
    exit;
}

// check for missing product numbers
if($_REQUEST['action'] == 'check_missing_sku') {
    $missing_sku = $db_posts->select("se_products", ["id", "title"], ["product_number" => ""]);
    echo $twig->render('shop/check.twig', [
        'id' => 'collapse_msku',
        'title' => 'Missing SKU',
        'count' => count($missing_sku),
        'groups' => [[
            'products' => array_map(static fn($item) => ['id' => $item['id'], 'title' => $decode($item['title'])], $missing_sku)
        ]]
    ]);
    exit;
}

// check for double slugs
if($_REQUEST['action'] == 'check_double_slugs') {
    $products = $db_posts->select("se_products", ["id", "slug", "title"]);
    $counts = array_count_values(array_column($products, 'slug'));
    $groups = [];
    $cnt_duplicates = 0;
    foreach($products as $product) {
        if($counts[$product['slug']] > 1) {
            $groups[$product['slug']]['name'] = $product['slug'];
            $groups[$product['slug']]['products'][] = ['id' => $product['id'], 'title' => $decode($product['title'])];
            $cnt_duplicates++;
        }
    }
    echo $twig->render('shop/check.twig', [
        'id' => 'collapse_double_slugs',
        'title' => 'Double Slugs',
        'count' => $cnt_duplicates,
        'groups' => array_values($groups)
    ]);
    exit;
}
