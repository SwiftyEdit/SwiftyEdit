<?php
//error_reporting(E_ALL ^E_NOTICE);
//prohibit unauthorized access
require 'core/access.php';


/* delete addon */

if(isset($_POST['delete_addon'])) {
	se_delete_addon($_POST['addon'],$_POST['type']);
	$all_mods = get_all_modules();
	$all_plugins = get_all_plugins();
}


/**
 * list and access module
 * list plugins
 * list and access themes
 */

$addon_mode = 'list_modules';
$active_modules = 'active';
$active_plugins = '';
$active_themes = '';

/* access module */
if(isset($a) && (is_file(SE_CONTENT."/modules/$sub/info.inc.php"))) {
	$addon_mode = 'access_module';
	unset($mod);
} else if($sub == 't') {
	$addon_mode = 'list_themes';
	$active_themes = 'active';
	$active_modules = '';
} else if($sub == 'p') {
	$addon_mode = 'list_plugins';
	$active_plugins = 'active';
	$active_modules = '';
} else if($sub == 'u') {
	$addon_mode = 'upload';
	$active_modules = '';
	$active_plugins = '';
	$active_themes = '';
	$active_upload = 'active';
}

if($sub == 'list' OR $sub == 'p' OR $sub == 'm' OR $sub == 't' OR $sub == 'u') {
	echo '<div class="subHeader">';
	
	if($_SESSION['drm_acp_sensitive_files'] == 'allowed') {
		echo '<div class="btn-group float-end" role="group">';
		echo '<a href="?tn=addons&sub=u" class="btn btn-default '.$active_upload.'">'.$icon['upload'].' '.$lang['btn_install'].'</a>';
		echo '</div>';
	}
	
	echo '<div class="btn-group" role="group">';
	echo '<a href="?tn=addons&sub=m" class="btn btn-default '.$active_modules.'">Module</a>';
	echo '<a href="?tn=addons&sub=p" class="btn btn-default '.$active_plugins.'">Plugins</a>';
	echo '<a href="?tn=addons&sub=t" class="btn btn-default '.$active_themes.'">Themes</a>';
	echo '</div>';
	echo '</div>';
}

/* list module */

if($addon_mode == 'list_modules') {
	include 'list.addons.php';
}

if($addon_mode == 'access_module') {
	include SE_CONTENT.'/modules/'.$sub.'/info.inc.php';
	include SE_CONTENT.'/modules/'.$sub.'/backend/'.$a.'.php';
}


/* list themes */

if($addon_mode == 'list_themes') {
	include 'list.themes.php';
}


/* list plugins */

if($addon_mode == 'list_plugins') {
	include 'list.plugins.php';
}

/* upload/update addons */
if($addon_mode == 'upload') {
	if($_SESSION['drm_acp_sensitive_files'] == 'allowed') {
		if($se_upload_addons === true) {
			include 'upload_addons.php';
		} else {
			echo '<div class="alert alert-danger">'.$lang['upload_addons_deactivated'].'</div>';
		}
	} else {
		echo '<div class="alert alert-danger">'.$lang['drm_no_access'].'</div>';
	}
	
}
