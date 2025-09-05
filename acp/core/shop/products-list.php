<?php

/**
 * @var $icon array
 * @var $hidden_csrf_token string
 * @var $lang array
 */

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





echo '<div class="accordion" id="accordionSidebar">';
echo '<div class="accordion-item">';
echo '<div class="accordion-header">';
echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategories" aria-expanded="false" aria-controls="collapseOne">'.$lang['label_categories'].'</button>';
echo '</div>';
echo '<div id="collapseCategories" class="accordion-collapse collapse" data-bs-parent="#accordionSidebar">';
echo '<div class="accordion-body p-0">';

echo '<div class="scroll-container p-0">';
echo '<div id="keyList" hx-get="'.$reader_uri.'?action=list_categories" hx-trigger="load, update_products_list from:body, updated_global_filter from:body"></div>';
echo '</div>';

echo '</div>'; // accordion-body
echo '</div>'; // collapse
echo '</div>'; // item
echo '<div class="accordion-item">';
echo '<div class="accordion-header">';
echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKeys" aria-expanded="false" aria-controls="collapseOne">'.$lang['label_keywords'].'</button>';
echo '</div>';
echo '<div id="collapseKeys" class="accordion-collapse collapse" data-bs-parent="#accordionSidebar">';
echo '<div class="accordion-body p-0">';

echo '<div class="scroll-container p-0">';
echo '<div id="keyList" hx-get="'.$reader_uri.'?action=list_keyword_btn" hx-trigger="load, update_products_list from:body, updated_global_filter from:body"></div>';
echo '</div>';

echo '</div>'; // accordion-body
echo '</div>'; // collapse
echo '</div>'; // item


echo '</div>'; // accordion

echo '</div>';
echo '</div>';


echo '<div class="card mb-2">';
echo '<div class="card-header">Cache</div>';
echo '<div class="card-body">';

$vals = ['csrf_token' => $_SESSION['token']];
echo '<div class="btn-group d-flex">';
echo '<button hx-post="'.$writer_uri.'" hx-target="#cacheResponse" hx-vals=\''.json_encode($vals).'\' hx-indicator=".htmx-indicator" class="btn btn-default w-100" name="products_cache" value="clear" title="'.$lang['btn_clear_cache'].'">'.$icon['trash'].' '.$lang['btn_delete_cache'].'</button>';
echo '<button hx-post="'.$writer_uri.'" hx-target="#cacheResponse" hx-vals=\''.json_encode($vals).'\' hx-indicator=".htmx-indicator" class="btn btn-default w-100" name="products_cache" value="update" title="'.$lang['btn_clear_cache'].'">'.$icon['arrow_clockwise'].' '.$lang['btn_update'].'</button>';
echo '</div>';

echo '<div id="cacheResponse">';
echo '<div class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>';
echo '</div>';

echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';