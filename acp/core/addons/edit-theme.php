<?php
error_reporting(0);
$path = explode('/', $query);

$theme = basename($path[2]);
$include_file = basename($path[3]);
$this_theme_root_url = '/admin/addons/theme/'.$theme;
$this_theme_root = SE_ROOT."public/assets/themes/".$theme;



echo '<div class="subHeader d-flex align-items-center">';
echo $icon['plugin'].' <a href="/admin/addons/">'.$lang['nav_btn_addons'].'</a>';
echo '</div>';

$theme_include_file = $this_theme_root.'/php/options.php';
include $theme_include_file;


if(is_array($theme_options)) {

    $theme_data = se_get_theme_options($theme);

    echo '<form hx-post="/admin-xhr/addons/write/" hx-target="body" hx-swap="beforeend">';
    echo '<div class="card mb-3">';
    echo '<div class="card-header">'.$theme.'</div>';

    echo '<div class="card-body">';


    foreach($theme_options as $key => $value) {

        $this_value = '';
        $get_key = '';
        $get_key = array_search("theme_$key", array_column($theme_data, 'theme_label'));
        if(is_numeric($get_key)) {
            $this_value = $theme_data[$get_key]['theme_value'];
        }



        echo '<div class="mb-3">';
        echo '<label class="form-label">'.$value.'</label>';
        echo '<input type="text" name="theme_'.$key.'" value="'.$this_value.'" class="form-control">';
        echo '</div>';

    }

    echo '</div>';


    echo '<div class="card-footer">';
    echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
    echo '<input type="hidden" name="theme" value="'.$theme.'">';
    echo '<input type="submit" name="save_theme_options" value="'.$lang['save'].'" class="btn btn-success">';
    echo '</div>';
    echo '</form>';


}