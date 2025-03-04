<?php

$writer_uri = '/admin/categories/write/';
include '../acp/core/templates.php';

if(is_int($get_cat_id)) {
    $btn_submit_text = $lang['update'];
    $form_mode = $get_cat_id;
    $get_category = $db_content->get("se_categories","*",[
        "cat_id" => "$get_cat_id"
    ]);

    $cat_name = $get_category['cat_name'];
    $cat_sort = $get_category['cat_sort'];
    $cat_thumbnail = $get_category['cat_thumbnail'];
    $cat_description = $get_category['cat_description'];

} else {
    $btn_submit_text = $lang['save'];
    $form_mode = 'new';
}

if($cat_lang == '') {
    $cat_lang = $se_settings['default_language'];
}


$input_text_title = [
    "input_name" => "cat_name",
    "input_value" => $cat_name,
    "label" => $lang['label_title'],
    "type" => "text"
];

$input_text_description = [
    "input_name" => "cat_description",
    "input_value" => $cat_description,
    "label" => $lang['label_description'],
    "type" => "textarea"
];

$input_text_priority = [
    "input_name" => "cat_sort",
    "input_value" => $cat_sort,
    "label" => $lang['label_priority'],
    "type" => "text"
];

$get_all_languages = get_all_languages();
foreach($get_all_languages as $langs) {
    $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
}

$input_select_language = [
    "input_name" => "cat_lang",
    "input_value" => $cat_lang,
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
];

$arr_Images = se_get_all_images_rec();

$select_images = [];
foreach ($arr_Images as $k => $v) {
    $select_images[basename($v)] = $v;
}

$select_nothing = ['label_no_file_selected' => "null"];
$select_images = $select_nothing+$select_images;

$input_select_thumbnail = [
    "input_name" => "cat_thumbnail",
    "input_value" => $cat_thumbnail,
    "label" => $lang['thumbnail'],
    "options" => $select_images,
    "type" => "select"
];

$form_tpl = '<form>';

$form_tpl .= se_print_form_input($input_text_title);
$form_tpl .= se_print_form_input($input_text_priority);
$form_tpl .= se_print_form_input($input_select_language);
$form_tpl .= se_print_form_input($input_select_thumbnail);
$form_tpl .= se_print_form_input($input_text_description);

$form_tpl .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
$form_tpl .= '<button type="submit" hx-post="'.$writer_uri.'" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-primary" name="save_category" value="'.$form_mode.'">'.$btn_submit_text.'</button>';
$form_tpl .= '</form>';

echo $form_tpl;