<?php

$writer_uri = '/admin/shop/write/';

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['shop'].' '.$lang['nav_btn_shop'];
echo '<a href="/admin/shop/options/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';



$mode = 'new';
$submit_btn = '<button 
                hx-post="/admin-xhr/shop/write/"
                hx-trigger="click"
                hx-swap="none"
                hx-include="[name=\'csrf_token\']"
                name="save_option"
                value="new"
                class="btn btn-success">SAVE</button>';

if(is_numeric($_POST['options-form'])) {
    $get_option_id = (int) $_POST['options-form'];
    $mode = 'edit';

    $option_data = $db_content->get("se_snippets","*",[
        "AND" => [
            "snippet_type" => "post_option",
            "snippet_id" => $get_option_id
        ]
    ]);
    $option_title = html_entity_decode($option_data['snippet_title']);
    $option_text_array = json_decode($option_data['snippet_content'],true);
    $option_priority = $option_data['snippet_priority'];
    $option_lang = $option_data['snippet_lang'];
    $submit_btn = '<button 
                hx-post="/admin-xhr/shop/write/"
                hx-trigger="click"
                hx-swap="beforeend"
                hx-include="[name=\'csrf_token\']"
                name="save_option"
                value="'.$get_option_id.'"
                class="btn btn-success">UPDATE</button>';
}


$input_title = [
    "input_name" => "option_title",
    "input_value" => $option_title,
    "label" => $lang['label_title'],
    "type" => "text"
];

$input_priority = [
    "input_name" => "option_priority",
    "input_value" => $option_priority,
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

$select_language = [
    "input_name" => "option_lang",
    "input_value" => $option_lang,
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
];

$input_option_values = [
    "input_name" => "option_text[]",
    "input_value" => '',
    "label" => $lang['label_value'],
    "type" => "text"
];


if(is_array($option_text_array)) {
    echo "ARRAY";
    $inputs_tpl = '<div class="sortableListGroup list-group mt-1">';
    foreach($option_text_array as $option_value) {
        $inputs_tpl .= '<div class="list-group-item">';
        $inputs_tpl .= '<div class="input-group">';
        $inputs_tpl .= '<span class="input-group-text" id="basic-addon1">';
        $inputs_tpl .= '<i class="bi bi-arrows-move" aria-hidden="true"></i>';
        $inputs_tpl .= '</span>';
        $inputs_tpl .= '<input type="text" name="option_text[]" value="'.$option_value.'" class="form-control">';
        $inputs_tpl .= '</div>';
        $inputs_tpl .= '</div>';
    }
    $inputs_tpl .= '</div>';
}



echo '<form>';
echo '<div class="card">';
echo '<div class="card-header">'.$mode.'</div>';
echo '<div class="card-body">';
echo '<div class="row">';
echo '<div class="col-md-8">';
echo se_print_form_input($input_title);
echo '</div>';
echo '<div class="col-md-2">';
echo se_print_form_input($input_priority);
echo '</div>';
echo '<div class="col-md-2">';
echo se_print_form_input($select_language);
echo '</div>';
echo '</div>';
echo se_print_form_input($input_option_values);
echo $inputs_tpl;
echo '</div>';
echo '<div class="card-footer">';
echo $submit_btn;
echo '</div>';
echo '</div>';
echo '</form>';