<?php

$writer_uri = '/admin/snippets/write/';

$q = pathinfo($_REQUEST['query']);

if(isset($_POST['snippet_id']) && is_numeric($_POST['snippet_id'])) {
    $get_id = (int) $_POST['snippet_id'];
    $form_mode = $get_id;
    $btn_submit_text = $lang['update'];
}

if(isset($_POST['duplicate_id']) && is_numeric($_POST['duplicate_id'])) {
    $get_id = (int) $_POST['duplicate_id'];
    $form_mode = 'new';
    $btn_submit_text = $lang['duplicate'];
}

if(is_int($get_id)) {

    $get_data = $db_content->get("se_snippets","*",[
        "snippet_id" => "$get_id"
    ]);

    foreach($get_data as $k => $v) {
        if($v == '') {
            continue;
        }
        $$k = htmlentities(stripslashes($v), ENT_QUOTES, "UTF-8");
    }

} else {
    $btn_submit_text = $lang['save'];
    $form_mode = 'new';
}

// build the form and it's data

$input_text_title = [
    "input_name" => "snippet_title",
    "input_value" => $snippet_title,
    "label" => $lang['label_title'],
    "type" => "text"
];

$input_text_name = [
    "input_name" => "snippet_name",
    "input_value" => $snippet_name,
    "label" => $lang['label_name'].' <sup>(a-z,0-9)</sup>',
    "type" => "text"
];

$input_wysiwyg_content = [
    "input_name" => "snippet_content",
    "input_value" => $snippet_content,
    "label" => 'editor',
    "type" => "textarea",
    "mode" => "wysiwyg"
];

$input_text_keywords = [
    "input_name" => "snippet_keywords",
    "input_value" => $snippet_keywords,
    "label" => $lang['label_keywords'],
    "type" => "text"
];

$input_text_classes = [
    "input_name" => "snippet_classes",
    "input_value" => $snippet_classes,
    "label" => $lang['label_classes'],
    "type" => "text"
];

$input_text_label = [
    "input_name" => "snippet_label",
    "input_value" => $snippet_label,
    "label" => 'Label',
    "type" => "text"
];


$get_all_languages = get_all_languages();
foreach($get_all_languages as $langs) {
    $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
}

$snippet_lang = $snippet_lang ?? $languagePack;

$input_select_language = [
    "input_name" => "snippet_lang",
    "input_value" => $snippet_lang,
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
];


$form_tpl = '<div id="formResponse" class="alert alert-info"></div>';

$form_tpl .= '<form>';

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-9">';

$form_tpl .= se_print_form_input($input_text_name);
$form_tpl .= se_print_form_input($input_text_title);
$form_tpl .= se_print_form_input($input_wysiwyg_content);

$form_tpl .= se_print_form_input($input_text_keywords);
$form_tpl .= se_print_form_input($input_text_label);
$form_tpl .= se_print_form_input($input_text_classes);


$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-3">';

$form_tpl .= se_print_form_input($input_select_language);


$form_tpl .= '<div class="card p-3">';
$form_tpl .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
$form_tpl .= '<button type="submit" hx-post="'.$writer_uri.'" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-success w-100" name="save_snippet" value="'.$form_mode.'">'.$btn_submit_text.'</button>';

if($form_mode != 'new') {
    $form_tpl .= '<button type="submit" hx-post="'.$writer_uri.'" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-danger" name="delete_delete" value="'.$get_page_id.'">'.$lang['btn_delete'].'</button>';
}
$form_tpl .= '</div>';

$form_tpl .= '</div>'; // sidebar-col
$form_tpl .= '</div>'; // row



$form_tpl .= '</form>';

echo $form_tpl;