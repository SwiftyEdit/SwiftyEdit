<?php

$path_img = 'assets/images';
$dirs_img = se_get_dirs_rec($path_img);
array_unshift($dirs_img, $path_img);
$path_files = 'assets/files';
$dirs_files = se_get_dirs_rec($path_files);
array_unshift($dirs_files, $path_files);

$disk = $_SESSION['disk'] ?? $path_img;

$select_dir = '<div id="list_directories" hx-trigger="update_directories from:body">';
$select_dir .= '<form hx-post="/admin/uploads/write/" hx-swap="none" hx-trigger="change" method="POST" class="d-inline">';

$select_dir  .= '<div class="row">';
$select_dir  .= '<div class="col">';
$select_dir .= '<select name="selected_folder" class="form-control custom-select">';
$select_dir .= '<optgroup label="'.$lang['images'].'">';
foreach($dirs_img as $d) {
    $selected = '';
    if($disk == $d) {
        $selected = 'selected';
    }
    $short_d = str_replace($path_img, '', $d);
    $select_dir .= '<option value="'.$d.'" '.$selected.'>'.basename($path_img).$short_d.'</option>';
}
$select_dir .= '</optgroup>';
$select_dir .= '<optgroup label="'.$lang['files'].'">';
foreach($dirs_files as $d) {
    $selected = '';
    if($disk == $d) {
        $selected = 'selected';
    }
    $short_d = str_replace($path_files, '', $d);
    $select_dir .= '<option value="'.$d.'" '.$selected.'>'.basename($path_files).$short_d.'</option>';
}
$select_dir .= '</optgroup>';
$select_dir .= '</select>';
$select_dir .= '</div>';
$select_dir .= '</div>';
$select_dir .= '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
$select_dir .= '</form>';
$select_dir .= '</div>';

echo '<div class="card p-3 mb-1">';

echo '<div class="row">';

echo '<div class="col-md-8">';
echo $select_dir;
echo '</div>';
echo '<div class="col-md-4">';
echo '<form hx-post="/admin/uploads/write/" hx-swap="none" method="POST">';
echo '<div class="input-group">';
echo '<input type="text" name="new_folder" class="form-control">';
echo '<div class="input-group-append">';
echo '<input type="submit" name="submit" value="'.$lang['btn_create_new_folder'].'" class="btn btn-default">';
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
echo '</div>';
echo '</div>';
echo '</form>';
echo '</div>';
echo '</div>';


echo '</div>';