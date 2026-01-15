<?php

$writer_uri = '/admin-xhr/events/write/';
$reader_uri = '/admin-xhr/events/read/';

if(is_numeric($_POST['id'])) {
    $id = (int) $_POST['id'];
    $mode = 'update';
    $post_data = se_get_event_data($id);
} else {
    $mode = 'new';
}


$input_post_teaser = [
    "input_name" => "teaser",
    "input_value" => $post_data['teaser'],
    "label" => ' ',
    "type" => "textarea",
    "mode" => "wysiwyg"
];

$input_post_text = [
    "input_name" => "text",
    "input_value" => $post_data['text'],
    "label" => ' ',
    "type" => "textarea",
    "mode" => "wysiwyg"
];

$input_event_price_note = [
    "input_name" => "event_price_note",
    "input_value" => $post_data['event_price_note'],
    "label" => ' ',
    "type" => "textarea",
    "mode" => "wysiwyg"
];

// select language
$get_all_languages = get_all_languages();
foreach($get_all_languages as $langs) {
    if(!in_array($langs['lang_folder'],$lang_codes)) {
        continue;
    }
    $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
}

$post_lang = $post_data['post_lang'] ?? $languagePack;

$input_select_language = [
    "input_name" => "event_lang",
    "input_value" => $post_lang,
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
];

// checkboxes for categories
$get_categories = se_get_categories();

$array_categories = array();
if($post_data['categories'] != '') {
    $array_categories = explode("<->", $post_data['categories']);
}

$checkboxes_cat = '<div class="list-group">';
foreach($get_categories as $cat) {

    $checked = '';
    if(in_array($cat['cat_hash'], $array_categories)) {
        $checked = "checked";
    }

    $category = $cat['cat_name'];
    $lang_flag = '<img src="'.return_language_flag_src($cat['cat_lang']).'" width="16">';
    $description = trim(first_words($cat['cat_description'] ?? '', 10)) ?: '...';
    $cat_id = (int)$cat['cat_id'];

    $checkboxes_cat .= '<label class="list-group-item d-flex gap-3">';
    $checkboxes_cat .= '<input class="form-check-input flex-shrink-0" type="checkbox" id="cat'.$cat_id.'" name="categories[]" value="'.$cat['cat_hash'].'" '.$checked.'>';
    $checkboxes_cat .= '<span class="pt-1 form-checked-content">';
    $checkboxes_cat .= '<strong>#'.$cat_id.' '.$category.'</strong><small class="d-block opacity-75">'.$lang_flag.' '.$description.'</small>';
    $checkboxes_cat .= '</span>';
    $checkboxes_cat .= '</label>';
}
$checkboxes_cat .= '</div>';

// checkbox for fixed posts
if($post_data['fixed'] == '1') {
    $checked_fixed = 'checked';
}
$checkbox_fixed  = '<div class="form-check">';
$checkbox_fixed .= '<input class="form-check-input" id="fix" type="checkbox" name="fixed" value="fixed" '.$checked_fixed.'>';
$checkbox_fixed .= '<label class="form-check-label" for="fix">'.$lang['label_fixed'].'</label>';
$checkbox_fixed .= '</div>';

// select post status
$status_types = [
    $lang['status_public'] => '1',
    $lang['status_draft'] => '2'
];

$post_status = $post_data['status'] ?? '2';

$input_select_status = [
    "input_name" => "status",
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

$post_comments = $post_data['comments'] ?? '2';

$input_select_comments = [
    "input_name" => "comments",
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
    "input_name" => "votings",
    "input_value" => $post_reactions,
    "label" => $lang['label_votings'],
    "options" => $reaction_types,
    "type" => "select"
];

// checkboxes for labels
$arr_checked_labels = array();
if($post_data['labels'] != '') {
    $arr_checked_labels = explode(",", $post_data['labels']);
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
    $checkbox_set_labels .= '<input class="form-check-input" id="label'.$label_id.'" type="checkbox" '.$checked_label.' name="labels[]" value="'.$label_id.'">';
    $checkbox_set_labels .= '<label class="form-check-label" for="label'.$label_id.'">'.$label_title.'</label>';
    $checkbox_set_labels .= '</div>';
}

// select for RSS
$rss_options = [
    $lang['yes'] => 'on',
    $lang['no'] => 'off'
];

$post_rss = $post_data['rss'] ?? 'off';

$select_rss = [
    "input_name" => "rss",
    "input_value" => $post_rss,
    "label" => $lang['label_rss'],
    "options" => $rss_options,
    "type" => "select"
];

// select guestlist mode
$gl_options = [
    $lang['label_guestlist_status_off'] => '1',
    $lang['label_guestlist_status_registered'] => '2',
    $lang['label_guestlist_status_global'] => '3'
];

if($post_data['event_guestlist'] == '') {
    $post_data['event_guestlist'] = $se_settings['posts_default_guestlist'];
}

$select_guestlist_mode = [
    "input_name" => "event_guestlist",
    "input_value" => $post_data['event_guestlist'],
    "label" => '',
    "options" => $gl_options,
    "type" => "select"
];

$input_teaser = se_print_form_input($input_post_teaser);
$input_text = se_print_form_input($input_post_text);
$input_price_note = se_print_form_input($input_event_price_note);
$input_select_lang = se_print_form_input($input_select_language);
$input_select_status = se_print_form_input($input_select_status);
$input_select_comments = se_print_form_input($input_select_comments);
$input_select_reactions = se_print_form_input($select_reactions);
$input_select_guestlist = se_print_form_input($select_guestlist_mode);
$input_select_rss = se_print_form_input($select_rss);

if($mode == 'new') {
    $submit_btn = '<button type="submit" hx-post="/admin-xhr/events/write/" hx-trigger="click" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-success w-100" name="save_post" value="save">'.$lang['save'].'</button';
} else {
    $submit_btn = '<button type="submit" hx-post="/admin-xhr/events/write/" hx-trigger="click" hx-target="#formResponse" hx-swap="innerHTML" class="btn btn-success w-100" name="save_post" value="update">'.$lang['update'].'</button';
}

/* image widget */
$images = se_get_all_media_data('image');
$images = se_unique_multi_array($images,'media_file');
$array_images = explode("<->", $post_data['images']);
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


/* release date */
if($post_data['releasedate'] > 0) {
    $releasedate = date('Y-m-d H:i', $post_data['releasedate']);
} else {
    $releasedate = date('Y-m-d H:i', time());
}

/* event dates */
if($post_data['event_startdate'] > 0) {
    $event_startdate = date('Y-m-d H:i', $post_data['event_startdate']);
} else {
    $event_startdate = date('Y-m-d H:i', time());
}

if($post_data['event_enddate'] > 0) {
    $event_enddate = date('Y-m-d H:i', $post_data['event_enddate']);
} else {
    $event_enddate = date('Y-m-d H:i', time());
}


$form_tpl = file_get_contents('../acp/templates/post_event.tpl');

// replace all entries from $lang
foreach($lang as $k => $v) {
    $form_tpl = str_replace('{'.$k.'}', $lang[$k], $form_tpl);
}

// replace form data
$form_tpl = str_replace('{title}', $post_data['title'], $form_tpl);
$form_tpl = str_replace('{input_teaser}', $input_teaser, $form_tpl);
$form_tpl = str_replace('{input_text}', $input_text, $form_tpl);
$form_tpl = str_replace('{input_price_note}', $input_price_note, $form_tpl);
$form_tpl = str_replace('{event_street}', $post_data['event_street'], $form_tpl);
$form_tpl = str_replace('{event_street_nbr}', $post_data['event_street_nbr'], $form_tpl);
$form_tpl = str_replace('{event_zip}', $post_data['event_zip'], $form_tpl);
$form_tpl = str_replace('{event_city}', $post_data['event_city'], $form_tpl);
$form_tpl = str_replace('{event_guestlist_limit}', $post_data['event_guestlist_limit'], $form_tpl);
$form_tpl = str_replace('{event_start}', $event_startdate, $form_tpl);
$form_tpl = str_replace('{event_end}', $event_enddate, $form_tpl);
$form_tpl = str_replace('{select_language}', $input_select_lang, $form_tpl);
$form_tpl = str_replace('{author}', $post_data['author'], $form_tpl);
$form_tpl = str_replace('{slug}', $post_data['slug'], $form_tpl);
$form_tpl = str_replace('{tags}', $post_data['tags'], $form_tpl);
$form_tpl = str_replace('{rss_url}', $post_data['rss_url'], $form_tpl);
$form_tpl = str_replace('{meta_title}', $post_data['meta_title'], $form_tpl);
$form_tpl = str_replace('{meta_description}', $post_data['meta_description'], $form_tpl);
$form_tpl = str_replace('{priority}', $post_data['priority'], $form_tpl);
$form_tpl = str_replace('{checkbox_categories}', $checkboxes_cat, $form_tpl);
$form_tpl = str_replace('{checkbox_fixed}', $checkbox_fixed, $form_tpl);
$form_tpl = str_replace('{select_status}', $input_select_status, $form_tpl);
$form_tpl = str_replace('{select_comments}', $input_select_comments, $form_tpl);
$form_tpl = str_replace('{select_votings}', $input_select_reactions, $form_tpl);
$form_tpl = str_replace('{select_rss}', $input_select_rss, $form_tpl);
$form_tpl = str_replace('{select_guestlist}', $input_select_guestlist, $form_tpl);
$form_tpl = str_replace('{post_labels}', $checkbox_set_labels, $form_tpl);
$form_tpl = str_replace('{widget_images}', $choose_images, $form_tpl);
$form_tpl = str_replace('{token}', $_SESSION['token'], $form_tpl);
$form_tpl = str_replace('{submit_button}', $submit_btn, $form_tpl);
$form_tpl = str_replace('{id}', $id, $form_tpl);
$form_tpl = str_replace('{date}', $post_data['date'], $form_tpl);

echo '<div id="formResponse"></div>';
echo $form_tpl;