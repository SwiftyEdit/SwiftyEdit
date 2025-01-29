<?php

error_reporting(E_ALL ^E_WARNING ^E_NOTICE ^E_DEPRECATED);
echo '<div class="subHeader d-flex align-items-center">'.$icon['gear'].' '.$lang['nav_btn_settings'].' '.$lang['nav_btn_events'].'</div>';

$writer_uri = '/admin/settings/general/write/';

$input_entries_per_page = [
    "input_name" => "prefs_events_entries_per_page",
    "input_value" => $se_settings['events_entries_per_page'],
    "label" => $lang['label_entries_per_page'],
    "type" => "text"
];

$input_event_time_offset = [
    "input_name" => "prefs_posts_event_time_offset",
    "input_value" => $se_settings['posts_event_time_offset'],
    "label" => $lang['label_events_time_offset'],
    "form_text" => $lang['label_events_time_offset_text'],
    "type" => "text"
];

$input_images_prefix = [
    "input_name" => "prefs_events_images_prefix",
    "input_value" => $se_settings['events_images_prefix'],
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
    "input_name" => "prefs_events_default_banner",
    "input_value" => $se_settings['events_default_banner'],
    "label" => $lang['label_settings_default_image'],
    "options" => $select_images,
    "type" => "select"
];

$input_select_guestlist = [
    "input_name" => "prefs_posts_default_guestlist",
    "input_value" => $se_settings['posts_default_guestlist'],
    "label" => $lang['label_guestlist'],
    "options" => [
        $lang['label_guestlist_status_off'] => 1,
        $lang['label_guestlist_status_registered'] => 2,
        $lang['label_guestlist_status_global'] => 3
    ],
    "type" => "select"
];


echo '<div class="card p-3">';
echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';

echo '<h5 class="heading-line">'.$lang['label_entries'].'</h5>';

echo se_print_form_input($input_entries_per_page);
echo se_print_form_input($input_event_time_offset);

echo '<h5 class="heading-line">'.$lang['images'].'</h5>';

echo se_print_form_input($input_images_prefix);
echo se_print_form_input($input_select_default_banner);

echo '<h5 class="heading-line">'.$lang['label_guestlist'].'</h5>';

echo se_print_form_input($input_select_guestlist);


echo '<button type="submit" class="btn btn-primary" name="update_events" value="update">'.$lang['btn_update'].'</button>';
echo '</form>';
echo '</div>';