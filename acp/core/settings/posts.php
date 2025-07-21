<?php

error_reporting(E_ALL ^E_WARNING ^E_NOTICE ^E_DEPRECATED);
echo '<div class="subHeader d-flex align-items-center">'.$icon['gear'].' '.$lang['nav_btn_settings'].' '.$lang['nav_btn_posts'].'</div>';

$writer_uri = '/admin/xhr/settings/general/write/';

$input_entries_per_page = [
    "input_name" => "prefs_posts_entries_per_page",
    "input_value" => $se_settings['posts_entries_per_page'],
    "label" => $lang['label_entries_per_page'],
    "type" => "text"
];

$input_img_prefix = [
    "input_name" => "prefs_posts_images_prefix",
    "input_value" => $se_settings['posts_images_prefix'],
    "label" => $lang['label_settings_prefix'],
    "type" => "text"
];

$arr_Images = se_get_all_images_rec();

foreach ($arr_Images as $k => $v) {
    $select_images[basename($v)] = $v;
}
$select_images = [];
$select_nothing = ['option_nothing_selected' => "null"];
$select_images = $select_nothing+$select_images;

$input_select_default_banner = [
    "input_name" => "prefs_posts_default_banner",
    "input_value" => $se_settings['posts_default_banner'],
    "label" => $lang['label_settings_default_image'],
    "options" => $select_images,
    "type" => "select"
];


echo '<div class="card p-3">';
echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';

echo '<h5 class="heading-line">'.$lang['label_entries'].'</h5>';

echo se_print_form_input($input_entries_per_page);




echo '<h5 class="heading-line">'.$lang['images'].'</h5>';

echo se_print_form_input($input_img_prefix);
echo se_print_form_input($input_select_default_banner);

echo '<button type="submit" class="btn btn-primary" name="update_posts" value="update">'.$lang['btn_update'].'</button>';
echo '</form>';
echo '</div>';