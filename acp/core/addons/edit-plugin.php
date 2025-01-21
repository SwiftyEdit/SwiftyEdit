<?php

$path = explode('/', $query);

$addon = basename($path[2]);
$include_file = basename($path[3]);
$this_addon_root_url = '/admin/addons/plugin/'.$addon;
$this_addon_root = SE_ROOT."/plugins/".$addon;

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['plugin'].' <a href="/admin/addons/">'.$lang['nav_btn_addons'].'</a>';
echo '</div>';

$addon_info_file = SE_ROOT.'/plugins/'.$addon.'/info.json';
$addon_include_file = SE_ROOT.'/plugins/'.$addon.'/backend/'.$include_file.'.php';

$info_json = file_get_contents("$addon_info_file");
$addon_info = json_decode($info_json, true);

echo '<div class="card">';
echo '<div class="card-header">';
echo '<ul class="nav nav-tabs card-header-tabs">';
foreach($addon_info['navigation'] as $nav) {

    $active = '';
    if($include_file == $nav['file']) {
        $active = 'active';
    }
    echo '<li class="nav-item">';
    echo '<a href="'.$this_addon_root_url.'/'.$nav['file'].'/" class="nav-link '.$active.'">'.$nav['text'].'</a>';
    echo '</li>';
}
echo '</ul>';

echo '<span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-primary">'.$addon_info['addon']['name'].'</span>';

echo '</div>';
echo '<div class="card-body">';

if(is_file($addon_include_file)) {
    include $addon_include_file;
}

echo '</div>';