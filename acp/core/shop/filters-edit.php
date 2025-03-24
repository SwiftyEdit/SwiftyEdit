<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['shop'].' '.$lang['nav_btn_shop'];
echo '</div>';

// edit filter group
if(isset($_POST['edit_group'])) {
    if(is_numeric($_POST['edit_group'])) {
        // edit group
        $mode = 'edit';
        $edit_group_id = (int) $_POST['edit_group'];
        $group_data = $db_content->get("se_filter","*", [
            "filter_id" => $edit_group_id
        ]);
        $submit_btn = '<button 
                hx-post="/admin/shop/write/"
                hx-swap="beforeend"
                hx-include="[name=\'csrf_token\']"
                name="save_filter_group"
                value="'.$edit_group_id.'"
                class="btn btn-success">'.$lang['btn_update'].'</button>';
        $delete_btn = '<button
                hx-post="/admin/shop/write/"
                hx-swap="beforeend"
                hx-include="[name=\'csrf_token\']"
                hx-confirm="'.$lang['msg_confirm_delete'].'"
                name="delete_filter_group"
                value="'.$edit_group_id.'"
                class="btn btn-danger">'.$icon['trash_alt'].'</button>';
    } else {
        // new group
        $mode = 'new';
        $delete_btn = '';
        $submit_btn = '<button 
                hx-post="/admin/shop/write/"
                hx-swap="beforeend"
                hx-include="[name=\'csrf_token\']"
                name="save_filter_group"
                value="new"
                class="btn btn-success">'.$lang['btn_save'].'</button>';
    }

    $input_title = [
        "input_name" => "filter_title",
        "input_value" => $group_data['filter_title'],
        "label" => $lang['label_title'],
        "type" => "text"
    ];

    $input_text = [
        "input_name" => "filter_description",
        "input_value" => $group_data['filter_description'],
        "label" => $lang['label_description'],
        "type" => "textarea",
        "mode" => "wysiwyg"
    ];

    $input_priority = [
        "input_name" => "filter_priority",
        "input_value" => $group_data['filter_priority'],
        "label" => $lang['label_priority'],
        "type" => "text"
    ];


    $get_all_languages = get_all_languages();
    foreach($get_all_languages as $langs) {
        if(!in_array($langs['lang_folder'],$lang_codes)) {
            continue;
        }
        $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
    }

    $input_select_language = [
        "input_name" => "filter_lang",
        "input_value" => $group_data['filter_lang'],
        "label" => $lang['label_language'],
        "options" => $lang_options,
        "type" => "select"
    ];

    $type_options = [
        'Radio' => '1',
        'Checkbox' => '2',
        'Range' => '3'
    ];

    $input_select_type = [
        "input_name" => "filter_input_type",
        "input_value" => $group_data['filter_input_type'],
        "label" => $lang['label_type'],
        "options" => $type_options,
        "type" => "select"
    ];

    // checkboxes for categories
    $get_categories = se_get_categories();

    $array_categories = array();
    if($group_data['filter_categories'] != '') {
        $array_categories = explode("<->", $group_data['filter_categories']);
    }

    $checkboxes_cat = '<div class="card">';
    $checkboxes_cat .= '<div class="card-header">'.$lang['label_categories'].'</div>';
    $checkboxes_cat .= '<div class="card-body">';
    $checkboxes_cat .= '<div class="scroll-container">';

    $checkboxes_cat .= '<div class="form-check">';
    $checkboxes_cat .= '<input class="form-check-input" type="checkbox" name="filter_cats[]" value="all" id="cat_id_all" '.$checked_all.'>';
    $checkboxes_cat .= '<label class="form-check-label" for="cat_id_all">'.$lang['label_all_categories'].'</label>';
    $checkboxes_cat .= '</div><hr>';

    foreach($get_categories as $cat) {

        $checked = '';
        if(in_array($cat['cat_hash'], $array_categories)) {
            $checked = "checked";
        }

        $checkboxes_cat .= '<div class="form-check">';
        $checkboxes_cat .= '<input class="form-check-input" id="'.$cat['cat_hash'].'" type="checkbox" name="filter_cats[]" value="'.$cat['cat_hash'].'" '.$checked.'>';
        $checkboxes_cat .= '<label class="form-check-label" for="'.$cat['cat_hash'].'">'.$cat['cat_name'].' <small>('.$cat['cat_lang'].')</small></label>';
        $checkboxes_cat .= '</div>';
    }
    $checkboxes_cat .= '</div>';
    $checkboxes_cat .= '</div>';
    $checkboxes_cat .= '</div>';

    echo '<form>';
    echo '<div class="card">';
    echo '<div class="card-header"><a href="/admin/shop/">'.$lang['nav_btn_shop'].'</a> / <a href="/admin/shop/filters/">'.$lang['filter'].'</a></div>';
    echo '<div class="card-body">';
    echo '<div class="row">';
    echo '<div class="col-md-9">';
    echo '<div class="row">';
    echo '<div class="col-md-8">';
    echo se_print_form_input($input_title);
    echo se_print_form_input($input_text);
    echo '</div>';
    echo '<div class="col-md-4">';
    echo se_print_form_input($input_priority);
    echo se_print_form_input($input_select_language);
    echo se_print_form_input($input_select_type);
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '<div class="col-md-3">';

    echo '<div class="ps-3 border-start">';
    echo $checkboxes_cat;
    echo '<div class="mt-3 d-flex justify-content-between">';
    echo $submit_btn;
    echo $delete_btn;
    echo '</div>';
    echo '</div>';

    echo '</div>';
    echo '</div>';

    echo '</form>';

    echo '</div>';
    echo '</div>';
}

// edit values
if(isset($_POST['edit_value'])) {
    if(is_numeric($_POST['edit_value'])) {
        // update
        $mode = 'edit';
        $edit_value_id = (int) $_POST['edit_value'];
        $value_data = $db_content->get("se_filter","*", [
            "filter_id" => $edit_value_id
        ]);
        $filter_hash = $value_data['filter_hash'];
        if($filter_hash == '') {
            $filter_hash = uniqid();
        }
        $submit_btn = '<button 
                hx-post="/admin/shop/write/"
                hx-swap="beforeend"
                hx-include="[name=\'csrf_token\']"
                name="save_filter_value"
                value="'.$edit_value_id.'"
                class="btn btn-success">'.$lang['btn_update'].'</button>';
        $delete_btn = '<button
                hx-post="/admin/shop/write/"
                hx-swap="beforeend"
                hx-include="[name=\'csrf_token\']"
                hx-confirm="'.$lang['msg_confirm_delete'].'"
                name="delete_filter_value"
                value="'.$edit_value_id.'"
                class="btn btn-danger">'.$icon['trash_alt'].'</button>';
    } else {
        // new
        $mode = 'new';
        $delete_btn = '';
        $submit_btn = '<button 
                hx-post="/admin/shop/write/"
                hx-swap="beforeend"
                hx-include="[name=\'csrf_token\']"
                name="save_filter_value"
                value="new"
                class="btn btn-success">'.$lang['btn_save'].'</button>';
        if(isset($_POST['parent_id'])) {
            $value_data['filter_parent_id'] = (int) $_POST['parent_id'];
        }

        $filter_hash = uniqid();

    }

    $input_title = [
        "input_name" => "filter_title",
        "input_value" => $value_data['filter_title'],
        "label" => $lang['label_title'],
        "type" => "text"
    ];

    $input_text = [
        "input_name" => "filter_description",
        "input_value" => $value_data['filter_description'],
        "label" => $lang['label_description'],
        "type" => "textarea",
        "mode" => "wysiwyg"
    ];

    $input_priority = [
        "input_name" => "filter_priority",
        "input_value" => $value_data['filter_priority'],
        "label" => $lang['label_priority'],
        "type" => "text"
    ];

    // select for group
    $all_filters = se_get_product_filter_groups('all');
    foreach($all_filters as $filters) {
        $key = '['.$filters['filter_priority'].'] '.$filters['filter_title'];
        $set_options["$key"] = $filters['filter_id'];
    }

    $input_select_groups = [
        "input_name" => "filter_parent_id",
        "input_value" => $value_data['filter_parent_id'],
        "label" => $lang['label_group'],
        "options" => $set_options,
        "type" => "select"
    ];

    echo '<form>';
    echo '<div class="card">';
    echo '<div class="card-header"><a href="/admin/shop/">'.$lang['nav_btn_shop'].'</a> / <a href="/admin/shop/filters/">'.$lang['filter'].'</a> / '.$lang['label_value'].'</div>';
    echo '<div class="card-body">';
    echo '<div class="row">';
    echo '<div class="col-md-9">';

    echo se_print_form_input($input_title);
    echo se_print_form_input($input_text);

    echo '</div>';
    echo '<div class="col-md-3">';

    echo '<div class="ps-3 border-start">';
    echo se_print_form_input($input_select_groups);
    echo se_print_form_input($input_priority);
    echo '<div class="mt-3 d-flex justify-content-between">';
    echo $submit_btn;
    echo $delete_btn;
    echo '<input type="hidden" name="filter_hash" value="'.$filter_hash.'">';
    echo '</div>';
    echo '</div>';

    echo '</div>';
    echo '</div>';


    echo '</form>';

    echo '</div>';
    echo '</div>';
}