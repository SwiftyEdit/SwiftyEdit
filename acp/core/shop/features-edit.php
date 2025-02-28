<?php

$writer_uri = '/admin/shop/write/';

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['shop'].' '.$lang['nav_btn_shop'];
echo '<a href="/admin/shop/features/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';



$mode = 'new';
$submit_btn = '<button 
                hx-post="/admin/shop/write/"
                hx-swap="none"
                hx-include="[name=\'csrf_token\']"
                name="save_feature"
                value="new"
                class="btn btn-success">SAVE</button>';

if(is_numeric($_POST['features-form'])) {
    $get_feature_id = (int) $_POST['features-form'];
    $mode = 'edit';

    $feature_data = $db_content->get("se_snippets","*",[
        "AND" => [
            "snippet_type" => "post_feature",
            "snippet_id" => $get_feature_id
        ]
    ]);
    $feature_title = html_entity_decode($feature_data['snippet_title']);
    $feature_text = $feature_data['snippet_content'];
    $feature_priority = $feature_data['snippet_priority'];
    $feature_lang = $feature_data['snippet_lang'];
    $submit_btn = '<button 
                hx-post="/admin/shop/write/"
                hx-swap="beforeend"
                hx-include="[name=\'csrf_token\']"
                name="save_feature"
                value="'.$get_feature_id.'"
                class="btn btn-success">UPDATE</button>';
}


$input_title = [
    "input_name" => "feature_title",
    "input_value" => $feature_title,
    "label" => $lang['label_title'],
    "type" => "text"
];

$input_priority = [
    "input_name" => "feature_priority",
    "input_value" => $feature_priority,
    "label" => $lang['label_priority'],
    "type" => "text"
];

$get_all_languages = get_all_languages();
foreach($get_all_languages as $langs) {
    $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
}

$select_language = [
    "input_name" => "feature_lang",
    "input_value" => $feature_lang,
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
];

$input_feature_content = [
    "input_name" => "feature_text",
    "input_value" => $feature_text,
    "label" => ' ',
    "type" => "textarea",
    "mode" => "wysiwyg"
];

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
echo se_print_form_input($input_feature_content);
echo '</div>';
echo '<div class="card-footer">';
echo $submit_btn;
echo '</div>';
echo '</div>';
echo '</form>';