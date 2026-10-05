<?php

error_reporting(E_ALL ^E_WARNING ^E_NOTICE ^E_DEPRECATED);
echo '<div class="subHeader d-flex align-items-center">'.$icon['gear'].' '.$lang['nav_btn_settings'].' '.$lang['nav_btn_posts'].'</div>';

$writer_uri = '/admin-xhr/settings/general/write/';

$input_entries_per_page = [
    "input_name" => "prefs_posts_entries_per_page",
    "input_value" => $se_settings['posts_entries_per_page'],
    "label" => $lang['label_entries_per_page'],
    "type" => "text"
];


echo '<div class="card p-3">';
echo '<form hx-post="'.$writer_uri.'" hx-include="[name=\'csrf_token\']" hx-target="body" hx-swap="beforeend">';

echo '<h5 class="heading-line">'.$lang['label_entries'].'</h5>';

echo se_print_form_input($input_entries_per_page);

echo '<button type="submit" class="btn btn-primary" name="update_posts" value="update">'.$lang['btn_update'].'</button>';
echo '</form>';
echo '</div>';