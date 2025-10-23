<?php

/**
 * @var array $se_settings
 * @var array $icon
 * @var array $lang
 */

error_reporting(E_ALL ^E_WARNING ^E_NOTICE ^E_DEPRECATED);
echo '<div class="subHeader d-flex align-items-center">'.$icon['gear'].' '.$lang['nav_btn_settings'].'</div>';


$writer_uri = '/admin-xhr/settings/general/write/';
$reader_uri = '/admin-xhr/settings/general/read/';

$input_page_name = [
    "input_name" => "prefs_pagename",
    "input_value" => $se_settings['pagename'],
    "label" => $lang['label_settings_page_name'],
    "type" => "text"
];


$input_page_title = [
    "input_name" => "prefs_pagetitle",
    "input_value" => $se_settings['pagetitle'],
    "label" => $lang['label_settings_page_title'],
    "type" => "text"
];


$input_page_subtitle = [
    "input_name" => "prefs_pagesubtitle",
    "input_value" => $se_settings['pagesubtitle'],
    "label" => $lang['label_settings_page_subtitle'],
    "type" => "text"
];

$input_page_description = [
    "input_name" => "prefs_pagedescription",
    "input_value" => $se_settings['pagedescription'],
    "label" => $lang['label_settings_page_description'],
    "type" => "textarea"
];

$input_page_author = [
    "input_name" => "prefs_default_publisher",
    "input_value" => $se_settings['default_publisher'],
    "label" => $lang['label_author'],
    "type" => "text"
];

$input_page_author_mode = [
    "input_name" => "prefs_publisher_mode",
    "input_value" => $se_settings['publisher_mode'],
    "label" => $lang['label_settings_publisher_mode'],
    "type" => "checkbox",
    "status" => $se_settings['prefs_publisher_mode'] == "overwrite" ? 'checked' :''
];

$input_rss_offset = [
    "input_name" => "prefs_rss_time_offset",
    "input_value" => $se_settings['rss_time_offset'],
    "label" => $lang['label_settings_rss_time_offset'],
    "type" => "text"
];

$input_nbr_page_versions = [
    "input_name" => "prefs_nbr_page_versions",
    "input_value" => $se_settings['nbr_page_versions'],
    "label" => $lang['label_settings_nbr_page_versions'],
    "type" => "text"
];

$input_pagesort_minlength = [
    "input_name" => "prefs_pagesort_minlength",
    "input_value" => $se_settings['pagesort_minlength'],
    "label" => $lang['label_settings_page_sort_min_length'],
    "type" => "text"
];


$arr_Images = se_get_all_images_rec();
$select_images = [];
foreach ($arr_Images as $k => $v) {
    $select_images[basename($v)] = $v;
}

$select_nothing = ['label_no_file_selected' => "null"];
$select_images = $select_nothing+$select_images;

$input_select_page_logo = [
    "input_name" => "prefs_pagelogo",
    "input_value" => $se_settings['pagelogo'],
    "label" => 'Page Logo',
    "options" => $select_images,
    "type" => "select"
];

$input_select_thumbnail = [
    "input_name" => "prefs_pagethumbnail",
    "input_value" => $se_settings['pagethumbnail'],
    "label" => $lang['label_pages_thumbnail'],
    "options" => $select_images,
    "type" => "select"
];

$select_favicons = [];
foreach ($arr_Images as $k => $v) {
    if(!str_ends_with("$v",'.png')) {
        continue;
    }
    $select_favicons[basename($v)] = $v;
}

$select_favicons = $select_nothing+$select_favicons;

$input_select_favicon = [
    "input_name" => "prefs_pagefavicon",
    "input_value" => $se_settings['pagefavicon'],
    "label" => 'Favicon',
    "options" => $select_favicons,
    "type" => "select"
];

$input_image_prefix = [
    "input_name" => "prefs_pagethumbnail_prefix",
    "input_value" => $se_settings['pagethumbnail_prefix'],
    "label" => $lang['label_settings_prefix'],
    "type" => "text"
];

$input_max_img_width = [
    "input_name" => "prefs_maximagewidth",
    "input_value" => $se_settings['maximagewidth'],
    "label" => 'Max Image Width',
    "type" => "text"
];

$input_max_img_height = [
    "input_name" => "prefs_maximageheight",
    "input_value" => $se_settings['maximageheight'],
    "label" => 'Max Image Height',
    "type" => "text"
];

$input_max_tmb_width = [
    "input_name" => "prefs_maxtmbwidth",
    "input_value" => $se_settings['maxtmbwidth'],
    "label" => 'Max Thumbnail Width',
    "type" => "text"
];

$input_max_tmb_height = [
    "input_name" => "prefs_maxtmbheight",
    "input_value" => $se_settings['maxtmbheight'],
    "label" => 'Max Thumbnail Width',
    "type" => "text"
];

$input_max_upload_filesize = [
    "input_name" => "prefs_maxfilesize",
    "input_value" => $se_settings['maxfilesize'],
    "label" => $lang['label_settings_max_filesize'],
    "type" => "text"
];

$input_uploads_unchanged = [
    "input_name" => "prefs_uploads_remain_unchanged",
    "input_value" => $se_settings['uploads_remain_unchanged'],
    "label" => $lang['label_settings_uploads_remain_unchanged'],
    "type" => "checkbox",
    "status" => $se_settings['uploads_remain_unchanged'] == "yes" ? 'checked' :''
];


$input_cms_domain = [
    "input_name" => "prefs_cms_domain",
    "input_value" => $se_settings['cms_domain'],
    "label" => $lang['label_settings_cms_domain'],
    "type" => "text"
];

$input_cms_ssl_domain = [
    "input_name" => "prefs_cms_ssl_domain",
    "input_value" => $se_settings['cms_ssl_domain'],
    "label" => $lang['label_settings_cms_domain_ssl'],
    "type" => "text"
];

$input_cms_base = [
    "input_name" => "prefs_cms_base",
    "input_value" => $se_settings['cms_base'],
    "label" => $lang['label_settings_cms_base'],
    "type" => "text"
];

$input_login_slug = [
    "input_name" => "prefs_login_slug",
    "input_value" => $se_settings['login_slug'],
    "label" => $lang['label_settings_login_slug'],
    "input_group_start_text" => "/admin/",
    "type" => "text"
];

$input_mail_name = [
    "input_name" => "prefs_mailer_name",
    "input_value" => $se_settings['mailer_name'],
    "label" => $lang['label_settings_mailer_name'],
    "type" => "text"
];

$input_mail_address = [
    "input_name" => "prefs_mailer_adr",
    "input_value" => $se_settings['mailer_adr'],
    "label" => $lang['label_settings_mailer_mail'],
    "type" => "text"
];

$input_mail_type = [
    "input_name" => "prefs_mailer_type",
    "input_value" => $se_settings['mailer_type'],
    "radios" => [
        "label_settings_use_mail" => "mail",
        "label_settings_use_smtp" => "smtp"
    ],
    "type" => "radios"
];

$input_notify_mail_address = [
    "input_name" => "prefs_notify_mail",
    "input_value" => $se_settings['notify_mail'],
    "label" => $lang['label_settings_notify_mail'],
    "type" => "text"
];

$date_formats = array("Y-m-d","d.m.Y","d/m/Y","m/d/Y");
foreach($date_formats as $dates) {
    $example = date("$dates",time());
    $select_dates["$dates ($example)"] = $dates;
}

$input_select_date_format = [
    "input_name" => "prefs_mailer_type",
    "input_value" => $se_settings['dateformat'],
    "label" => $lang['label_settings_date_format'],
    "options" => $select_dates,
    "type" => "select"
];

$time_formats = array("H:i","g:i a","g:i A");
foreach($time_formats as $times) {
    $example = date("$times",time());
    $select_times["$times ($example)"] = $times;
}

$input_select_time_format = [
    "input_name" => "prefs_timeformat",
    "input_value" => $se_settings['timeformat'],
    "label" => $lang['label_settings_time_format'],
    "options" => $select_times,
    "type" => "select"
];

$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
foreach ($timezones as $timezone) {
    $tz[$timezone] = $timezone;
}

$input_select_timezone = [
    "input_name" => "prefs_timezone",
    "input_value" => $se_settings['timezone'],
    "label" => $lang['label_settings_timezone'],
    "options" => $tz,
    "type" => "select"
];

$input_maintenance = [
    "input_name" => "prefs_maintenance_code",
    "input_value" => $se_settings['maintenance_code'],
    "label" => $lang['label_settings_maintenance_code'],
    "type" => "text"
];

$input_usertemplates = [
    "input_name" => "prefs_usertemplate",
    "input_value" => $se_settings['usertemplate'],
    "radios" => [
        "label_settings_themes_userstyles_off" => "off",
        "label_settings_themes_userstyles_on" => "on",
        "label_settings_themes_userstyles_overwrite" => "overwrite"
    ],
    "type" => "radios"
];

$input_smarty_cache = [
    "input_name" => "prefs_smarty_cache",
    "input_value" => $se_settings['smarty_cache'],
    "label" => 'Smarty Cache',
    "type" => "checkbox",
    "status" => $se_settings['smarty_cache'] == "1" ? 'checked' :''
];

$input_smarty_compile_check = [
    "input_name" => "prefs_smarty_compile_check",
    "input_value" => $se_settings['smarty_compile_check'],
    "label" => 'Smarty Compile Check',
    "type" => "checkbox",
    "status" => $se_settings['smarty_compile_check'] == "1" ? 'checked' :''
];

$input_smarty_cache_lifetime = [
    "input_name" => "prefs_smarty_cache_lifetime",
    "input_value" => $se_settings['smarty_cache_lifetime'],
    "label" => 'Smarty Cache lifetime',
    "type" => "text"
];

$get_all_languages = get_all_languages();
foreach($get_all_languages as $langs) {
    $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
}

$input_select_language = [
    "input_name" => "prefs_default_language",
    "input_value" => $se_settings['default_language'],
    "label" => $lang['label_settings_default_language'],
    "options" => $lang_options,
    "type" => "select"
];




echo '<div class="card">';
echo '<div class="card-header">';
echo '<ul class="nav nav-tabs card-header-tabs">';
echo '<li class="nav-item"><button class="nav-link active" id="general" data-bs-toggle="tab" data-bs-target="#general-settings-tab">'.$lang['nav_btn_general'].'</button></li>';
echo '<li class="nav-item"><button class="nav-link" id="system" data-bs-toggle="tab" data-bs-target="#system-tab">'.$lang['nav_btn_system'].'</button></li>';
echo '<li class="nav-item"><button class="nav-link" id="email" data-bs-toggle="tab" data-bs-target="#email-tab">E-Mail</button></li>';
echo '</ul>';
echo '</div>';
echo '<div class="card-body">';
echo '<div class="tab-content" id="myTabContent">';
echo '<div class="tab-pane fade show active" id="general-settings-tab" role="tabpanel" tabindex="0">';

echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';

$input_group = [
    se_print_form_input($input_page_name),
    se_print_form_input($input_page_title),
    se_print_form_input($input_page_subtitle)
];

echo str_replace(['{col1}','{col2}','{col3}'],$input_group,$bs_row_col3);

echo se_print_form_input($input_page_description);
echo se_print_form_input($input_page_author);
echo se_print_form_input($input_page_author_mode);

echo '<hr>';

$input_group = [
    se_print_form_input($input_rss_offset),
    se_print_form_input($input_nbr_page_versions),
    se_print_form_input($input_pagesort_minlength)
];

echo str_replace(['{col1}','{col2}','{col3}'],$input_group,$bs_row_col3);


echo '<h5 class="heading-line">'.$lang['images'].'</h5>';


echo se_print_form_input($input_image_prefix);

$input_group = [
    se_print_form_input($input_select_page_logo),
    se_print_form_input($input_select_thumbnail),
    se_print_form_input($input_select_favicon)
];

echo str_replace(['{col1}','{col2}','{col3}'],$input_group,$bs_row_col3);

$input_group = [
    se_print_form_input($input_max_img_width),
    se_print_form_input($input_max_img_height),
    se_print_form_input($input_max_tmb_width),
    se_print_form_input($input_max_tmb_height)
];

echo str_replace(['{col1}','{col2}','{col3}','{col4}'],$input_group,$bs_row_col4);


echo se_print_form_input($input_max_upload_filesize);
echo se_print_form_input($input_uploads_unchanged);

echo '<hr>';
echo '<button type="submit" class="btn btn-primary" name="update_general" value="update">'.$lang['btn_update'].'</button>';
echo '</form>'; // hx-post

echo '</div>'; // tab
echo '<div class="tab-pane fade" id="system-tab" role="tabpanel" tabindex="0">';

echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';

echo se_print_form_input($input_cms_domain);
echo se_print_form_input($input_cms_ssl_domain);
echo se_print_form_input($input_cms_base);
echo se_print_form_input($input_login_slug);


echo '<button type="submit" class="btn btn-primary" name="update_general_system" value="update">'.$lang['btn_update'].'</button>';
echo '</form>'; // hx-post

echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';
echo '<h5 class="heading-line">'.$lang['label_settings_datetime'].'</h5>';

$input_group = [
    se_print_form_input($input_select_timezone),
    se_print_form_input($input_select_date_format),
    se_print_form_input($input_select_time_format)
];

echo str_replace(['{col1}','{col2}','{col3}'],$input_group,$bs_row_col3);

echo '<button type="submit" class="btn btn-primary" name="update_datetime" value="update">'.$lang['btn_update'].'</button>';
echo '</form>'; // hx-post

echo '<h5 class="heading-line">'.$lang['label_settings_themes'].'</h5>';
echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';

echo se_print_form_input($input_maintenance);
echo se_print_form_input($input_usertemplates);

echo se_print_form_input($input_smarty_cache);
echo se_print_form_input($input_smarty_compile_check);
echo se_print_form_input($input_smarty_cache_lifetime);


echo '<hr>';
echo '<button type="submit" class="btn btn-primary" name="update_themes" value="update">'.$lang['btn_update'].'</button>';
echo '</form>'; // hx-post

echo '<h5 class="heading-line">'.$lang['label_language'].'</h5>';

echo '<div class="row">';
echo '<div class="col-md-6">';
echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';
echo se_print_form_input($input_select_language);
echo '<button type="submit" class="btn btn-primary" name="update_language" value="update">'.$lang['btn_update'].'</button>';
echo '</form>'; // hx-post
echo '</div>';
echo '<div class="col-md-6">';
echo $lang['label_settings_hide_languages'];
$hidden_langs = [];
if($se_settings['deactivated_languages'] != '') {
    $hidden_langs = json_decode($se_settings['deactivated_languages']);
}
echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';
echo '<table class="table table-sm table-hover">';
foreach($get_all_languages as $langs) {
    $check = '';
    if (in_array($langs['lang_folder'], $hidden_langs)) {
        $check = 'checked';
    }

    echo '<tr>';
    echo '<td>';
    echo '<input type="checkbox" id="' . $langs['lang_folder'] . '" class="form-check-input" name="hide_langs[]" value="' . $langs['lang_folder'] . '" ' . $check . '>';
    echo '</td>';
    echo '<td><label for="' . $langs['lang_folder'] . '" class="d-block">' . $langs['lang_sign'] . '</label></td>';
    echo '<td><label for="' . $langs['lang_folder'] . '" class="d-block">' . $langs['lang_desc'] . '</label></td>';
    echo '<td><label for="' . $langs['lang_folder'] . '" class="d-block">' . $langs['lang_folder'] . '</label></td>';
    echo '</tr>';
}
echo '</table>';
echo '<button type="submit" class="btn btn-primary" name="update_hide_languages" value="update">'.$lang['btn_update'].'</button>';
echo '</form>'; // hx-post
echo '</div>';
echo '</div>';

echo '</div>'; // tab
echo '<div class="tab-pane fade" id="email-tab" role="tabpanel" tabindex="0">';

echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';
echo '<div class="row">';
echo '<div class="col-md-6">';
echo se_print_form_input($input_mail_name);
echo se_print_form_input($input_mail_address);
echo se_print_form_input($input_mail_type);
echo '</div>';
echo '<div class="col-md-6">';
echo se_print_form_input($input_notify_mail_address);
echo '</div>';
echo '</div>';

echo '<hr>';
echo '<button type="submit" class="btn btn-primary" name="update_email" value="update">'.$lang['btn_update'].'</button>';
echo '</form>'; // hx-post

echo '<div id="sendmail_test" class="my-3"></div>';

echo '<button class="btn btn-default" hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="#sendmail_test" name="sendmail_test">';
echo $lang['label_settings_mailer_send_test'].' '.$se_settings['notify_mail'];
echo '</button>';

echo '</div>'; // tab

echo '</div>';
echo '</div>';
echo '</div>';


