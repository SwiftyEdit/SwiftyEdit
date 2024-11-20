<?php

/**
 * if we are editing pages, load the configuration from the selected theme
 * otherwise load the default theme configuration
 *
 * @var array $se_prefs global preferences
 *
 */

$editor_tpl_folder = $se_prefs['prefs_template'];


if(isset($page_template)) {
	$editor_tpl_folder = $page_template;
}

if($editor_tpl_folder == 'use_standard') {
	$editor_tpl_folder = $se_prefs['prefs_template'];
}

/**
 * We check the directories in the following order
 * 1. ../styles/.../dist/
 * 2. ../styles/.../config/
 * 3. ../styles/.../css/ and ../styles/.../js/
 */

$theme_src = '../public/assets/themes/' . $editor_tpl_folder . '/';

if (is_file($theme_src . 'dist/editor.css')) {
    $editor_styles = $theme_src.'dist/editor.css';
} else if (is_file($theme_src . 'config/editor.css')) {
    $editor_styles = $theme_src.'config/editor.css';
} else {
    $editor_styles = $theme_src.'css/editor.css';
}

if (is_file($theme_src . 'dist/tinyMCE_config.js')) {
    $tinyMCE_config = $theme_src.'dist/tinyMCE_config.js';
} else if (is_file($theme_src . 'config/tinyMCE_config.js')) {
    $tinyMCE_config = $theme_src.'config/tinyMCE_config.js';
} else {
    $tinyMCE_config = $theme_src.'js/tinyMCE_config.js';
}


if(!is_file("$editor_styles")) {
    $editor_styles = '../public/assets/themes/default/dist/editor.css';
}

if(!is_file($tinyMCE_config)) {
    $tinyMCE_config = '../public/assets/themes/default/dist/tinyMCE_config.js';
}

$tinyMCE_config_contents = file_get_contents($tinyMCE_config);