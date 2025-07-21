<?php

$writer_uri = '/admin/xhr/snippets/write/';

$q = pathinfo($_REQUEST['query']);

if(isset($_POST['snippet_id']) && is_numeric($_POST['snippet_id'])) {
    $get_id = (int) $_POST['snippet_id'];
    $form_mode = $get_id;
    $btn_submit_text = $lang['update'];
}

// open snippet from last part of $query
if(is_numeric($q['filename'])) {
    $get_id = (int) $q['filename'];
    $form_mode = $get_id;
    $btn_submit_text = $lang['update'];
}

if(isset($_POST['duplicate_id']) && is_numeric($_POST['duplicate_id'])) {
    $get_id = (int) $_POST['duplicate_id'];
    $form_mode = 'new';
    $btn_submit_text = $lang['duplicate'];
}

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['card_heading'].' '.$lang['nav_btn_snippets'];
echo '<a href="/admin/snippets/" class="btn btn-default ms-auto">'.$icon['arrow_left_short'].' '.$lang['nav_btn_overview'].'</a>';
echo '</div>';

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
    "input_value" => $get_data['snippet_title'],
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
    "input_value" => $get_data['snippet_content'],
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

$input_link = [
    "input_name" => "snippet_permalink",
    "input_value" => $snippet_permalink,
    "label" => $lang['label_url'],
    "type" => "text"
];

$input_link_name = [
    "input_name" => "snippet_permalink_name",
    "input_value" => $get_data['snippet_permalink_name'],
    "label" => $lang['label_link_name'],
    "type" => "text"
];

$input_link_title = [
    "input_name" => "snippet_permalink_title",
    "input_value" => $get_data['snippet_permalink_title'],
    "label" => $lang['label_title'],
    "type" => "text"
];

$input_link_classes = [
    "input_name" => "snippet_permalink_classes",
    "input_value" => $snippet_permalink_classes,
    "label" => $lang['label_classes'],
    "type" => "text"
];

$input_priority = [
    "input_name" => "snippet_priority",
    "input_value" => (int) $get_data['snippet_priority'],
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

$snippet_lang = $snippet_lang ?? $languagePack;

$input_select_language = [
    "input_name" => "snippet_lang",
    "input_value" => $snippet_lang,
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
];

/* image widget */
$images = se_get_all_media_data('image');
$images = se_unique_multi_array($images,'media_file');
$array_images = explode("<->", $get_data['snippet_images']);
$draggable = '';
if(is_array($array_images)) {
    $array_images = array_filter($array_images);
    foreach($array_images as $image) {
        $image_src = str_replace('../content/','/',$image); // old path from SwiftyEdit 1.x
        $image_src = str_replace('../images/','/images/',$image_src);
        $draggable .= '<div class="list-group-item draggable" data-id="'.$image.'">';
        $draggable .= '<div class="d-flex flex-row gap-2">';
        $draggable .= '<div class="rounded-circle flex-shrink-0" style="width:40px;height:40px;background-image:url('.$image_src.');background-size:cover;"></div>';
        $draggable .= '<div class="text-muted small">'.basename($image).'</div>';
        $draggable .= '</div>';
        $draggable .= '</div>';
    }
}

$choose_images = '<div id="imgdropper" class="sortable_target list-group mb-3">'.$draggable.'</div>';
$choose_images .= '<div id="imgWidget" hx-post="/admin/xhr/widgets/read/?widget=img-select" hx-include="[name=\'csrf_token\']" hx-trigger="load, update_image_widget from:body">';
$choose_images .= 'Loading Images ...</div>';

// checkboxes for labels
$arr_checked_labels = array();
if($get_data['snippet_labels'] != '') {
    $arr_checked_labels = explode(",", $get_data['snippet_labels']);
}

foreach($se_labels as $label) {
    $label_title = $label['label_title'];
    $label_id = $label['label_id'];
    $label_color = $label['label_color'];
    if(in_array("$label_id", $arr_checked_labels)) {
        $checked_label = "checked";
    } else {
        $checked_label = "";
    }
    $checkbox_set_labels .= '<div class="form-check form-check-inline" style="border-bottom: 1px solid '.$label_color.'">';
    $checkbox_set_labels .= '<input class="form-check-input" id="label'.$label_id.'" type="checkbox" '.$checked_label.' name="snippet_labels[]" value="'.$label_id.'">';
    $checkbox_set_labels .= '<label class="form-check-label" for="label'.$label_id.'">'.$label_title.'</label>';
    $checkbox_set_labels .= '</div>';
}

// select template
$arr_Styles = get_all_templates();
$select_select_template = '<select id="select_template" name="select_template"  class="custom-select form-control">';

if($snippet_template == '') {
    $selected_standard = 'selected';
}

$select_select_template .= "<option value='use_standard<|-|>use_standard' $selected_standard>$lang[label_use_default]</option>";


foreach($arr_Styles as $template) {

    if($template == 'administration') {  continue; }

    $tpl_path = "../public/assets/themes/$template/templates/snippet*.tpl";
    $arr_layout_tpl = glob("$tpl_path");
    $select_select_template .= "<optgroup label='$template'>";

    foreach($arr_layout_tpl as $layout_tpl) {
        $layout_tpl = basename($layout_tpl);
        $selected = '';
        if($template == "$snippet_theme" && $layout_tpl == "$snippet_template") {
            $selected = 'selected';
        }
        $select_select_template .=  "<option $selected value='$template<|-|>$layout_tpl'>$template » $layout_tpl</option>";
    }
    $select_select_template .= '</optgroup>';
}

$select_select_template .= '</select>';

$form_tpl = '<div id="formResponse"></div>';

$form_tpl .= '<form>';

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-9">';

$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">';

$form_tpl .= '<ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">';
$form_tpl .= '<li class="nav-item"><a class="nav-link active" href="#content" data-bs-toggle="tab">'.$lang['label_content'].'</a></li>';
$form_tpl .= '<li class="nav-item"><a class="nav-link" href="#images" data-bs-toggle="tab">'.$lang['images'].'</a></li>';
$form_tpl .= '<li class="nav-item"><a class="nav-link" href="#link" data-bs-toggle="tab">'.$lang['label_url'].'</a></li>';
$form_tpl .= '</ul>';

$form_tpl .= '</div>';
$form_tpl .= '<div class="card-body">';

$form_tpl .= '<div class="tab-content">';

$form_tpl .= '<div class="tab-pane fade show active" id="content">';

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-4">';
$form_tpl .= se_print_form_input($input_text_name);
$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-8">';
$form_tpl .= se_print_form_input($input_text_title);
$form_tpl .= '</div>';
$form_tpl .= '</div>';
$form_tpl .= se_print_form_input($input_wysiwyg_content);

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-4">'.se_print_form_input($input_text_keywords).'</div>';
$form_tpl .= '<div class="col-md-4">'.se_print_form_input($input_text_label).'</div>';
$form_tpl .= '<div class="col-md-4">'.se_print_form_input($input_text_classes).'</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '<div class="tab-pane fade" id="images">';
$form_tpl .= $choose_images;
$form_tpl .= '</div>'; // images
$form_tpl .= '<div class="tab-pane fade" id="link">';
$form_tpl .= se_print_form_input($input_link);
$form_tpl .= se_print_form_input($input_link_name);
$form_tpl .= se_print_form_input($input_link_title);
$form_tpl .= se_print_form_input($input_link_classes);
$form_tpl .= '</div>'; // links
$form_tpl .= '</div>';
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-3">';


$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">'.$lang['label_settings'].'</div>';
$form_tpl .= '<div class="card-body">';

$form_tpl .= se_print_form_input($input_select_language);
$form_tpl .= se_print_form_input($input_priority);


$form_tpl .= '<div class="mb-3">';
$form_tpl .= '<label class="form-label">'.$lang['label_template'].'</label>';
$form_tpl .= $select_select_template;
$form_tpl .= '</div>';

$form_tpl .= '<div class="mb-3">';
$form_tpl .= '<label class="d-block">'.$lang['labels'].'</label>';
$form_tpl .= $checkbox_set_labels;
$form_tpl .= '</div>';


$form_tpl .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

$form_tpl .= '<div class="d-flex justify-content">';
$form_tpl .= '<button type="submit" hx-post="'.$writer_uri.'" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-success w-100" name="save_snippet" value="'.$form_mode.'">'.$btn_submit_text.'</button>';

if($form_mode != 'new') {
    $form_tpl .= '<button type="submit" hx-post="'.$writer_uri.'" hx-confirm="'.$lang['msg_confirm_delete'].'" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-danger ms-1" name="delete_snippet" value="'.$get_id.'">'.$lang['btn_delete'].'</button>';
}
$form_tpl .= '</div>';
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>'; // sidebar-col
$form_tpl .= '</div>'; // row



$form_tpl .= '</form>';

echo $form_tpl;