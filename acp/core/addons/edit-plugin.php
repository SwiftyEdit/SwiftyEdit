<?php

$path = explode('/', $query);

$addon = basename($path[2]);
$include_file = basename($path[3]);
$this_addon_root_url = '/admin/addons/plugin/'.$addon;
$this_addon_root = SE_ROOT."/plugins/".$addon;

$addon_lang = se_return_addon_translations($addon);

$addon_info_file = SE_ROOT.'/plugins/'.$addon.'/info.json';
$addon_include_file = SE_ROOT.'/plugins/'.$addon.'/backend/'.$include_file.'.php';

$info_json = file_get_contents("$addon_info_file");
$addon_info = json_decode($info_json, true);

// se_docs_context_files() for the help button
require_once __DIR__.'/../docs/functions.php';

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['plugin'].' <a href="/admin/addons/">'.$lang['nav_btn_addons'].'</a>';

// plugin name (links to the plugin's first page) and help button,
// the help opens the first doc (docs/{lang}/*.md by priority, else readme.md)
$addon_start_url = $this_addon_root_url.'/'.($addon_info['navigation'][0]['file'] ?? 'start').'/';
echo '<div class="ms-auto">';
echo '<span class="badge bg-primary-subtle px-2 align-items-center rounded-pill">';
echo '<a href="'.htmlspecialchars($addon_start_url, ENT_QUOTES).'" class="mx-2">'.htmlspecialchars($addon_info['addon']['name'] ?? $addon).'</a>';
$help_files = se_docs_context_files('plugin', $addon);
if(!empty($help_files)) {
    echo se_print_plugin_docs_link($addon, basename($help_files[0]['path']), '<i class="bi bi-question m-0"></i>', null, 'btn btn-sm btn-default rounded-circle');
}
echo '</span>';
echo '</div>';

echo '</div>';

echo '<div class="card">';
echo '<div class="card-header">';
echo '<ul class="nav nav-tabs card-header-tabs">';
foreach($addon_info['navigation'] as $nav) {

    $nav_text = $nav['text'];
    if(array_key_exists($nav['text'],$addon_lang)) {
        $nav_text = $addon_lang[$nav['text']];
    }

    $active = '';
    if($include_file == $nav['file']) {
        $active = 'active';
    }
    echo '<li class="nav-item">';
    echo '<a href="'.$this_addon_root_url.'/'.$nav['file'].'/" class="nav-link '.$active.'">'.$nav_text.'</a>';
    echo '</li>';
}
echo '</ul>';

echo '</div>';
echo '<div class="card-body">';

if(is_file($addon_include_file)) {
    include $addon_include_file;
}

echo '</div>';
echo '</div>';