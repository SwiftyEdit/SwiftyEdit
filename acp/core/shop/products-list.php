<?php

$reader_uri = '/admin-xhr/shop/read/';
$writer_uri = '/admin-xhr/shop/write/';

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['shop'].' '.$lang['nav_btn_shop'];
echo '<a href="/admin/shop/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-9">';

echo '<div id="getProducts" class="" hx-get="'.$reader_uri.'?action=list_products" hx-trigger="load, update_products_list from:body, updated_global_filter from:body">';
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
echo '<input class="form-control" type="text" name="products_text_filter" value="" placeholder="'.$lang['search'].'">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';

echo '<div class="pt-1" hx-get="'.$reader_uri.'?action=list_active_searches" hx-trigger="load, changed, update_products_list from:body, updated_global_filter from:body"></div>';



$sorting_options = [
    $lang['label_priority'] => 'priority',
    $lang['label_data_submited'] => 'date',
    $lang['label_data_last_edit'] => 'lastedit',
    $lang['label_price'] => 'product_price_net'
];

$input_select_sortings = [
    "input_name" => "sorting_products",
    "input_value" => $_SESSION['sorting_products'],
    "label" => $lang['sorting'],
    "options" => $sorting_options,
    "type" => "select"
];


echo '<div class="row mb-3 mt-1">';
echo '<div class="col-md-8">';
echo '<form hx-post="'.$writer_uri.'" hx-swap="none" hx-trigger="change" method="POST">';
echo se_print_form_input($input_select_sortings);
echo $hidden_csrf_token;
echo '</form>';
echo '</div>';
echo '<div class="col-md-4">';
echo '<label class="form-label">&nbsp;</label>';
echo '<div class="btn-group d-flex">';
echo '<button hx-post="'.$writer_uri.'" hx-trigger="click" hx-include="[name=\'csrf_token\']" hx-swap="none" name="sorting_products_dir" value="ASC" title="'.$lang['btn_sort_asc'].'" class="btn btn-default w-100 '.$sel_value['sort_asc'].'">'.$icon['arrow_up'].'</button> ';
echo '<button hx-post="'.$writer_uri.'" hx-trigger="click" hx-include="[name=\'csrf_token\']" hx-swap="none" name="sorting_products_dir" value="DESC" title="'.$lang['btn_sort_desc'].'" class="btn btn-default w-100 '.$sel_value['sort_desc'].'">'.$icon['arrow_down'].'</button>';
echo '</div>';
echo '</div>';
echo '</div>';


echo '</div>';
echo '</div>';

echo '<div class="card mb-2">';
echo '<div class="card-header">'.$lang['label_categories'].'</div>';
echo '<div class="scroll-container p-0">';
echo '<div id="keyList" hx-get="'.$reader_uri.'?action=list_categories" hx-trigger="load, update_products_list from:body, updated_global_filter from:body"></div>';
echo '</div>';
echo '</div>';

echo '<div class="card">';
echo '<div class="card-header">'.$lang['label_keywords'].'</div>';
echo '<div class="card-body">';
echo '<div id="keyList" hx-get="'.$reader_uri.'?action=list_keyword_btn" hx-trigger="load, update_products_list from:body, updated_global_filter from:body"></div>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';