<?php

/**
 * global variables
 * @var array $lang
 * @var array $icon
 * @var object $db_content
 * @var array $se_settings
 * @var array $lang_codes
 * @var string $hidden_csrf_token
 */

$writer_uri = '/admin-xhr/categories/write/';
$q = pathinfo($_REQUEST['query']);

if(isset($_POST['category_id']) && is_numeric($_POST['category_id'])) {
    $get_id = (int) $_POST['category_id'];
    $form_mode = $get_id;
    $btn_submit_text = $lang['update'];
}

// open category from last part of $query
if(is_numeric($q['filename'])) {
    $get_id = (int) $q['filename'];
    $form_mode = $get_id;
    $btn_submit_text = $lang['update'];
}

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['bookmarks_fill'].' '.$lang['categories'];
echo '<a href="/admin/categories/" class="btn btn-default ms-auto">'.$icon['arrow_left_short'].' '.$lang['nav_btn_overview'].'</a>';
echo '</div>';

if(is_int($get_id)) {
    $btn_submit_text = $lang['update'];
    $form_mode = $get_id;
    $get_category = $db_content->get("se_categories","*",[
        "cat_id" => "$get_id"
    ]);

    $cat_name = $get_category['cat_name'];
    $cat_lang = $get_category['cat_lang'];
    $cat_name_clean = $get_category['cat_name_clean'];
    $cat_sort = $get_category['cat_sort'];
    $cat_thumbnail = $get_category['cat_thumbnail'];
    $cat_description = $get_category['cat_description'];
    $cat_text = $get_category['cat_text'];
    $cat_hash = $get_category['cat_hash'];

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

$input_slug = [
    "input_name" => "cat_name_clean",
    "input_value" => $cat_name_clean,
    "label" => $lang['label_slug'],
    "type" => "text"
];

$input_text_description = [
    "input_name" => "cat_description",
    "input_value" => $cat_description,
    "label" => $lang['label_description'],
    "type" => "textarea"
];

$input_wysiwyg = [
    "input_name" => "cat_text",
    "input_value" => $cat_text,
    "label" => ' ',
    "type" => "textarea",
    "mode" => "wysiwyg"
];

$input_text_priority = [
    "input_name" => "cat_sort",
    "input_value" => $cat_sort,
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
    "input_name" => "cat_lang",
    "input_value" => $cat_lang,
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
];

/* image widget */
$images = se_get_all_media_data('image');
$images = se_unique_multi_array($images,'media_file');
$array_images = explode("<->", $cat_thumbnail);
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
$choose_images .= '<div id="imgWidget" hx-post="/admin-xhr/widgets/read/?widget=img-select" hx-include="[name=\'csrf_token\']" hx-trigger="load, update_image_widget from:body">';
$choose_images .= 'Loading Images ...</div>';


$form_tpl = '<div id="formResponse"></div>';

$form_tpl .= '<form>';


$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-9">';

$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">';

$form_tpl .= '<ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">';
$form_tpl .= '<li class="nav-item"><a class="nav-link active" href="#info" data-bs-toggle="tab">'.$lang['nav_btn_info'].'</a></li>';
$form_tpl .= '<li class="nav-item"><a class="nav-link" href="#content" data-bs-toggle="tab">'.$lang['label_content'].'</a></li>';
$form_tpl .= '</ul>';

$form_tpl .= '</div>';
$form_tpl .= '<div class="card-body">';

$form_tpl .= '<div class="tab-content">';

$form_tpl .= '<div class="tab-pane fade show active" id="info">';

$form_tpl .= '<div class="row">';
$form_tpl .= '<div class="col-md-6">';

$form_tpl .= se_print_form_input($input_text_title);
$form_tpl .= se_print_form_input($input_text_description);
$form_tpl .= se_print_form_input($input_slug);

$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-6">';
$form_tpl .= $choose_images;
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '<div class="tab-pane fade" id="content">';

$form_tpl .= se_print_form_input($input_wysiwyg);

$form_tpl .= '</div>';
$form_tpl .= '</div>';
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>';
$form_tpl .= '<div class="col-md-3">';


$form_tpl .= '<div class="card">';
$form_tpl .= '<div class="card-header">'.$lang['label_settings'].'</div>';
$form_tpl .= '<div class="card-body">';

$form_tpl .= '<div class="mb-1">'.se_print_form_input($input_text_priority).'</div>';
$form_tpl .= '<div class="mb-1">'.se_print_form_input($input_select_language).'</div>';

if($cat_hash != '') {
    $form_tpl .= '<input type="hidden" name="cat_hash" value="'.$cat_hash.'">';
}

$form_tpl .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
$form_tpl .= '<button type="submit" hx-post="'.$writer_uri.'" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-success w-100" name="save_category" value="'.$form_mode.'">'.$btn_submit_text.'</button>';

$form_tpl .= '</div>';
$form_tpl .= '</div>';
$form_tpl .= '</div>';
$form_tpl .= '</div>';

$form_tpl .= '</div>'; // sidebar-col
$form_tpl .= '</div>'; // row

$form_tpl .= '</form>';

echo $form_tpl;
