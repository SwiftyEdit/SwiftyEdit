<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['plugin'].' '.$lang['nav_btn_addons'];
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-6">';
// list plugins
echo '<div class="card">';
echo '<div class="card-header">Plugins</div>';
echo '<div class="card-body"><div id="listPlugins" hx-get="/admin/addons/read/?action=list_plugins" hx-trigger="load"></div></div>';
echo '</div>';

echo '</div>';
echo '<div class="col-md-6">';
// list themes
echo '<div class="card">';
echo '<div class="card-header">Themes</div>';
echo '<div class="card-body"><div id="listThemes" hx-get="/admin/addons/read/?action=list_themes" hx-trigger="load, update_themes_list from:body"></div></div>';
echo '</div>';
echo '</div>';
echo '</div>';