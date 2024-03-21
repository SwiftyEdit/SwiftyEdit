<?php

//prohibit unauthorized access
require 'core/access.php';


/**
 * save descriptions
 */
if(isset($_POST['save_prefs_descriptions'])) {

    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }

    if(isset($_POST['prefs_publisher_mode'])) {
        $data['prefs_publisher_mode'] = 'overwrite';
    } else {
        $data['prefs_publisher_mode'] = 'no';
    }

    $data['prefs_rss_time_offset'] = (int) $_POST['prefs_rss_time_offset'];

    se_write_option($data,'se');
}

/* save thumbnail */

if(isset($_POST['save_prefs_thumbnail'])) {

    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');
}


/* save upload preferences */
if(isset($_POST['save_prefs_upload'])) {

    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }


    if(isset($_POST['prefs_showfilesize'])) {
        $data['prefs_showfilesize'] = 'yes';
    } else {
        $data['prefs_showfilesize'] = 'no';
    }

    if(isset($_POST['prefs_uploads_remain_unchanged'])) {
        $data['prefs_uploads_remain_unchanged'] = 'yes';
    } else {
        $data['prefs_uploads_remain_unchanged'] = 'no';
    }

    se_write_option($data,'se');
}


if(!empty($_POST)) {
    /* read the preferences again */
    $se_get_preferences = se_get_preferences();

    foreach($se_get_preferences as $k => $v) {
        $key = $se_get_preferences[$k]['option_key'];
        $value = $se_get_preferences[$k]['option_value'];
        $se_prefs[$key] = $value;
    }

    foreach($se_prefs as $k => $v) {
        $$k = stripslashes($v);
    }
}



echo '<form action="?tn=system&sub=general&file=general" method="POST" class="form-horizontal">';

$input_page_name = [
    "input_name" => "prefs_pagename",
    "input_value" => $se_prefs['prefs_pagename'],
    "label" => $lang['label_settings_page_name']
];


$input_page_title = [
    "input_name" => "prefs_pagetitle",
    "input_value" => $se_prefs['prefs_pagetitle'],
    "label" => $lang['label_settings_page_title']
];


$input_page_subtitle = [
    "input_name" => "prefs_pagesubtitle",
    "input_value" => $se_prefs['prefs_pagesubtitl'],
    "label" => $lang['label_settings_page_subtitle']
];


echo '<div class="row">';
echo '<div class="col-md-4">';
echo tpl_form_input_text($input_page_name);
echo '</div>';
echo '<div class="col-md-4">';
echo tpl_form_input_text($input_page_title);
echo '</div>';
echo '<div class="col-md-4">';
echo tpl_form_input_text($input_page_subtitle);
echo '</div>';
echo '</div>';


$prefs_pagedescription_input = '<textarea class="form-control" name="prefs_pagedescription">'.$se_prefs['prefs_pagedescription'].'</textarea>';
echo tpl_form_control_group('',$lang['label_settings_page_description'],$prefs_pagedescription_input);




$toggle_btn_publisher .= '<span class="input-group-text">'.$lang['label_settings_publisher_mode'].'</span>';
$toggle_btn_publisher .= '<span class="input-group-text">';
$toggle_btn_publisher .= '<input type="checkbox" name="prefs_publisher_mode" '.($se_prefs['prefs_publisher_mode'] == "overwrite" ? 'checked' :'').'>';
$toggle_btn_publisher .= '</span>';

$prefs_publisher_input  .= '<div class="input-group">';
$prefs_publisher_input .= '<input class="form-control" type="text" name="prefs_default_publisher" value="'.$se_prefs['prefs_default_publisher'].'">';
$prefs_publisher_input .= $toggle_btn_publisher;
$prefs_publisher_input .= '</div>';

echo tpl_form_control_group('',$lang['label_author'],$prefs_publisher_input);

echo '<hr>';

$input_rss_offset = [
    "input_name" => "prefs_rss_time_offset",
    "input_value" => $se_prefs['prefs_rss_time_offset'],
    "label" => $lang['label_settings_rss_time_offset']
];

echo tpl_form_input_text($input_rss_offset);

$input_nbr_page_versions = [
    "input_name" => "prefs_nbr_page_versions",
    "input_value" => $se_prefs['prefs_nbr_page_versions'],
    "label" => $lang['label_settings_nbr_page_versions']
];

echo tpl_form_input_text($input_nbr_page_versions);

$input_pagesort_minlength = [
    "input_name" => "prefs_pagesort_minlength",
    "input_value" => $se_prefs['prefs_pagesort_minlength'],
    "label" => $lang['label_settings_page_sort_min_length']
];

echo tpl_form_input_text($input_pagesort_minlength);

echo '<input type="submit" class="btn btn-success" name="save_prefs_descriptions" value="'.$lang['save'].'">';
echo $hidden_csrf_token;
echo '</form>';


echo '<h5 class="heading-line">'.$lang['images'].'</h5>';

echo '<form action="?tn=system&sub=general&file=general" method="POST" class="form-horizontal">';

$arr_Images = se_get_all_images_rec();

/* default page logo */
$select_prefs_pagelogo  = '<select name="prefs_pagelogo" class="form-control custom-select">';
$select_prefs_pagelogo .= '<option value="">'.$lang['option_nothing_selected'].'</option>';

foreach($arr_Images as $page_logo) {
    $selected = "";
    if($se_prefs['prefs_pagelogo'] == "$page_logo") {
        $selected = "selected";
    }
    $show_page_logo_filename = str_replace('../content/','/',$page_logo);
    $select_prefs_pagelogo .= '<option '.$selected.' value="'.$page_logo.'">'.$show_page_logo_filename.'</option>';
}

$select_prefs_pagelogo .= "</select>";

echo tpl_form_control_group('',"Logo",$select_prefs_pagelogo);

/* default thumbnail */

$select_prefs_thumbnail  = '<select name="prefs_pagethumbnail" class="form-control custom-select">';
$select_prefs_thumbnail .= '<option value="">'.$lang['option_nothing_selected'].'</option>';

foreach($arr_Images as $page_thumbnail) {
    $selected = "";
    if($se_prefs['prefs_pagethumbnail'] == "$page_thumbnail") {
        $selected = "selected";
    }
    $show_page_thumbnail_filename = str_replace('../content/','/',$page_thumbnail);
    $select_prefs_thumbnail .= '<option '.$selected.' value="'.$page_thumbnail.'">'.$show_page_thumbnail_filename.'</option>';
}
$select_prefs_thumbnail .= "</select>";

echo tpl_form_control_group('',$lang['label_pages_thumbnail'],$select_prefs_thumbnail);

/* Thumbnail Prefix */

$input_tmb_prefix = [
    "input_name" => "prefs_pagethumbnail_prefix",
    "input_value" => $se_prefs['prefs_pagethumbnail_prefix'],
    "label" => $lang['label_settings_prefix']
];

echo tpl_form_input_text($input_tmb_prefix);

/* Favicon */
$select_prefs_favicon  = '<select name="prefs_pagefavicon" class="form-control custom-select">';
$select_prefs_favicon .= '<option value="">'.$lang['option_nothing_selected'].'</option>';

foreach($arr_Images as $page_favicon) {

    if(substr($page_favicon, -4) != '.png') {
        continue;
    }

    $selected = "";
    if($se_prefs['prefs_pagefavicon'] == "$page_favicon") {
        $selected = "selected";
    }
    $show_page_favicon_filename = str_replace('../content/','/',$page_favicon);
    $select_prefs_favicon .= '<option '.$selected.' value="'.$page_favicon.'">'.$show_page_favicon_filename.'</option>';
}
$select_prefs_favicon .= "</select>";

echo tpl_form_control_group('',$lang['label_settings_favicon'],$select_prefs_favicon);



echo tpl_form_control_group('','',"<input type='submit' class='btn btn-success' name='save_prefs_thumbnail' value='$lang[save]'>");
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</form>';


echo '<h6 class="heading-line">'.$lang['uploads'].'</h6>';
echo '<form action="?tn=system&sub=general&file=general" method="POST" class="form-horizontal">';

$prefs_maximage_input  = '<div class="row"><div class="col-md-6">';
$prefs_maximage_input .= '<div class="input-group">';
$prefs_maximage_input .= '<input class="form-control" type="text" name="prefs_maximagewidth" value="'.$se_prefs['prefs_maximagewidth'].'">';
$prefs_maximage_input .= '<span class="input-group-text">'.$icon['arrow_left'].$icon['arrow_right'].'</span>';
$prefs_maximage_input .= '</div>';
$prefs_maximage_input .= '</div><div class="col-md-6">';
$prefs_maximage_input .= '<div class="input-group">';
$prefs_maximage_input .= '<input class="form-control" type="text" name="prefs_maximageheight" value="'.$se_prefs['prefs_maximageheight'].'">';
$prefs_maximage_input .= '<span class="input-group-text">'.$icon['arrow_up'].$icon['arrow_down'].'</span>';
$prefs_maximage_input .= '</div>';
$prefs_maximage_input .= '</div></div>';

$prefs_maxtmb_input  = '<div class="row"><div class="col-md-6">';
$prefs_maxtmb_input .= '<div class="input-group">';
$prefs_maxtmb_input .= '<input class="form-control" type="text" name="prefs_maxtmbwidth" value="'.$se_prefs['prefs_maxtmbwidth'].'">';
$prefs_maxtmb_input .= '<span class="input-group-text">'.$icon['arrow_left'].$icon['arrow_right'].'</span>';
$prefs_maxtmb_input .= '</div>';
$prefs_maxtmb_input .= '</div><div class="col-md-6">';
$prefs_maxtmb_input .= '<div class="input-group">';
$prefs_maxtmb_input .= '<input class="form-control" type="text" name="prefs_maxtmbheight" value="'.$se_prefs['prefs_maxtmbheight'].'">';
$prefs_maxtmb_input .= '<span class="input-group-text">'.$icon['arrow_up'].$icon['arrow_down'].'</span>';
$prefs_maxtmb_input .= '</div>';
$prefs_maxtmb_input .= '</div></div>';

echo '<div class="row">';
echo '<div class="col-md-6">';
echo tpl_form_control_group('',$lang['label_settings_max_img'],"$prefs_maximage_input");
echo '</div>';
echo '<div class="col-md-6">';
echo tpl_form_control_group('',$lang['label_settings_max_tmb'],"$prefs_maxtmb_input");
echo '</div>';
echo '</div>';

$input_max_filesize = [
    "input_name" => "prefs_maxfilesize",
    "input_value" => $se_prefs['prefs_maxfilesize'],
    "label" => $lang['label_settings_max_filesize']
];

echo tpl_form_input_text($input_max_filesize);

$toggle_btn_upload_unchanged  = '<div class="form-group form-check">';
$toggle_btn_upload_unchanged .= '<input type="checkbox" class="form-check-input" id="checkUpload" name="prefs_uploads_remain_unchanged" '.($se_prefs['prefs_uploads_remain_unchanged'] == "yes" ? 'checked' :'').'>';
$toggle_btn_upload_unchanged .= '<label class="form-check-label" for="checkUpload">'.$lang['label_settings_uploads_remain_unchanged'].'</label>';
$toggle_btn_upload_unchanged .= '</div>';

echo $toggle_btn_upload_unchanged;


echo tpl_form_control_group('','',"<input type='submit' class='btn btn-success' name='save_prefs_upload' value='$lang[save]'>");
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</form>';