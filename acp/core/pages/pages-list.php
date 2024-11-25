<?php

//error_reporting(E_ALL ^E_WARNING ^E_NOTICE ^E_DEPRECATED);

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['file'].' '.$lang['nav_btn_pages'];
echo '<a href="/admin/pages/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

$reader_uri = '/admin/pages/read/';
$writer_uri = '/admin/pages/write/';

echo '<div class="app-container">';
echo '<div class="max-height-container">';

echo '<div class="row">';

echo '<div class="col-md-5">';

echo '<div class="card">';
echo '<div class="card-header">Sorted</div>';
echo '<div class="card-body">';
echo '<div class="scroll-box">';
echo '<div id="getPagesSorted" class="" hx-post="'.$reader_uri.'?action=list_pages_sorted" hx-trigger="load, changed, update_pages_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="col-md-4">';

echo '<div class="card">';
echo '<div class="card-header">Single</div>';
echo '<div class="card-body">';
echo '<div class="scroll-box">';
echo '<div id="getPagesSingle" class="" hx-post="'.$reader_uri.'?action=list_pages_single" hx-trigger="load, changed, update_pages_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="col-md-3">';
// sidebar

echo '<div class="card">';
echo '<div class="card-header">FILTER</div>';
echo '<div class="card-body">';


echo '<form hx-post="'.$writer_uri.'" hx-swap="none" method="POST" hx-on::after-request="this.reset()" class="mt-1">';
echo '<div class="input-group">';
echo '<span class="input-group-text">'.$icon['search'].'</span>';
echo '<input class="form-control" type="text" name="pages_text_filter" value="" placeholder="'.$lang['search'].'"">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';

echo '<div class="pt-1" hx-get="'.$reader_uri.'?action=list_active_searches" hx-trigger="load, changed, update_pages_list from:body, updated_global_filter from:body">';

echo '</div>';

$sorting_options = [
    $lang['label_data_last_edit'] => 'lastedit',
    $lang['label_link_name'] => 'linkname',
    $lang['label_priority'] => 'priority'
];

$input_select_page_status = [
    "input_name" => "sorting_single_pages",
    "input_value" => $_SESSION['sorting_single_pages'],
    "label" => $lang['sorting'],
    "options" => $sorting_options,
    "type" => "select"
];

echo '<form hx-post="'.$writer_uri.'" hx-swap="none" method="POST" class="mt-1">';
echo '<div class="row mb-3">';
echo '<div class="col-md-8">';
echo se_print_form_input($input_select_page_status);
echo '</div>';
echo '<div class="col-md-4">';
echo '<label class="form-label">&nbsp;</label>';
echo '<div class="btn-group d-flex">';
echo '<button name="sorting_single_pages_asc" value="asc" title="'.$lang['btn_sort_asc'].'" class="btn btn-sm btn-default w-100 '.$sel_value['sort_asc'].'">'.$icon['arrow_up'].'</button> ';
echo '<button name="sorting_single_pages_desc" value="desc" title="'.$lang['btn_sort_desc'].'" class="btn btn-sm btn-default w-100 '.$sel_value['sort_desc'].'">'.$icon['arrow_down'].'</button>';
echo '</div>';
echo '</div>';
echo '</div>';
echo $hidden_csrf_token;
echo '</form>';


echo '<div class="accordion accordion-flush" id="accordionExample">';
echo '<div class="accordion-item">';
echo '<h6 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
        '.$lang['label_keywords'].'
      </button></h6>';
echo '<div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">';
echo '<div class="accordion-body">';
echo '<div class="pt-1" hx-get="'.$reader_uri.'?action=list_keyword_btn" hx-trigger="load, changed, update_pages_list from:body, updated_global_filter from:body"></div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '<div class="accordion-item">';
echo '<h6 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
        '.$lang['label_type'].'
      </button></h6>';
echo '<div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">';
echo '<div class="accordion-body">';
echo '<div class="list-group list-group-flush" hx-get="'.$reader_uri.'?action=list_page_types" hx-trigger="load, changed, update_pages_list from:body, updated_global_filter from:body"></div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';





echo '</div>';
echo '</div>';

// end of sidebar
echo '</div>';
echo '</div>';

echo '</div>'; // max-height-container
echo '</div>'; // app-container