<?php

/**
 * SwiftyEdit /admin/shop/
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $db_content
 * @var object $db_posts
 * @var string $hidden_csrf_token
 * @var array $se_settings
 */

$writer_uri = '/admin/shop/edit/';
$duplicate_uri = '/admin/shop/duplicate/';

$global_filter_languages = json_decode($_SESSION['global_filter_languages'],true);
$global_filter_status = json_decode($_SESSION['global_filter_status'],true);
$global_filter_label = json_decode($_SESSION['global_filter_label'],true);

include_once '../acp/core/templates.php';
global $lang_codes;

$se_labels = se_get_labels();
$se_categories = se_get_categories();

/**
 * list active keywords from search input
 * used in the sidebar
 */
if($_REQUEST['action'] == 'list_active_searches') {

    if(isset($_SESSION['products_text_filter']) AND $_SESSION['products_text_filter'] != "") {
        unset($all_filter);
        $all_filter_products = explode(" ", $_SESSION['products_text_filter']);

        foreach($all_filter_products as $f) {
            if($_REQUEST['rm_keyword'] == "$f") { continue; }
            if($f == "") { continue; }
            $btn_remove_keyword .= '<button class="btn btn-sm btn-default m-1" name="rmkey" value="'.$f.'" hx-post="/admin-xhr/shop/write/" hx-trigger="click" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button>';
        }
        if(isset($btn_remove_keyword)) {
            echo '<div class="d-inline">'.$btn_remove_keyword.'</div>';
        }
    }
    exit;
}

if($_REQUEST['action'] == 'list_active_searches_orders') {
    if(isset($_SESSION['orders_text_filter']) AND $_SESSION['orders_text_filter'] != "") {
        unset($all_filter);
        $all_filter_orders = explode(" ", $_SESSION['orders_text_filter']);

        foreach($all_filter_orders as $f) {
            if($_REQUEST['rm_keyword'] == "$f") { continue; }
            if($f == "") { continue; }
            $btn_remove_keyword .= '<button class="btn btn-sm btn-default m-1" name="rmkey_orders" value="'.$f.'" hx-post="/admin-xhr/shop/write/" hx-trigger="click" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button>';
        }
        if(isset($btn_remove_keyword)) {
            echo '<div class="d-inline">'.$btn_remove_keyword.'</div>';
        }
    }
    exit;
}

// list all keywords
// used in sidebar
if($_REQUEST['action'] == 'list_keyword_btn') {
    $get_keywords = se_get_products_keywords();
    arsort($get_keywords);
    $vals = ['csrf_token' => $_SESSION['token']];
    echo '<div class="scroll-container">';
    foreach($get_keywords as $k => $v) {
        $k = trim($k);
        if(str_contains($_SESSION['products_keyword_filter'],$k)) {
            echo '<button name="remove_keyword" value="'.$k.'" hx-post="/admin-xhr/shop/write/" hx-trigger="click" hx-swap="none" hx-vals=\''.json_encode($vals).'\' class="btn btn-default active btn-xs mb-1">'.$k.' <span class="badge bg-secondary">'.$v.'</span></button> ';
        } else {
            echo '<button name="add_keyword" value="'.$k.'" hx-post="/admin-xhr/shop/write/" hx-trigger="click" hx-swap="none" hx-vals=\''.json_encode($vals).'\' class="btn btn-default btn-xs mb-1">'.$k.' <span class="badge bg-secondary">'.$v.'</span></button> ';
        }
    }
    echo '</div>';
    exit;
}


// list products
if($_REQUEST['action'] == 'list_products') {

    // defaults
    $order_by = 'lastedit';
    $order_direction = 'DESC';
    $limit_start = $_SESSION['pagination_products_page'] ?? 0;
    $nbr_show_items = 10;

    $match_str = $_SESSION['products_text_filter'] ?? '';
    $keyword_str = $_SESSION['products_keyword_filter'] ?? '';
    $order_key = $_SESSION['sorting_products'] ?? $order_by;
    $order_direction = $_SESSION['sorting_products_direction'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    $filter_base = [
        "AND" => [
            "type" => 'p'
        ]
    ];

    $filter_by_str = array();
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "title[~]" => "%$f%",
                    "teaser[~]" => "%$f%",
                    "text[~]" => "%$f%",
                    "text_additional1[~]" => "%$f%",
                    "text_additional2[~]" => "%$f%",
                    "text_additional3[~]" => "%$f%",
                    "text_additional4[~]" => "%$f%",
                    "text_additional5[~]" => "%$f%"
                ]
            ];
        }
    }

    $filter_by_keyword = array();
    if($keyword_str != '') {
        $this_filter = explode(",",$keyword_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_keyword = [
                "tags[~]" => "$f"
            ];
        }
    }

    $filter_by_category = array();
    if($_SESSION['filter_prod_categories'] != '') {
        $cat_filter = explode(" ",$_SESSION['filter_prod_categories']);
        $cat_filter = array_filter($cat_filter);
        $filter_by_category = [
                "categories[~]" => $cat_filter
        ];
    }

    // global language filter
    $filter_by_language = array();
    if(is_array($global_filter_languages)) {
        $lang_filter = array_filter($global_filter_languages);
        $filter_by_language = [
            "product_lang[~]" => $lang_filter
        ];
    }

    // global status filter
    // global status for ghost = 4 in but in products = 3
    $filter_by_status = array();
    if(is_array($global_filter_status)) {
        $status_filter = array_filter($global_filter_status);
        $index = array_search(4,$status_filter);
        if ($index !== false) {
            $status_filter[$index] = 3;
        }
        $filter_by_status = [
            "status[~]" => $status_filter
        ];
    }

    // global label filter
    $filter_by_label = array();
    if(is_array($global_filter_label)) {
        $label_filter = array_filter($global_filter_label);
        $filter_by_label = [
            "labels[~]" => $label_filter
        ];
    }


    $db_where = [
        "AND" => $filter_base+$filter_by_str+$filter_by_keyword+$filter_by_category+$filter_by_language+$filter_by_status+$filter_by_label
    ];

    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];

    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

    $products_data_cnt = $db_posts->count("se_products", $db_where);


    $products_data = $db_posts->select("se_products","*",
        $db_where+$db_order+$db_limit
    );

    $nbr_pages = ceil($products_data_cnt/$nbr_show_items);

    echo '<div class="card p-3">';
    echo se_print_pagination('/admin-xhr/shop/write/',$nbr_pages,$_SESSION['pagination_products_page']);

    echo '<table class="table table-striped table-hover">';

    foreach($products_data as $product) {

        $product_id = (int) $product['id'];
        $add_row_class = '';
        $add_label = '';

        if($product['status'] == '2') {
            $add_row_class = 'item_is_draft';
            $add_label = '<span class="badge badge-se">'.$lang['status_draft'].'</span>';
        }
        if($product['status'] == '3') {
            $add_row_class = 'item_is_ghost';
            $add_label = '<span class="badge badge-se">'.$lang['status_ghost'].'</span>';
        }

        /* trim teaser to $trim chars */
        $trimmed_teaser = se_return_first_chars($product['teaser'],100);

        // show dates
        $published_date = '<span title="'.$lang['label_data_submited'].'">'.$icon['save'].': '.se_format_datetime($product['date']).'</span>';
        $release_date = '<span title="'.$lang['label_data_releasedate'].'">'.$icon['calendar_check'].': '.se_format_datetime($product['releasedate']).'</span>';
        $lastedit_date = '';
        if($product['lastedit'] != '') {
            $lastedit_date = '<span title="'.$lang['label_data_lastedit'].'">'.$icon['edit'].': '.se_format_datetime($product['lastedit']).'</span>';
        }
        $show_items_dates = '<span class="text-muted small">'.$published_date.' | '.$lastedit_date.' | '.$release_date.'</span>';

        $product_lang_thumb = '<img src="'.return_language_flag_src($product['product_lang']).'" width="15" title="'.$product['product_lang'].'" alt="'.$product['product_lang'].'">';

        // labels
        $get_labels = explode(',',$product['labels']);
        $label = '';
        if($product['labels'] != '') {
            $label = '<p>';
            foreach($get_labels as $labels) {

                foreach($se_labels as $l) {
                    if($labels == $l['label_id']) {
                        $label_color = $l['label_color'];
                        $label_title = $l['label_title'];
                    }
                }

                $label .= '<span class="label-dot" style="background-color:'.$label_color.';" title="'.$label_title.'"></span>';
            }
            $label .= '</p>';
        }

        // categories
        $get_categories = explode('<->',$product['categories']);
        $categories = '';
        if($product['categories'] != '') {
            foreach($get_categories as $cats) {

                foreach($se_categories as $cat) {
                    if($cats == $cat['cat_hash']) {
                        $cat_title = $cat['cat_name'];
                        $cat_description = $cat['cat_description'];
                    }
                }
                $categories .= '<span class="text-muted small" title="'.$cat_description.'">'.$icon['tags'].' '.$cat_title.'</span> ';
            }
        }

        // thumbnail
        $prod_image = explode("<->", $product['images']);
        $show_thumb = '';
        if($prod_image[1] != "") {
            $image_src = str_replace("../images/","/",$prod_image[1]);
            $show_thumb  = '<div data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="<img src=\''.$image_src.'\'>">';
            $show_thumb .= '<div class="show-thumb" style="background-image: url('.$image_src.');">';
            $show_thumb .= '</div>';
            $show_thumb .= '</div>';
        } else {
            $show_thumb = '<div class="show-thumb" style="background-image: url(/themes/administration/images/no-image.png);">';
        }

        // variants
        $variants = [];
        $variants = se_get_product_variants($product_id);
        $cnt_variants = count($variants);

        $edit_variant_select = '';
        if($cnt_variants > 1) {
            $edit_variant_select = '<form class="mt-2" action="/admin/shop/edit/" method="POST">';
            $edit_variant_select .= '<div class="dropdown">';
            $edit_variant_select .= '<button class="btn btn-default btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">'.$lang['label_product_variants'].' ('.$cnt_variants.')</button>';
            $edit_variant_select .= '<ul class="dropdown-menu">';
            foreach($variants as $variant) {
                $edit_variant_select .= '<li><button class="dropdown-item" name="product_id" value="'.$variant['id'].'" type="submit">'.$variant['id'].' '.$variant['title'].'</button></li>';
            }
            $edit_variant_select .= '</ul>';
            $edit_variant_select .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
            $edit_variant_select .= '</form>';
        }

        // fix button
        $icon_fixed_form = '<form hx-post="/admin-xhr/shop/write/" method="POST" class="form-inline">';
        if($product['fixed'] == '1') {
            $icon_fixed_form .= '<button type="submit" class="btn btn-link w-100" name="rfixed" value="'.$product['id'].'">'.$icon['star'].'</button>';
        } else {
            $icon_fixed_form .= '<button type="submit" class="btn btn-link w-100" name="sfixed" value="'.$product['id'].'">'.$icon['star_outline'].'</button>';
        }
        $icon_fixed_form .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $icon_fixed_form .= '</form>';

        // priority form
        $prio_form  = '<form hx-post="/admin-xhr/shop/write/" hx-trigger="keyup changed delay:1s" method="POST" class="no-enter">';
        $prio_form .= '<input type="number" name="priority" value="'.$product['priority'].'" class="form-control" style="max-width:150px">';
        $prio_form .= '<input type="hidden" name="prio_id" value="'.$product['id'].'">';
        $prio_form .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $prio_form .= '</form>';


        // buttons
        $btn_edit_tpl  = '<form action="'.$writer_uri.'" method="post" class="d-inline">';
        $btn_edit_tpl .= '<button class="btn btn-default text-success" name="product_id" value="'.$product_id.'">'.$icon['edit'].'</button>';
        $btn_edit_tpl .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $btn_edit_tpl .= '</form>';

        $btn_duplicate_tpl  = '<form action="'.$duplicate_uri.'" method="post" class="d-inline">';
        $btn_duplicate_tpl .= '<button class="btn btn-default" name="duplicate_id" value="'.$product_id.'">'.$icon['copy'].'</button>';
        $btn_duplicate_tpl .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $btn_duplicate_tpl .= '</form>';


        // price tag from product or from price groups

        if($product['product_price_group'] != '' AND $product['product_price_group'] != 'null') {
            $price_data = se_get_price_group_data($product['product_price_group']);
            $product_tax = $price_data['tax'];
            $product_price_net = $price_data['price_net'];
            $product_volume_discounts = $price_data['price_volume_discount'];
        } else {
            $product_tax = $product['product_tax'];
            $product_price_net = $product['product_price_net'];
            $product_volume_discounts = $product['product_price_volume_discounts'];
        }

        if ($product_tax == '1') {
            $tax = $se_settings['posts_products_default_tax'];
        } else if ($product['product_tax'] == '2') {
            $tax = $se_settings['posts_products_tax_alt1'];
        } else {
            $tax = $se_settings['posts_products_tax_alt2'];
        }

        if (empty($product_price_net)) {
            $product_price_net = 0;
        }

        $post_price_net = str_replace('.', '', $product_price_net);
        $post_price_net = str_replace(',', '.', $post_price_net);

        $post_price_gross = $post_price_net * ($tax + 100) / 100;

        $post_price_net_format = se_post_print_currency($post_price_net);
        $post_price_gross_format = se_post_print_currency($post_price_gross);

        $show_items_price = '<div class="card p-2 text-nowrap">';
        $show_items_price .= '<span class="small">' . $product['product_currency'] . ' ' . $post_price_net_format . '</span>';
        $show_items_price .= '<span class="small"> + ' . $tax . '%</span>';
        $show_items_price .= '<span class="text-success">' . $product['product_currency'] . ' ' . $post_price_gross_format . '</span>';
        $show_items_price .= '</div>';

        echo '<tr class="'.$add_row_class.'">';
        echo '<td>'.$product['id'].'</td>';
        echo '<td>'.$icon_fixed_form.'</td>';
        echo '<td>'.$prio_form.'</td>';
        echo '<td>'.$show_thumb.'</td>';
        echo '<td>';
        echo '<h6>'.$product_lang_thumb.' '.$product['title'].' '.$add_label.'</h6>'.$trimmed_teaser.'<br>'.$show_items_dates;
        echo $label;
        echo $categories;
        if($edit_variant_select != '') {
            echo $edit_variant_select;
        }
        echo '</td>';
        echo '<td>'.$show_items_price.'</td>';
        echo '<td class="text-nowrap">'.$btn_edit_tpl.' '.$btn_duplicate_tpl.'</td>';
        echo '</tr>';
    }

    echo '</table>';
    echo '</div>';

}

// List of products that have been assigned to a specific filter
if($_REQUEST['show'] == 'products_by_filter') {
    $filter_id = (int) $_REQUEST['filter_id'];
    // search in json string
    $get_filter = ':"'.$filter_id.'"';
    $get_products = $db_posts->select("se_products",["id","title"],[
        "filter[~]" => $get_filter,
        "ORDER" => ["lastedit" => "DESC"],
        "LIMIT" => 50,
    ]);

    foreach($get_products as $prod) {
        echo '<div class="d-flex justify-content-start border-end mb-1">';
        echo '<div class="flex-shrink-0 p-1">#'.$prod['id'].'</div>';
        echo '<div class="w-100 p-1">'.htmlentities($prod['title']).'</div>';
        echo '</div>';
    }
}


// list price groups
if($_REQUEST['action'] == 'list_price_groups') {

    $get_all_price_groups = se_get_price_groups();
    $cnt_price_groups = count($get_all_price_groups);

    if($cnt_price_groups < 1) {
        echo '<div class="alert alert-info">'.$lang['msg_no_entries_found'].'</div>';
        exit;
    } else {

        echo '<table class="table table-sm">';
        echo '<tr>';
        echo '<td>'.$lang['label_title'].'</td>';
        echo '<td>'.$lang['label_price'].'</td>';
        echo '<td>'.$lang['label_scaling_prices'].'</td>';
        echo '<td></td>';
        echo '<td>';
        foreach ($get_all_price_groups as $group) {

            $status_volume_discounts = $icon['check'];
            if($group['price_volume_discount'] == '' OR $group['price_volume_discount'] == 'null') {
                $status_volume_discounts = $icon['x'];
            }

            echo '<tr>';
            echo '<td>'.$group['title'].'</td>';
            echo '<td>'.$group['price_net'].'</td>';
            echo '<td>'.$status_volume_discounts.'</td>';
            echo '<td class="text-end">';
            echo '<form>';
            echo '<button hx-post="/admin-xhr/shop/read/" hx-trigger="click" hx-swap="innerHTML" hx-target="#PriceGroupForm" class="btn btn-default btn-sm text-success" name="open_price_group" value="'.$group['id'].'">'.$icon['edit'].'</button> ';
            echo '<button class="btn btn-default btn-sm text-danger" name="delete" value="'.$group['id'].'">'.$icon['trash_alt'].'</button>';
            echo $hidden_csrf_token;
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';


    }
}

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

    $form_tpl = file_get_contents('../acp/templates/form-edit-price-groups.tpl');
    /* replace all entries from $lang */
    foreach($lang as $k => $v) {
        $form_tpl = str_replace('{'.$k.'}', $lang[$k], $form_tpl);
    }

    // buid select for tax
    $tax_options = [
        1 => $se_settings['posts_products_default_tax'],
        2 => $se_settings['posts_products_tax_alt1'],
        3 => $se_settings['posts_products_tax_alt2'],
    ];

    // use default tax if $price_group['tax'] is not set
    $get_tax = $tax_options[$price_group['tax']] ?? $tax_options[1];

    // Dropdown
    $select_tax = "<select name='tax' class='form-control custom-select' id='tax'>";
    foreach ($tax_options as $key => $value) {
        $selected = ($price_group['tax'] == $key) ? 'selected' : '';
        $select_tax .= "<option value='{$key}' {$selected}>{$value}</option>";
    }
    $select_tax .= '</select>';

    // volume discounts

    $volume_discounts = json_decode($price_group['price_volume_discount'],true);
    $cnt_volume_discounts = 1;
    if(is_array($volume_discounts)) {
        $cnt_volume_discounts = count($volume_discounts);
    }


    $show_price_volume_discount = '<div class="card my-2">';
    $show_price_volume_discount .= '<div class="card-header">';
    $show_price_volume_discount .= '<span>'.$lang['label_product_scaling_prices'].'</span>';
    $show_price_volume_discount .= '<button class="btn btn-default btn-sm float-end" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVDP" aria-expanded="false" aria-controls="collapseExample">+</button>';
    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '<div class="card-body collapse" id="collapseVDP">';


    for($i=0;$i<($cnt_volume_discounts+5);$i++) {

        $this_ammount = $volume_discounts[$i]['amount'];
        $price_net = $volume_discounts[$i]['price'];

        $show_price_volume_discount .= '<div class="calculate_price">';
        $show_price_volume_discount .= '<div class="row">';
        $show_price_volume_discount .= '<div class="col-md-4">';
        $show_price_volume_discount .= '<label>' . $lang['label_product_amount'] . '</label>';
        $show_price_volume_discount .= '<input class="form-control" name="product_vd_amount[]" type="number" value="'.$this_ammount.'">';
        $show_price_volume_discount .= '</div>';
        $show_price_volume_discount .= '<div class="col-md-4">';
        $show_price_volume_discount .= '<label>' . $lang['label_product_price'] . ' ' . $lang['label_product_net'] . '</label>';
        $show_price_volume_discount .= '<input class="form-control prod_price_net" name="product_vd_price[]" type="text" value="'.$price_net.'">';
        $show_price_volume_discount .= '</div>';
        $show_price_volume_discount .= '<div class="col-md-4">';
        $show_price_volume_discount .= '<label>' . $lang['label_product_price'] . ' ' . $lang['label_product_gross'] . '</label>';
        $show_price_volume_discount .= '<input class="form-control prod_price_gross" name="product_vd_price_gross[]" type="text" value="">';
        $show_price_volume_discount .= '</div>';
        $show_price_volume_discount .= '</div>';
        $show_price_volume_discount .= '</div>';
    }

    $show_price_volume_discount .= '</div>';
    $show_price_volume_discount .= '</div>';

    $form_tpl = str_replace('{title}', $price_group['title'], $form_tpl);
    $form_tpl = str_replace('{amount}', $price_group['amount'], $form_tpl);
    $form_tpl = str_replace('{unit}', $price_group['unit'], $form_tpl);
    $form_tpl = str_replace('{price_net}', $price_group['price_net'], $form_tpl);
    $form_tpl = str_replace('{select_tax}', $select_tax, $form_tpl);
    $form_tpl = str_replace('{show_price_volume_discount}', $show_price_volume_discount, $form_tpl);

    if(isset($price_group['id'])) {
        $form_tpl = str_replace('{btn_send}', $lang['button_update'], $form_tpl);
        $form_tpl = str_replace('{id}', $price_group['id'], $form_tpl);
        $price_group_id = $price_group['id'];
        $btn_name = $lang['button_update'];
    } else {
        $form_tpl = str_replace('{btn_send}', $lang['button_save'], $form_tpl);
        $form_tpl = str_replace('{id}', 'new', $form_tpl);
        $price_group_id = 'new';
        $btn_name = $lang['button_save'];
    }

    echo '<form>';
    echo $form_tpl;
    echo '<button 
                hx-post="/admin-xhr/shop/write/"
                hx-trigger="click"
                hx-swap="none"
                hx-include="[name=\'csrf_token\']"
                name="save_price"
                value="'.$price_group_id.'"
                class="list-group-item list-group-item-action">'.$btn_name.'</button>';
    echo '</form>';

}

// list categories in sidebar
if($_REQUEST['action'] == 'list_categories') {

    $get_categories = se_get_categories();
    echo '<div class="list-group list-group-flush">';
    foreach($get_categories as $c) {

        $cat_lang_thumb = '<img src="'.return_language_flag_src($c['cat_lang']).'" width="15" alt="'.$c['cat_lang'].'">';
        $active = '';
        if(str_contains($_SESSION['filter_prod_categories'],$c['cat_hash'])) {
            $active = 'active';
        }

        echo '<button 
                hx-post="/admin-xhr/shop/write/"
                hx-trigger="click"
                hx-swap="none"
                hx-include="[name=\'csrf_token\']"
                name="set_filter_cat"
                value="'.$c['cat_hash'].'"
                class="list-group-item list-group-item-action '.$active.'">';
        echo ''.$c['cat_name'].'';
        echo '<span class="float-end">'.$cat_lang_thumb.'</span>';
        echo '</button>';
    }
}

// list all features
if($_REQUEST['action'] == 'list_features') {
    $show_data = se_get_posts_features();
    $cnt_data = count($show_data);

    echo '<div class="card p-3">';

    echo '<table class="table table-sm">';
    echo '<tr>';
    echo '<td>#</td>';
    echo '<td>'.$lang['label_priority'].'</td>';
    echo '<td>'.$lang['label_language'].'</td>';
    echo '<td>'.$lang['label_text'].'</td>';
    echo '<td></td>';
    echo '</tr>';

    foreach($show_data as $data) {

        $flag = '<img src="'.return_language_flag_src($data['snippet_lang']).'" width="15">';

        $btn_edit  = '<form action="/admin/shop/features/edit/" method="post" class="d-inline">';
        $btn_edit .= '<button class="btn btn-default" name="features-form" value="'.$data['snippet_id'].'">'.$icon['edit'].'</button>';
        $btn_edit .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $btn_edit .=  '</form>';

        echo '<tr>';
        echo '<td>'.$data['snippet_id'].'</td>';
        echo '<td>'.$data['snippet_priority'].'</td>';
        echo '<td>'.$flag.'</td>';
        echo '<td><strong>'.$data['snippet_title'].'</strong><br>'.$data['snippet_content'].'</td>';
        echo '<td class="text-end" style="width:120px;">';

        echo $btn_edit;

        echo '</td>';
        echo '</tr>';
    }


    echo '</table>';
    echo '</div>'; // card

}

// list all options
if($_REQUEST['action'] == 'list_options') {
    $show_data = se_get_posts_options();
    $cnt_data = count($show_data);

    echo '<div class="card p-3">';

    echo '<table class="table table-sm">';
    echo '<tr>';
    echo '<td>#</td>';
    echo '<td>'.$lang['label_priority'].'</td>';
    echo '<td>'.$lang['label_language'].'</td>';
    echo '<td>'.$lang['label_text'].'</td>';
    echo '<td></td>';
    echo '</tr>';

    foreach($show_data as $data) {

        $flag = '<img src="'.return_language_flag_src($data['snippet_lang']).'" width="15">';

        $btn_edit  = '<form action="/admin/shop/options/edit/" method="post" class="d-inline">';
        $btn_edit .= '<button class="btn btn-default" name="options-form" value="'.$data['snippet_id'].'">'.$icon['edit'].'</button>';
        $btn_edit .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $btn_edit .=  '</form>';

        $get_show_values = json_decode($data['snippet_content']);
        $show_values = '';
        foreach($get_show_values as $value) {
            $show_values .= '<span class="badge text-bg-secondary">'.$value.'</span> ';
        }

        echo '<tr>';
        echo '<td>'.$data['snippet_id'].'</td>';
        echo '<td>'.$data['snippet_priority'].'</td>';
        echo '<td>'.$flag.'</td>';
        echo '<td><strong>'.$data['snippet_title'].'</strong><br>'.$show_values.'</td>';
        echo '<td class="text-end" style="width:120px;">';

        echo $btn_edit;

        echo '</td>';
        echo '</tr>';
    }


    echo '</table>';
    echo '</div>'; // card

}

// list filters

if($_REQUEST['action'] == 'list_filters') {


    $filter_base = [
        "AND" => [
            "filter_type" => 1
        ]
    ];

    $filter_by_category = array();
    if($_SESSION['filter_prod_categories'] != '') {
        $cat_filter = explode(" ",$_SESSION['filter_prod_categories']);
        $cat_filter = array_filter($cat_filter);
        $filter_by_category = [
            "OR" => [
                "filter_categories" => $cat_filter
            ]
        ];
    }

    $filter_by_lang = array();
    if($global_filter_languages != '') {
        $filter_by_lang = [
                "filter_lang" => $global_filter_languages
        ];
    }

    $db_where = [
        "AND" => $filter_base+$filter_by_category+$filter_by_lang
    ];

    $db_order = [
        "ORDER" => [
            "filter_priority" => "DESC"
        ]
    ];

    $filters = $db_content->select("se_filter","*",
        $db_where+$db_order
    );

    echo '<div class="card p-3">';
    echo '<table class="table table-hover">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>'.$icon['translate'].'</th>';
    echo '<th>'.$icon['bars'].'</th>';
    echo '<th>Type</th>';
    echo '<th>Group</th>';
    echo '<th>Values</th>';
    echo '</tr>';
    echo '</thead>';
    foreach($filters as $k => $v) {

        $group_title = $v['filter_title'];
        $group_id = $v['filter_id'];
        $group_prio = $v['filter_priority'];
        $group_categories = explode(",",$v['filter_categories']);

        $type = '';
        if($v['filter_input_type'] == '1') {
            $type = $icon['ui_radios'];
        } else if($v['filter_input_type'] == '2') {
            $type = $icon['ui_checks'];
        } else {
            $type = $icon['sliders'];
        }

        $flag = '<img src="'.return_language_flag_src($v['filter_lang']).'" width="15">';

        $get_filter_items = se_get_product_filter_values($group_id);

        echo '<tr>';
        echo '<td>'.$flag.'</td>';
        echo '<td>'.$group_prio.'</td>';
        echo '<td>'.$type.'</td>';
        echo '<td>';

        echo '<form action="/admin/shop/filters/edit/" method="post" class="">';
        echo '<button class="btn btn-default" name="edit_group" value="'.$group_id.'"><code>#'.$group_id.'</code> '.$group_title.'</button>';
        echo  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        echo  '</form>';

        // show categories
        $get_categories = se_get_categories();
        foreach($get_categories as $key => $value) {
            if (in_array($value['cat_hash'], $group_categories)) {
                echo '<span class="badge text-bg-secondary opacity-50">'.$value['cat_name'].'</span> ';
            }
        }
        echo '</td>';
        echo '<td>';
        echo '<form action="/admin/shop/filters/edit/" method="post" class="d-inline">';
        foreach($get_filter_items as $item) {
            echo '<button type="submit" name="edit_value" value="'.$item['filter_id'].'" class="btn btn-sm btn-default me-1">';
            echo '<span class="badge text-bg-secondary rounded-pill opacity-50">'.$item['filter_priority'].'</span> ';
            echo $item['filter_title'];
            echo '</button>';
        }
        echo '<button type="submit" name="edit_value" value="new" class="btn btn-sm btn-default me-1">';
        echo '<span class="text-success">'.$icon['plus'].'</span>';
        echo '</button>';
        echo '<input type="hidden" name="parent_id" value="'.$group_id.'">';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        echo '</form>';
        echo '</td>';
        echo '</tr>';

    }

    echo '</table>';
    echo '</div>';

}

// list orders
if($_REQUEST['action'] == 'list_orders') {

    // defaults
    $order_by = 'order_time';
    $order_direction = 'DESC';
    $limit_start = $_SESSION['pagination_orders'] ?? 0;
    $nbr_show_items = 25;

    $match_str = $_SESSION['orders_text_filter'] ?? '';
    $order_key = $_SESSION['sorting_orders'] ?? $order_by;
    $order_direction = $_SESSION['sorting_orders_direction'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    $filter_base = [
        "AND" => [
            "id[>]" => 0
        ]
    ];

    $filter_by_str = array();
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "order_invoice_mail[~]" => "%$f%",
                    "order_invoice_address[~]" => "%$f%",
                    "order_products[~]" => "%$f%",
                    "order_user_comment[~]" => "%$f%",
                    "order_admin_comment[~]" => "%$f%"
                ]
            ];
        }
    }



    $db_where = [
        "AND" => $filter_base+$filter_by_str
    ];

    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];

    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

    $orders_data_cnt = $db_content->count("se_orders", $db_where);


    $orders_data = $db_content->select("se_orders","*",
        $db_where+$db_order+$db_limit
    );

    $nbr_pages = ceil($orders_data_cnt/$nbr_show_items);

    echo '<div class="card p-3">';
    echo se_print_pagination('/admin-xhr/shop/write/',$nbr_pages,$_SESSION['pagination_orders'],'10','','pagination_orders');

    $show_order_status = [
        "1" => $lang['status_order_received'],
        "2" => '<span class="text-success">'.$lang['status_order_completed'].'</span>',
        "3" => '<span class="text-danger">'.$lang['status_order_canceled'].'</span>'
    ];

    $show_payment_status = [
        "1" => '<span class="text-danger">'.$lang['status_order_payment_open'].'</span>',
        "2" => '<span class="text-success">'.$lang['status_order_payment_paid'].'</span>'
    ];

    echo '<table class="table table-hover">';
    foreach($orders_data as $order) {

        $order_status = $order['order_status'];
        $payment_status = $order['order_status_payment'];

        $vals = [
            'csrf_token' => $_SESSION['token'],
            'order_id' => $order['id']
        ];

        $dropdown = '<div class="dropdown">';
        $dropdown .= '<button class="btn btn-default dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">'.$icon['edit'].'</button>';
        $dropdown .= '<ul class="dropdown-menu">';
        $dropdown .= '<li><span class="dropdown-item-text opacity-50">Payment</span></li>';
        $dropdown .= '<li><button class="dropdown-item" hx-post="/admin-xhr/shop/write/" hx-trigger="click" name="set_payment" value="1" hx-swap="none" hx-vals=\''.json_encode($vals).'\'>'.$lang['status_order_payment_open'].'</button></li>';
        $dropdown .= '<li><button class="dropdown-item" hx-post="/admin-xhr/shop/write/" hx-trigger="click" name="set_payment" value="2" hx-swap="none" hx-vals=\''.json_encode($vals).'\'>'.$lang['status_order_payment_paid'].'</button></li>';
        $dropdown .= '<li><hr class="dropdown-divider"></li>';
        $dropdown .= '<li><span class="dropdown-item-text opacity-50">Order Status</span></li>';
        $dropdown .= '<li><button class="dropdown-item" hx-post="/admin-xhr/shop/write/" hx-trigger="click" name="set_order_status" value="1" hx-swap="none" hx-vals=\''.json_encode($vals).'\'>'.$lang['status_order_received'].'</button></li>';
        $dropdown .= '<li><button class="dropdown-item" hx-post="/admin-xhr/shop/write/" hx-trigger="click" name="set_order_status" value="2" hx-swap="none" hx-vals=\''.json_encode($vals).'\'>'.$lang['status_order_completed'].'</button></li>';
        $dropdown .= '<li><button class="dropdown-item" hx-post="/admin-xhr/shop/write/" hx-trigger="click" name="set_order_status" value="3" hx-swap="none" hx-vals=\''.json_encode($vals).'\'>'.$lang['status_order_canceled'].'</button></li>';
        $dropdown .= '</ul>';
        $dropdown .= '</div>';


        echo '<tr>';
        echo '<td>'.se_format_datetime($order['order_time']).'</td>';
        echo '<td>'.$order['order_nbr'].'</td>';
        echo '<td>'.$order['order_invoice_address'].' '.$order['order_payment_type'].' '.$order['order_invoice_mail'].'</td>';
        echo '<td>'.se_post_print_currency($order['order_price_total']).' ('.$show_payment_status[$payment_status].')</td>';
        echo '<td>'.$show_order_status[$order_status].'</td>';
        echo '<td><div class="btn-group">'.$dropdown.' ';
        echo '<button hx-get="/admin-xhr/shop/orders/read/?show_order='.$order['id'].'" hx-target="#order-modal" hx-trigger="click" data-bs-toggle="modal" data-bs-target="#order-modal" class="btn btn-default">'.$icon['info_circle'].'</button>';
        echo '</div></td>';
        echo '</tr>';
    }
    echo '</table>';


    echo '<div id="order-modal" class="modal modal-blur fade" style="display: none" aria-hidden="false" tabindex="-1">';
    echo '<div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content"></div></div>';
    echo '</div>';

    echo '</div>';
}

if(isset($_REQUEST['show_order'])) {

    $get_order_id = (int) $_REQUEST['show_order'];
    $get_order = se_get_order_details($get_order_id);

    $order_invoice_address = html_entity_decode($get_order['order_invoice_address']);
    $order_products = json_decode($get_order['order_products'],true);
    $order_time = date('d.m.Y H:i', $get_order['order_time']);
    $payment_status = $get_order['order_status_payment'];

    $show_payment_status = [
        "1" => '<span class="text-danger">'.$lang['status_order_payment_open'].'</span>',
        "2" => '<span class="text-success">'.$lang['status_order_payment_paid'].'</span>'
    ];

    echo '<div class="modal-dialog modal-xl modal-dialog-centered">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">#'.$get_order['order_nbr'].'</h5>
      <button class="btn btn-default ms-auto" onclick="printJS(\'printOrder\', \'html\')">Print</button>
    </div>
    <div class="modal-body" id="printOrder">';

    echo '<div class="row">';
    echo '<div class="col-md-6">';

    echo '<p>#' . $get_order['order_nbr'] . '</p>';
    echo '<p>' . $order_time . '</p>';
    echo '<span class="fs-4 bg-success-subtle text-success-emphasis p-2 ">'.$get_order['order_currency'].' '.se_post_print_currency($get_order['order_price_total']).'</span> ';
    echo '<span class="p-2">'.$show_payment_status[$payment_status].'</span>';
    echo '</div>';
    echo '<div class="col-md-4">';
    echo $order_invoice_address;
    echo '</div>';
    echo '</div>';

    echo '<div class="card p-3">';
    echo '<table class="table table-bordered">';
    foreach($order_products as $order_product) {
        echo '<tr>';
        echo '<td>'.$order_product['amount'].' x</td>';
        echo '<td>'.$order_product['title'];
        if($order_product['options'] != '') {
            echo '<div class="item-options">';
            echo $order_product['options'] . '<br>' . $order_product['options_comment_label'] . ':<br>' . $order_product['options_comment'];
            echo '</div>';
        }
        echo '</td>';
        echo '<td class="text-end">'.se_post_print_currency($order_product['price_net_raw']).'</td>';
        echo '<td class="text-end">'.$order_product['tax'].' %</td>';
        echo '<td class="text-end">'.se_post_print_currency($order_product['price_gross_raw']).'</td>';

        echo '</tr>';
    }

    // shipping
    echo '<tr>';
    echo '<td colspan="4">'.$lang['label_shipping'].'</td>';
    echo '<td class="text-end">'.se_post_print_currency($get_order['order_shipping_costs']).'</td>';
    echo '</tr>';

    // total
    echo '<tr>';
    echo '<td colspan="4">'.$lang['price_total'].'</td>';
    echo '<td class="text-end">'.se_post_print_currency($get_order['order_price_total']).'</td>';
    echo '</tr>';


    echo '</table>';
    echo '</div>';

    echo '</div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.$lang['close'].'</button>
    </div>
  </div>
</div>';
}