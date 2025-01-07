<?php

$writer_uri = '/admin/shop/edit/';
$duplicate_uri = '/admin/shop/duplicate/';

include '../acp/core/templates.php';
global $lang_codes;

if($_REQUEST['action'] == 'list_products') {

    // defaults
    $order_by = 'lastedit';
    $order_direction = 'DESC';
    $limit_start = $_SESSION['pagination_products_page'] ?? 0;
    $nbr_show_items = 20;

    $match_str = $_SESSION['products_text_filter'] ?? '';
    $keyword_str = $_SESSION['products_keyword_filter'] ?? '';
    $order_key = $_SESSION['sorting_products'] ?? $order_by;
    $order_direction = $_SESSION['sorting_products_direction'] ?? $order_direction;

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
        $this_filter = explode(" ",$keyword_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_keyword = [
                "tags[~]" => "%$f%"
            ];
        }
    }

    $db_where = [
        "AND" => $filter_base+$filter_by_str+$filter_by_keyword
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
    echo se_print_pagination('/admin/shop/write/',$nbr_pages,$_SESSION['pagination_products_page']);

    echo '<table class="table table-striped table-hover">';

    foreach($products_data as $product) {

        $product_id = (int) $product['id'];

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

        $product_lang_thumb = '<img src="/assets/lang/'.$product['product_lang'].'/flag.png" width="15" title="'.$product['product_lang'].'" alt="'.$product['product_lang'].'">';

        // thumbnail
        $prod_image = explode("<->", $product['images']);
        $show_thumb = '';
        if($prod_image[1] != "") {
            $image_src = str_replace("../","/",$prod_image[1]);
            $show_thumb  = '<a data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="<img src=\''.$image_src.'\'>">';
            $show_thumb .= '<div class="show-thumb" style="background-image: url('.$image_src.');">';
            $show_thumb .= '</div>';
        } else {
            $show_thumb = '<div class="show-thumb" style="background-image: url(/assets/themes/administration/images/no-image.png);">';
        }

        // buttons
        $btn_edit_tpl  = '<form action="'.$writer_uri.'" method="post" class="d-inline">';
        $btn_edit_tpl .= '<button class="btn btn-default" name="product_id" value="'.$product_id.'">'.$icon['edit'].'</button>';
        $btn_edit_tpl .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $btn_edit_tpl .=  '</form>';

        $btn_duplicate_tpl  = '<form action="'.$duplicate_uri.'" method="post" class="d-inline">';
        $btn_duplicate_tpl .= '<button class="btn btn-default" name="duplicate_id" value="'.$product_id.'">'.$icon['copy'].'</button>';
        $btn_duplicate_tpl .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $btn_duplicate_tpl .=  '</form>';

        echo '<tr>';
        echo '<td>'.$product['id'].'</td>';
        echo '<td>'.$product['fixed'].'</td>';
        echo '<td>'.$show_thumb.'</td>';
        echo '<td><h6>'.$product_lang_thumb.' '.$product['title'].'</h6>'.$trimmed_teaser.'<br>'.$show_items_dates.'</td>';
        echo '<td>'.$product['product_price_net'].'</td>';
        echo '<td>'.$btn_edit_tpl.' '.$btn_duplicate_tpl.'</td>';
        echo '</tr>';
    }

    echo '</table>';
    echo '</div>';

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
            echo '<button hx-post="/admin/shop/read/" hx-swap="innerHTML" hx-target="#PriceGroupForm" class="btn btn-default btn-sm text-success" name="open_price_group" value="'.$group['id'].'">'.$icon['edit'].'</button> ';
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
        1 => $se_prefs['prefs_posts_products_default_tax'],
        2 => $se_prefs['prefs_posts_products_tax_alt1'],
        3 => $se_prefs['prefs_posts_products_tax_alt2'],
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
                hx-post="/admin/shop/write/"
                hx-swap="none"
                hx-include="[name=\'csrf_token\']"
                name="save_price"
                value="'.$price_group_id.'"
                class="list-group-item list-group-item-action '.$active.'">'.$btn_name.'</button>';
    echo '</form>';

}

// list categories in sidebar
if($_REQUEST['action'] == 'list_categories') {

    $get_categories = se_get_categories();
    echo '<div class="list-group">';
    foreach($get_categories as $c) {

        $cat_lang_thumb = '<img src="/assets/lang/'.$c['cat_lang'].'/flag.png" width="15" alt="'.$c['cat_lang'].'">';
        $active = '';
        if(str_contains($_SESSION['filter_prod_categories'],$c['cat_hash'])) {
            $active = 'active';
        }

        echo '<button 
                hx-post="/admin/shop/write/"
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

        $flag = '<img src="/assets/lang/' . $data['snippet_lang'] . '/flag.png" width="15">';

        $btn_edit  = '<form action="/admin/shop/features/edit/" method="post" class="d-inline">';
        $btn_edit .= '<button class="btn btn-default" name="features-form" value="'.$data['snippet_id'].'">'.$icon['edit'].'</button>';
        $btn_edit .=  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        $btn_edit .=  '</form>';

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

        $flag = '<img src="/assets/lang/' . $data['snippet_lang'] . '/flag.png" width="15">';

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

    $all_filters = se_get_product_filter_groups('all');

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
    foreach($all_filters as $k => $v) {

        $group_title = $v['filter_title'];
        $group_id = $v['filter_id'];
        $group_prio = $v['filter_priority'];
        $group_categories = explode(",",$v['filter_categories']);

        $type = '';
        if($v['filter_input_type'] == '1') {
            $type = $icon['ui_radios'];
        } else {
            $type = $icon['ui_checks'];
        }

        $flag = '<img src="/assets/lang/' . $v['filter_lang'] . '/flag.png" width="15">';

        $get_filter_items = se_get_product_filter_values($group_id);

        echo '<tr>';
        echo '<td>'.$flag.'</td>';
        echo '<td>'.$group_prio.'</td>';
        echo '<td>'.$type.'</td>';
        echo '<td>';

        echo '<form action="/admin/shop/filters/edit/" method="post" class="">';
        echo '<button class="btn btn-default" name="edit_group" value="'.$group_id.'">'.$icon['edit'].' '.$group_title.'</button>';
        echo  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        echo  '</form>';

        // show categories
        $get_categories = se_get_categories();
        foreach($get_categories as $k => $v) {
            if (in_array($v['cat_hash'], $group_categories)) {
                echo '<span class="badge text-bg-secondary opacity-50">'.$v['cat_name'].'</span> ';
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
        echo '<input type="hidden" name="parent_id" value="'.$group_id.'">';
        echo '</button>';
        echo  '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        echo '</form>';
        echo '</td>';
        echo '</tr>';

    }

    echo '</table>';
    echo '</div>';



}