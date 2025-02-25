<?php

$writer_uri = '/admin/blog/write/';
$reader_uri = '/admin/blog/read/';

if(is_numeric($_POST['post_id'])) {
    $post_id = (int) $_POST['post_id'];
    $mode = 'update';
    $post_data = se_get_post_data($post_id);
} else {
    $mode = 'new';
}

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['files'].' '.$lang['nav_btn_blog'];
echo '<div class="d-flex ms-auto">'.$mode.'</div>';
echo '</div>';

if($mode == 'new') {
    echo '<div class="card mb-3">';
    echo '<div class="card-header">' . $lang['label_select_post_type'] . '</div>';
    echo '<div class="btn-group d-flex" role="group">';
    echo '<button hx-post="' . $writer_uri . '" name="set_post_type" value="m" hx-include="[name=\'csrf_token\']"  class="btn btn-default w-100"><span class="color-message">' . $icon['plus'] . '</span> ' . $lang['post_type_message'] . '</button>';
    echo '<button hx-post="' . $writer_uri . '" name="set_post_type" value="i" hx-include="[name=\'csrf_token\']"  class="btn btn-default w-100"><span class="color-image">' . $icon['plus'] . '</span> ' . $lang['post_type_image'] . '</button>';
    echo '<button hx-post="' . $writer_uri . '" name="set_post_type" value="g" hx-include="[name=\'csrf_token\']"  class="btn btn-default w-100"><span class="color-gallery">' . $icon['plus'] . '</span> ' . $lang['post_type_gallery'] . '</button>';
    echo '<button hx-post="' . $writer_uri . '" name="set_post_type" value="v" hx-include="[name=\'csrf_token\']"  class="btn btn-default w-100"><span class="color-video">' . $icon['plus'] . '</span> ' . $lang['post_type_video'] . '</button>';
    echo '<button hx-post="' . $writer_uri . '" name="set_post_type" value="l" hx-include="[name=\'csrf_token\']"  class="btn btn-default w-100"><span class="color-link">' . $icon['plus'] . '</span> ' . $lang['post_type_link'] . '</button>';
    echo '<button hx-post="' . $writer_uri . '" name="set_post_type" value="f" hx-include="[name=\'csrf_token\']"  class="btn btn-default w-100"><span class="color-file">' . $icon['plus'] . '</span> ' . $lang['post_type_file'] . '</button>';
    echo '</div>';
    echo '</div>';
}

// define which template is used
$form_array = [
    'm' => '../acp/templates/post_message.tpl',
    "i" => '../acp/templates/post_image.tpl',
    'g' => '../acp/templates/post_gallery.tpl',
    'v' => '../acp/templates/post_video.tpl',
    'l' => '../acp/templates/post_link.tpl',
    'f' => '../acp/templates/post_file.tpl'
];

// load the template
$post_type_form = $_SESSION['post_type_form'] ?? 'm';
$form_tpl = file_get_contents($form_array[$post_type_form]);
// inject the sidebar - it is in all templates the same
$form_tpl_sidebar = file_get_contents('../acp/templates/post_options.tpl');
$form_tpl = str_replace('{sidebar}', $form_tpl_sidebar, $form_tpl);

// prepare form data and inputs

$input_post_teaser = [
    "input_name" => "post_teaser",
    "input_value" => $post_data['post_teaser'],
    "label" => ' ',
    "type" => "textarea",
    "mode" => "wysiwyg"
];

$input_post_text = [
    "input_name" => "post_text",
    "input_value" => $post_data['post_text'],
    "label" => ' ',
    "type" => "textarea",
    "mode" => "wysiwyg"
];

// select language
$get_all_languages = get_all_languages();
foreach($get_all_languages as $langs) {
    $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
}

$post_lang = $post_data['post_lang'] ?? $languagePack;

$input_select_language = [
    "input_name" => "post_lang",
    "input_value" => $post_lang,
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
];

// checkboxes for categories
$get_categories = se_get_categories();

$array_categories = array();
if($post_data['post_categories'] != '') {
    $array_categories = explode("<->", $post_data['post_categories']);
}

foreach($get_categories as $cat) {

    $checked = '';
    if(in_array($cat['cat_hash'], $array_categories)) {
        $checked = "checked";
    }

    $checkboxes_cat .= '<div class="form-check">';
    $checkboxes_cat .= '<input class="form-check-input" id="'.$cat['cat_hash'].'" type="checkbox" name="post_categories[]" value="'.$cat['cat_hash'].'" '.$checked.'>';
    $checkboxes_cat .= '<label class="form-check-label" for="'.$cat['cat_hash'].'">'.$cat['cat_name'].' <small>('.$cat['cat_lang'].')</small></label>';
    $checkboxes_cat .= '</div>';
}

// checkbox for fixed posts
if($post_data['post_fixed'] == '1') {
    $checked_fixed = 'checked';
}
$checkbox_fixed  = '<div class="form-check">';
$checkbox_fixed .= '<input class="form-check-input" id="fix" type="checkbox" name="post_fixed" value="fixed" '.$checked_fixed.'>';
$checkbox_fixed .= '<label class="form-check-label" for="fix">'.$lang['label_fixed'].'</label>';
$checkbox_fixed .= '</div>';


// select post status
$status_types = [
    $lang['status_public'] => '1',
    $lang['status_draft'] => '2'
];

$post_status = $post_data['post_status'] ?? '2';

$input_select_status = [
    "input_name" => "post_status",
    "input_value" => $post_status,
    "label" => $lang['label_status'],
    "options" => $status_types,
    "type" => "select"
];

// select for comments
$comment_types = [
    $lang['yes'] => '1',
    $lang['no'] => '2'
];

$post_comments = $post_data['post_comments'] ?? '2';

$input_select_comments = [
    "input_name" => "post_comments",
    "input_value" => $post_comments,
    "label" => $lang['label_comments'],
    "options" => $comment_types,
    "type" => "select"
];

// select for reactions/votings
$reaction_types = [
    $lang['label_votings_status_off'] => '1',
    $lang['label_votings_status_registered'] => '2',
    $lang['label_votings_status_global'] => '3'
];

$post_reactions = $post_data['post_votings'] ?? $se_settings['posts_default_votings'];

$select_reactions = [
    "input_name" => "post_votings",
    "input_value" => $post_reactions,
    "label" => $lang['label_votings'],
    "options" => $reaction_types,
    "type" => "select"
];

// checkboxes for labels
$arr_checked_labels = array();
if($post_data['post_labels'] != '') {
    $arr_checked_labels = explode(",", $post_data['post_labels']);
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
    $checkbox_set_labels .= '<input class="form-check-input" id="label'.$label_id.'" type="checkbox" '.$checked_label.' name="post_labels[]" value="'.$label_id.'">';
    $checkbox_set_labels .= '<label class="form-check-label" for="label'.$label_id.'">'.$label_title.'</label>';
    $checkbox_set_labels .= '</div>';
}

// select for RSS
$rss_options = [
    $lang['yes'] => 'on',
    $lang['no'] => 'off'
];

$post_rss = $post_data['post_rss'] ?? 'off';

$select_rss = [
    "input_name" => "post_rss",
    "input_value" => $post_rss,
    "label" => $lang['label_rss'],
    "options" => $rss_options,
    "type" => "select"
];


$input_teaser = se_print_form_input($input_post_teaser);
$input_text = se_print_form_input($input_post_text);
$input_select_lang = se_print_form_input($input_select_language);
$input_select_status = se_print_form_input($input_select_status);
$input_select_comments = se_print_form_input($input_select_comments);
$input_select_reactions = se_print_form_input($select_reactions);
$input_select_rss = se_print_form_input($select_rss);


// select for files
$select_file = '<select class="form-control custom-select" name="post_file_attachment">';
$select_file .= '<option value="">-- '.$lang['label_select_no_file'].' --</option>';
$files_directory = SE_PUBLIC.'/assets/files';
$all_files = se_scandir_rec($files_directory);

foreach($all_files as $file) {
    //$se_upload_file_types is set in config.php
    $file_info = pathinfo($file);
    if(in_array($file_info['extension'],$se_upload_file_types)) {

        $short_path = str_replace("$files_directory","",$file);

        $selected = "";
        if($post_data['post_file_attachment'] == $short_path) {
            $selected = 'selected';
        }

        $select_file .= '<option '.$selected.' value='.$short_path.'>'.$short_path.'</option>';
    }
}
$select_file .= '</select>';
$form_tpl = str_replace('{select_file}', $select_file, $form_tpl);


if($mode == 'new') {
    $submit_btn = '<button type="submit" hx-post="/admin/blog/write/" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-success w-100" name="save_post" value="save">'.$lang['save'].'</button';
} else {
    $submit_btn = '<button type="submit" hx-post="/admin/blog/write/" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-success w-100" name="save_post" value="update">'.$lang['update'].'</button';
}

// replace all entries from $lang
foreach($lang as $k => $v) {
    $form_tpl = str_replace('{'.$k.'}', $lang[$k], $form_tpl);
}

// replace form data
$form_tpl = str_replace('{post_title}', $post_data['post_title'], $form_tpl);
$form_tpl = str_replace('{input_teaser}', $input_teaser, $form_tpl);
$form_tpl = str_replace('{input_text}', $input_text, $form_tpl);
$form_tpl = str_replace('{select_language}', $input_select_lang, $form_tpl);
$form_tpl = str_replace('{post_teaser}', $post_data['post_teaser'], $form_tpl);
$form_tpl = str_replace('{post_text}', $post_data['post_text'], $form_tpl);
$form_tpl = str_replace('{post_author}', $post_data['post_author'], $form_tpl);
$form_tpl = str_replace('{post_source}', $post_data['post_source'], $form_tpl);
$form_tpl = str_replace('{post_slug}', $post_data['post_slug'], $form_tpl);
$form_tpl = str_replace('{post_tags}', $post_data['post_tags'], $form_tpl);
$form_tpl = str_replace('{post_rss_url}', $post_data['post_rss_url'], $form_tpl);
$form_tpl = str_replace('{post_meta_title}', $post_data['post_meta_title'], $form_tpl);
$form_tpl = str_replace('{post_meta_description}', $post_data['post_meta_description'], $form_tpl);
$form_tpl = str_replace('{post_priority}', $post_data['post_priority'], $form_tpl);
$form_tpl = str_replace('{post_video_url}', $post_data['post_video_url'], $form_tpl);
$form_tpl = str_replace('{post_link}', $post_data['post_link'], $form_tpl);
$form_tpl = str_replace('{post_link_text}', $post_data['post_link_text'], $form_tpl);
$form_tpl = str_replace('{post_file_attachment_external}', $post_data['post_file_attachment_external'], $form_tpl);
$form_tpl = str_replace('{post_file_license}', $post_data['post_file_license'], $form_tpl);
$form_tpl = str_replace('{post_file_version}', $post_data['post_file_version'], $form_tpl);
$form_tpl = str_replace('{checkbox_categories}', $checkboxes_cat, $form_tpl);
$form_tpl = str_replace('{checkbox_fixed}', $checkbox_fixed, $form_tpl);
$form_tpl = str_replace('{select_status}', $input_select_status, $form_tpl);
$form_tpl = str_replace('{select_comments}', $input_select_comments, $form_tpl);
$form_tpl = str_replace('{select_votings}', $input_select_reactions, $form_tpl);
$form_tpl = str_replace('{select_rss}', $input_select_rss, $form_tpl);
$form_tpl = str_replace('{post_labels}', $checkbox_set_labels, $form_tpl);



/* image widget */
$images = se_get_all_media_data('image');
$images = se_unique_multi_array($images,'media_file');
$array_images = explode("<->", $post_data['post_images']);
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
$choose_images .= '<div id="imgWidget" hx-post="/admin/widgets/read/?widget=img-select" hx-include="[name=\'csrf_token\']" hx-trigger="load, update_image_widget from:body">';
$choose_images .= 'Loading Images ...</div>';

$form_tpl = str_replace('{post_type}', $post_type_form, $form_tpl);
$form_tpl = str_replace('{post_id}', $post_data['post_id'], $form_tpl);
$form_tpl = str_replace('{post_date}', $post_data['post_date'], $form_tpl);
$form_tpl = str_replace('{post_year}', date('Y',$post_data['post_date']), $form_tpl);

$form_tpl = str_replace('{submit_button}', $submit_btn, $form_tpl);
$form_tpl = str_replace('{widget_images}', $choose_images, $form_tpl);
$form_tpl = str_replace('{token}', $_SESSION['token'], $form_tpl);
echo '<div id="formResponse"></div>';
echo $form_tpl;