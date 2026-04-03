<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['plugin'].' <a href="/admin/addons/">'.$lang['nav_btn_addons'].'</a>';
echo 'Install Plugins or Themes from URL';
echo '</div>';

$reader_uri = '/admin-xhr/addons/read/'; // data-reader.php
$writer_uri = '/admin-xhr/addons/write/'; // data-writer.php



echo '<div class="card p-3">';

echo '<form hx-post="'.$writer_uri.'" hx-target="#get-addon-response">';
echo '<label>URL to .json file</label>';
echo '<div class="input-group">';
echo '<input type="text" class="form-control" name="get_addon_info_from_url" value="">';
echo '<button type="submit" class="btn btn-primary">'.$lang['btn_addon_get_info'].'</button>';
echo '</div>';
echo $hidden_csrf_token;
echo '</form>';

echo '<div id="get-addon-response">';
echo '</div>';

echo '</div>'; // card