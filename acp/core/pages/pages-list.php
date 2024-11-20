<?php

//error_reporting(E_ALL ^E_WARNING ^E_NOTICE ^E_DEPRECATED);

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['file'].' '.$lang['nav_btn_pages'];
echo '<a href="/admin/pages/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

$reader_uri = '/admin/pages/read/';
$writer_uri = '/admin/pages/write/';


echo '<div class="row">';

echo '<div class="col-md-5">';

echo '<div class="card">';
echo '<div class="card-header">Sorted</div>';
echo '<div class="card-body">';
echo '<div id="getPagesSorted" class="" hx-post="'.$reader_uri.'?action=list_pages_sorted" hx-trigger="load, changed, update_pages_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="col-md-4">';

echo '<div class="card">';
echo '<div class="card-header">Single</div>';
echo '<div class="card-body">';

echo '<div id="getPagesSingle" class="" hx-post="'.$reader_uri.'?action=list_pages_single" hx-trigger="load, changed, update_pages_list from:body, updated_global_filter from:body" hx-include="[name=\'csrf_token\']">';
echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="col-md-3">';
// sidebar

echo '<div class="card">';
echo '<div class="card-header">FILTER</div>';
echo '<div class="card-body">';

echo $_SESSION['pages_text_filter'];

echo '<form hx-post="'.$writer_uri.'" hx-swap="none" method="POST" class="mt-1">';
echo '<div class="input-group">';
echo '<span class="input-group-text">'.$icon['search'].'</span>';
echo '<input class="form-control" type="text" name="pages_text_filter" value="" placeholder="'.$lang['search'].'">';
echo $hidden_csrf_token;
echo '</div>';
echo '</form>';

if(isset($_SESSION['pages_text_filter']) AND $_SESSION['pages_text_filter'] != "") {
    unset($all_filter);
    $all_filter = explode(" ", $_SESSION['pages_text_filter']);

    foreach($all_filter as $f) {
        if($_REQUEST['rm_keyword'] == "$f") { continue; }
        if($f == "") { continue; }
        $btn_remove_keyword .= '<button class="btn btn-sm btn-default" name="rmkey" value="'.$f.'" hx-post="'.$writer_uri.'" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button> ';
    }
}

if(isset($btn_remove_keyword)) {
    echo '<div class="d-inline">';
    echo '<p style="padding-top:5px;">' . $btn_remove_keyword . '</p>';
    echo '</div><hr>';
}

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
echo se_print_form_input($input_select_page_status);

echo '<div class="btn-group d-flex">';
echo '<button name="sorting_single_pages_asc" value="asc" title="'.$lang['btn_sort_asc'].'" class="btn btn-sm btn-default w-100 '.$sel_value['sort_asc'].'">'.$icon['arrow_up'].'</button> ';
echo '<button name="sorting_single_pages_desc" value="desc" title="'.$lang['btn_sort_desc'].'" class="btn btn-sm btn-default w-100 '.$sel_value['sort_desc'].'">'.$icon['arrow_down'].'</button>';
echo '</div>';
echo $hidden_csrf_token;
echo '</form>';

echo '</div>';
echo '</div>';

// end of sidebar
echo '</div>';
echo '</div>';