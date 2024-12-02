<?php

$reader_uri = '/admin/shop/read/';
$writer_uri = '/admin/shop/write/';

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['shop'].' '.$lang['nav_btn_shop'];
echo '<a href="/admin/shop/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div id="getProducts" class="" hx-post="'.$reader_uri.'?action=list_products" hx-trigger="load, update_products_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';

echo '</div>';
echo '<div class="col-md-3">';

// sidebar

echo '<div class="card mb-2">';
echo '<div class="card-header">'.$lang['filter'].'</div>';
echo '<div class="card-body">';
echo '<form hx-post="'.$writer_uri.'" hx-swap="none" hx-on--after-request="this.reset()" method="POST" class="mt-1">';
echo '<div class="input-group">';
echo '<span class="input-group-text">'.$icon['search'].'</span>';
echo '<input class="form-control" type="text" name="snippets_text_filter" value="" placeholder="'.$lang['search'].'">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';

echo '<div class="pt-1" hx-get="'.$reader_uri.'?action=list_active_searches" hx-trigger="load, changed, update_products_list from:body, updated_global_filter from:body"></div>';



$sorting_options = [
    $lang['label_priority'] => 'priority',
    $lang['label_data_submited'] => 'time_submited',
    $lang['label_data_last_edit'] => 'time_edit',
    $lang['label_price'] => 'price'
];

$input_select_sortings = [
    "input_name" => "sorting_products",
    "input_value" => $_SESSION['sorting_products'],
    "label" => $lang['sorting'],
    "options" => $sorting_options,
    "type" => "select"
];

echo '<form hx-post="'.$writer_uri.'" hx-swap="none" method="POST" class="mt-1">';
echo '<div class="row mb-3">';
echo '<div class="col-md-8">';
echo se_print_form_input($input_select_sortings);
echo '</div>';
echo '<div class="col-md-4">';
echo '<label class="form-label">&nbsp;</label>';
echo '<div class="btn-group d-flex">';
echo '<button name="sorting_products_dir" value="asc" title="'.$lang['btn_sort_asc'].'" class="btn btn-sm btn-default w-100 '.$sel_value['sort_asc'].'">'.$icon['arrow_up'].'</button> ';
echo '<button name="sorting_products_dir" value="desc" title="'.$lang['btn_sort_desc'].'" class="btn btn-sm btn-default w-100 '.$sel_value['sort_desc'].'">'.$icon['arrow_down'].'</button>';
echo '</div>';
echo '</div>';
echo '</div>';
echo $hidden_csrf_token;
echo '</form>';

echo '</div>';
echo '</div>';

echo '<div class="card mb-2">';
echo '<div class="card-header">'.$lang['label_categories'].'</div>';
echo '<div class="card-body">';
echo '<div id="keyList" hx-post="'.$reader_uri.'?action=list_categories" hx-trigger="load, update_products_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']"></div>';
echo '</div>';
echo '</div>';

echo '<div class="card">';
echo '<div class="card-header">'.$lang['label_keywords'].'</div>';
echo '<div class="card-body">';
echo '<div id="keyList" hx-post="'.$reader_uri.'?action=list_keywords" hx-trigger="load, update_products_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']"></div>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';