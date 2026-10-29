<?php

/**
 * @var array $lang
 * @var array $icon
 * @var object $db_posts
 * @var object $db_content
 * @var object $twig
 * @var string $query - the current url
 */

$writer_uri = '/admin-xhr/shop/write/';
$form_header_mode = $lang['btn_new'];
$my_user_presets = se_get_my_presets();

// edit mode shows the update and delete buttons, otherwise a plain save button
$is_edit = false;
$show_variant_button = false;
$get_product_id = null;

// check if last part of url is an id
$path = parse_url($query, PHP_URL_PATH);
$segments = explode('/', rtrim($path, '/'));
$lastSegment = end($segments);
if(is_numeric($lastSegment)) {
    $get_product_id = (int) $lastSegment;
    $is_edit = true;
    $form_header_mode = $lang['edit'].' #'.$get_product_id;
}

if(isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
    $get_product_id = (int) $_POST['product_id'];
    $is_edit = true;
    $form_header_mode = $lang['edit'].' #'.$get_product_id;
}

if(isset($_POST['duplicate_id']) && is_numeric($_POST['duplicate_id'])) {
    $get_product_id = (int) $_POST['duplicate_id'];
    $form_header_mode = $lang['duplicate'].' #'.$get_product_id;
    $show_variant_button = true;
}

$product_data = null;
if(is_int($get_product_id)) {
    $product_data = $db_posts->get("se_products","*",[
        "id" => "$get_product_id"
    ]);
}

$record_data = $product_data ?? [];

if(($product_data['type'] ?? '') == 'v') {
    // hide the submit as variant button
    $show_variant_button = false;
    $form_header_mode .= ' ('.$lang['product_type_variant'].')';
} else {
    $form_header_mode .= ' ('.$lang['product_type_main'].')';
}

if(!is_array($product_data)) {
    $product_data = [];
    $product_data['product_amount'] = 1;
}

// the editors get the stored (html-encoded) values, like before
$editor_fields = [
    'teaser' => $lang['label_teaser'],
    'text' => '',
    'text_additional1' => '',
    'text_additional2' => '',
    'text_additional3' => '',
    'text_additional4' => '',
    'text_additional5' => ''
];
$editors = [];
foreach($editor_fields as $field => $label) {
    $editors[$field] = se_print_form_input([
        "input_name" => $field,
        "input_value" => $product_data[$field] ?? null,
        "label" => $label,
        "type" => "textarea",
        "mode" => "wysiwyg"
    ]);
}

// text fields are stored html-encoded (se_prepareProductData), Twig escapes on output
$decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

// options for the select() macro in the template, $selected is compared as string
$build_options = static function(array $values, $selected) use ($decode) {
    $options = [];
    foreach($values as $value => $label) {
        $options[] = [
            'value' => $decode($value),
            'label' => $decode($label),
            'selected' => ((string) $value === (string) $selected)
        ];
    }
    return $options;
};

// select main catalog page
$all_catalog_pages = $db_content->select("se_pages","page_permalink",[
    "page_posts_types" => "p"
]);
array_unshift($all_catalog_pages, "default");
sort($all_catalog_pages);
$main_catalog_options = [];
foreach($all_catalog_pages as $permalink) {
    $main_catalog_options[] = [
        'value' => $decode($permalink),
        'label' => ($permalink == 'default') ? $lang['label_use_default'] : $decode($permalink),
        'selected' => (($product_data['main_catalog_slug'] ?? '') == "$permalink")
    ];
}

/* language */
$product_lang = $product_data['product_lang'] ?? '';
if($product_lang == '' && $default_lang_code != '') {
    $product_lang = $default_lang_code;
}
$lang_options = $build_options(array_combine($lang_codes, $lang_codes), $product_lang);

/* categories */
$cats = se_get_categories();
$array_categories = explode("<->", $product_data['categories'] ?? '');
$categories = [];
foreach($cats as $cat) {
    $description = trim(first_words($decode($cat['cat_description'] ?? ''), 10)) ?: '...';
    $categories[] = [
        'id' => (int) $cat['cat_id'],
        'hash' => $cat['cat_hash'],
        'name' => $decode($cat['cat_name']),
        'description' => $description,
        'flag' => return_language_flag_src($cat['cat_lang']),
        'checked' => in_array($cat['cat_hash'], $array_categories)
    ];
}

/* release date */
if(isset($product_data['releasedate']) AND $product_data['releasedate'] > 0) {
    $releasedate = date('Y-m-d H:i', $product_data['releasedate']);
} else {
    $releasedate = date('Y-m-d H:i', time());
}
// future dates are not allowed, see se_prepareProductData()
$releasedate_max = date('Y-m-d\TH:i', time());

/* image widget */
$images = [];
foreach(array_filter(explode("<->", $product_data['images'] ?? '')) as $image) {
    $image_src = str_replace('../content/','/',$image); // old path from SwiftyEdit 1.x
    $image_src = str_replace('../images/','/images/',$image_src);
    $images[] = [
        'path' => $image,
        'src' => $image_src,
        'name' => basename($image)
    ];
}

/* status | draft or published */
if(!isset($product_data['status'])) {
    // new product, check if we have user presets
    if($my_user_presets['status'] == 'p') {
        $product_data['status'] = 1;
    } else if($my_user_presets['status'] == 'd') {
        $product_data['status'] = 2;
    }
}

if(se_hasPermission('drm_can_publish')) {
    $status_options = $build_options([
        2 => $lang['status_draft'],
        1 => $lang['status_public'],
        3 => $lang['status_ghost']
    ], $product_data['status'] ?? '');
} else {
    /* user can not publish */
    $status_options = $build_options(['draft' => $lang['status_draft']], 'draft');
}

/* comments yes/no */
$comments = (($product_data['comments'] ?? '') == 1) ? 1 : 2;
$comments_options = $build_options([1 => $lang['yes'], 2 => $lang['no']], $comments);

/* votings/reactions no, yes for registered users, yes for all */
if(($product_data['votings'] ?? '') == '') {
    $product_data['votings'] = $se_settings['posts_default_votings'];
}
if($product_data['votings'] == 1 OR $product_data['votings'] == '') {
    $votings = 1;
} else if($product_data['votings'] == 2) {
    $votings = 2;
} else {
    $votings = 3;
}
$votings_labels = [
    1 => $lang['label_votings_status_off'],
    2 => $lang['label_votings_status_registered'],
    3 => $lang['label_votings_status_global']
];
$votings_options = $build_options($votings_labels, $votings);

/* RSS */
$rss = (($product_data['rss'] ?? '') == "on") ? 'on' : 'off';
$rss_options = $build_options(['on' => $lang['yes'], 'off' => $lang['no']], $rss);

/* translation urls */
$translation_urls_array = [];
if(($product_data['translation_urls'] ?? '') != '') {
    $translation_urls_array = json_decode(html_entity_decode($product_data['translation_urls']), true) ?? [];
}
$translation_urls = [];
foreach($active_lang as $v) {
    $translation_urls[$v['sign']] = $decode($translation_urls_array[$v['sign']] ?? '');
}

/* hooks */
$hooks = [];
foreach(se_get_backend_hook_meta('product.') as $hookName => $entries) {
    foreach($entries as $entryIndex => $meta) {
        if(!is_array($meta)) {
            continue;
        }
        $hooks[] = [
            // hooks[hookName][entryIndex]
            'input_name' => 'hooks['.$hookName.']['.(int) $entryIndex.']',
            'plugin' => $meta['plugin'],
            'label' => $meta['label'] ?? ''
        ];
    }
}

/* select tax */
$tax = $product_data['product_tax'] ?? '';
if($tax != '2' && $tax != '3') {
    $tax = '1';
}
$tax_options = $build_options([
    1 => $se_settings['posts_products_default_tax'],
    2 => $se_settings['posts_products_tax_alt1'],
    3 => $se_settings['posts_products_tax_alt2']
], $tax);

/* select shipping mode */
if(!isset($product_data['product_shipping_mode'])) {
    if($my_user_presets['product_type'] == 'deliver') {
        $product_data['product_shipping_mode'] = 2;
    }
}
$shipping_mode = (($product_data['product_shipping_mode'] ?? '') == '1' OR ($product_data['product_shipping_mode'] ?? '') == '') ? 1 : 2;
$shipping_mode_options = $build_options([
    1 => $lang['label_shipping_mode_digital'],
    2 => $lang['label_shipping_mode_deliver']
], $shipping_mode);

/* select shipping category */
$shipping_cat = $product_data['product_shipping_cat'] ?? '';
if($shipping_cat == '') {
    $shipping_cat = '0';
}
$shipping_cat_options = $build_options([
    0 => $lang['label_shipping_costs_no_cat'],
    1 => $lang['label_shipping_costs_cat1'],
    2 => $lang['label_shipping_costs_cat2'],
    3 => $lang['label_shipping_costs_cat3']
], $shipping_cat);

/* product filter */
$get_product_filter = json_decode($product_data['filter'] ?? '', true);
$filter_groups = [];
foreach(se_get_product_filter_groups('all') as $group) {

    $group_categories = explode(",", $group['filter_categories']);
    $filter_cats = [];
    foreach($categories as $category) {
        if(in_array($category['hash'], $group_categories)) {
            $filter_cats[] = $category;
        }
    }

    $items = [];
    foreach(se_get_product_filter_values($group['filter_id']) as $filter_item) {
        $items[] = [
            'id' => $filter_item['filter_id'],
            'title' => $decode($filter_item['filter_title']),
            'checked' => is_array($get_product_filter) && in_array((string) $filter_item['filter_id'], $get_product_filter)
        ];
    }

    $filter_groups[] = [
        'title' => $decode($group['filter_title']),
        'flag' => return_language_flag_src($group['filter_lang']),
        'categories' => $filter_cats,
        'items' => $items
    ];
}

/* features */
$get_post_features = json_decode($product_data['product_features'] ?? '', true);
$get_post_features_values = json_decode($product_data['product_features_values'] ?? '', true);
$features = [];
foreach(se_get_posts_features() as $feature) {
    $feature_id = $feature['snippet_id'];
    $features[] = [
        'id' => $feature_id,
        'title' => $decode($feature['snippet_title']),
        'text' => $decode(strip_tags(first_words($feature['snippet_content'], 15))),
        // the values are stored as entered (json), not html-encoded
        'value' => $get_post_features_values[$feature_id] ?? '',
        'checked' => is_array($get_post_features) && in_array((string) $feature_id, $get_post_features)
    ];
}

/* related products, accessories and addons */

// title and lang by product id
$temp_prod = [];
foreach(se_get_all_products() as $prod) {
    $temp_prod[$prod['id']] = [
        "title" => $decode($prod['title']),
        "lang" => $prod['product_lang'],
        "number" => $prod['product_number'] ?? ''
    ];
}

$dropper_items = static function($json) use ($temp_prod) {
    $items = [];
    $ids = json_decode($json ?? '', true);
    if(is_array($ids)) {
        foreach(array_filter($ids) as $id) {
            $items[] = [
                'id' => $id,
                'title' => $temp_prod[$id]['title'] ?? '',
                'flag' => return_language_flag_src($temp_prod[$id]['lang'] ?? null),
                'number' => $temp_prod[$id]['number'] ?? ''
            ];
        }
    }
    return $items;
};

/* product options */
$product_options = json_decode($product_data['product_options'] ?? '', true);
$options = [];
foreach(se_get_posts_options() as $option) {
    $values = json_decode($option['snippet_content']) ?? [];
    $options[] = [
        'id' => $option['snippet_id'],
        'title' => $decode($option['snippet_title']),
        'values' => array_map($decode, (array) $values),
        'checked' => is_array($product_options) && in_array($option['snippet_id'], $product_options)
    ];
}

/* variants */
$variant_links = [];
$cnt_variants = 0;
if(isset($product_data['id']) && is_numeric($product_data['id'])) {
    $variants = se_get_product_variants($product_data['id']);
    $cnt_variants = count($variants);
    foreach($variants as $variant) {
        // skip the product which is itself
        if($variant['id'] == $product_data['id']) {
            continue;
        }
        $variant_links[] = [
            'id' => $variant['id'],
            'title' => $decode($variant['title'])
        ];
    }
}

/* prices */
$product_price_net = $product_data['product_price_net'] ?? '';
if($product_price_net == '') {
    $product_price_net = '0,00';
}

$product_currency = $product_data['product_currency'] ?? '';
if($product_currency == '') {
    $product_currency = $se_settings['posts_products_default_currency'];
}

// select for price groups
$price_group_options = [
    ['value' => 'null', 'label' => $lang['label_product_price_group_no_selection'], 'selected' => false]
];
foreach(se_get_price_groups() as $price_group) {
    $price_group_options[] = [
        'value' => $price_group['hash'],
        'label' => $decode($price_group['title']),
        'selected' => ($price_group['hash'] == ($product_data['product_price_group'] ?? ''))
    ];
}

/* volume discounts, stored ones plus five empty rows for new entries */
$volume_discounts = json_decode($product_data['product_price_volume_discount'] ?? '', true);
$volume_discounts = is_array($volume_discounts) ? array_values($volume_discounts) : [];
$empty_rows = max(count($volume_discounts), 1) + 5 - count($volume_discounts);
for($i=0;$i<$empty_rows;$i++) {
    $volume_discounts[] = ['amount' => '', 'price' => ''];
}

/* select delivery time, snippets where name starts with 'shop_delivery_time' */
$delivery_time_options = [
    ['value' => 'no_specification', 'label' => $lang['label_product_no_delivery_time'], 'selected' => false]
];
$snippets_delivery_time = $db_content->select("se_snippets", "*", [
    "snippet_name[~]" => "shop_delivery_time%"
]);
foreach($snippets_delivery_time as $snippet) {
    $delivery_time_options[] = [
        'value' => $snippet['snippet_id'],
        'label' => $decode($snippet['snippet_title']),
        'selected' => ($snippet['snippet_id'] == ($product_data['product_delivery_time'] ?? ''))
    ];
}

/* text snippets for prices and for the description */
$snippet_options = static function(string $name_pattern, $selected) use ($db_content, $decode, $lang) {
    $options = [
        ['value' => 'no_snippet', 'label' => $lang['no_snippet_selected'], 'selected' => false]
    ];
    $snippets = $db_content->select("se_snippets", "*", [
        "snippet_name[~]" => $name_pattern
    ]);
    foreach($snippets as $snippet) {
        $options[] = [
            'value' => $decode($snippet['snippet_name']),
            'label' => $decode($snippet['snippet_name'].' - '.$snippet['snippet_title']),
            'selected' => ($snippet['snippet_name'] == $selected)
        ];
    }
    return $options;
};
$snippet_price_options = $snippet_options("%post_price%", $product_data['product_textlib_price'] ?? '');
$snippet_text_options = $snippet_options("%post_text%", $product_data['product_textlib_content'] ?? '');

/* select file from /assets/files/, pre-sale and after-sale files */
$files_directory = SE_PUBLIC.'/assets/files';
$short_paths = [];
foreach(se_scandir_rec($files_directory) as $file) {
    //$se_upload_file_types is set in config.php
    $file_info = pathinfo($file);
    if(in_array($file_info['extension'], $se_upload_file_types)) {
        $short_paths[] = str_replace("$files_directory", "", $file);
    }
}
$file_options = static function($selected) use ($short_paths, $lang) {
    $options = [
        ['value' => '', 'label' => '-- '.$lang['label_no_file_selected'].' --', 'selected' => false]
    ];
    foreach($short_paths as $short_path) {
        $options[] = [
            'value' => $short_path,
            'label' => $short_path,
            'selected' => ($short_path == $selected)
        ];
    }
    return $options;
};

// shopping cart mode and price tag mode
$cart_mode = (($product_data['product_cart_mode'] ?? '') == 2) ? 2 : 1;
$cart_mode_options = $build_options([
    1 => $lang['product_cart_mode_on'],
    2 => $lang['product_cart_mode_off']
], $cart_mode);

$pricetag_mode = (($product_data['product_pricetag_mode'] ?? '') == 2) ? 2 : 1;
$pricetag_mode_options = $build_options([
    1 => $lang['product_pricetag_mode_on'],
    2 => $lang['product_pricetag_mode_off']
], $pricetag_mode);

// order quantity
$product_order_quantity_min = (int) ($product_data['product_order_quantity_min'] ?? 0);
$product_order_quantity_max = (int) ($product_data['product_order_quantity_max'] ?? 0);
if($product_order_quantity_min < 2) {
    $product_order_quantity_min = 1;
}
if($product_order_quantity_max == 0) {
    $product_order_quantity_max = '';
}

// labels
$arr_checked_labels = explode(",", $product_data['labels'] ?? '');
$labels = [];
foreach($se_labels as $label) {
    $labels[] = [
        'id' => $label['label_id'],
        'color' => $label['label_color'],
        'title' => $decode($label['label_title']),
        'checked' => in_array((string) $label['label_id'], $arr_checked_labels)
    ];
}

if(!isset($product_data['type']) OR $product_data['type'] == '') {
    $product_data['type'] = 'p';
}

// decoded text fields for the template, the overrides below are prepared values
$product = array_map($decode, $product_data);
$product['product_currency'] = $decode($product_currency);
$product['product_price_net'] = $decode($product_price_net);
$product['product_nbr_stock'] = (int) ($product_data['product_nbr_stock'] ?? 0);
$product['product_cnt_sales'] = (int) ($product_data['product_cnt_sales'] ?? 0);
$product['product_order_quantity_min'] = $product_order_quantity_min;
$product['product_order_quantity_max'] = $product_order_quantity_max;

echo $twig->render('shop/product-form.twig', [
    'product' => $product,
    'product_id' => $get_product_id,
    'is_edit' => $is_edit,
    'is_variant' => ($product_data['type'] == 'v'),
    'show_variant_button' => $show_variant_button,
    'form_header_mode' => $form_header_mode,
    'writer_uri' => $writer_uri,
    'editors' => $editors,
    'images' => $images,
    'categories' => $categories,
    'filter_groups' => $filter_groups,
    'features' => $features,
    'options' => $options,
    'user_uploads' => (($product_data['file_attachment_user'] ?? '') == '2'),
    'labels' => $labels,
    'hooks' => $hooks,
    'has_variants' => ($cnt_variants > 1),
    'variants' => $variant_links,
    'variant_type' => (int) ($product_data['product_variant_type'] ?? 0),
    'accessories' => $dropper_items($product_data['product_accessories'] ?? null),
    'related' => $dropper_items($product_data['product_related'] ?? null),
    'addons' => $dropper_items($product_data['product_addons'] ?? null),
    'volume_discounts' => $volume_discounts,
    'translation_urls' => $translation_urls,
    'content_tags' => $decode(se_tags_csv_for_content('product', (int) ($product_data['id'] ?? 0))),
    'se_base_url' => $se_base_url,
    'releasedate' => $releasedate,
    'releasedate_max' => $releasedate_max,
    'releasedate_summary' => se_format_datetime(strtotime($releasedate)),
    'comments_summary' => ($comments == 1) ? $lang['yes'] : $lang['no'],
    'votings_summary' => $votings_labels[$votings],
    'fixed' => (($product_data['fixed'] ?? '') == '1'),
    'ignore_stock' => (($product_data['product_stock_mode'] ?? '') == 1),
    'addon_only' => (($product_data['product_addon_only'] ?? '2') == 1),
    'variant_docs_link' => se_print_docs_link("05-00-shop.md"),
    'canonical_docs_link' => se_print_docs_link("05-00-shop.md", null, "canonical-url"),
    'addon_module_forms' => se_render_record_addons('product', $record_data),
    'main_catalog_options' => $main_catalog_options,
    'lang_options' => $lang_options,
    'status_options' => $status_options,
    'comments_options' => $comments_options,
    'votings_options' => $votings_options,
    'rss_options' => $rss_options,
    'tax_options' => $tax_options,
    'shipping_mode_options' => $shipping_mode_options,
    'shipping_cat_options' => $shipping_cat_options,
    'price_group_options' => $price_group_options,
    'delivery_time_options' => $delivery_time_options,
    'snippet_price_options' => $snippet_price_options,
    'snippet_text_options' => $snippet_text_options,
    'file_options' => $file_options($product_data['file_attachment'] ?? ''),
    'file_as_options' => $file_options($product_data['file_attachment_as'] ?? ''),
    'cart_mode_options' => $cart_mode_options,
    'pricetag_mode_options' => $pricetag_mode_options
]);
