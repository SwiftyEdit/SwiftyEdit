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

$editor_styles = '../styles/'.$editor_tpl_folder.'/css/editor.css';
$tinyMCE_config = '../styles/'.$editor_tpl_folder.'/js/tinyMCE_config.js';

if(!is_file("$editor_styles")) {
    $editor_styles = '../styles/default/css/editor.css';
}

if(!is_file($tinyMCE_config)) {
    $tinyMCE_config = '../styles/default/js/tinyMCE_config.js';
}

$tinyMCE_config_contents = file_get_contents($tinyMCE_config);