<?php

//$goto_install = array();

/**
 * function checkwritable()
 * check folders and files
 */

if(!defined('INSTALLER')) {
	header("location:../login.php");
	die("PERMISSION DENIED!");
}

function checkwritable($path) {

	global $goto_install;
	global $lang;
	global $icon;
	
	echo '<div class="row">';
    echo '<div class="col-md-4"><span title="'.$path.'">'.$icon['info_circle'].'</span> '.basename($path).'</div>';
	echo '<div class="col-md-8">';
	if(!is_writable("$path")){
	
		echo '<div class="alert alert-danger">'.$icon['exclamation_triangle'].' '.$lang['permission_false'].'<pre class="mb-0">'.$path.'</pre></div>';
		$goto_install[] = "false";
	
	} else {
	
		echo '<div class="alert alert-success">'.$icon['check'].' '. $lang['permission_true'].'<pre class="mb-0">'.$path.'</pre></div>';
		$goto_install[] = "true";
	
	}
	
	echo '</div>';
	echo '</div>';

}



function checkexistingdir($path) {

	global $goto_install;
	
	if(!is_dir("$path")){
		echo '<div class="row">';
		echo '<div class="col-md-4"><span title="'.$path.'">'.basename($path).'</span></div>';
		echo '<div class="col-md-8">';
		echo '<div class="alert alert-danger">' . $lang['missing_folder'] . '</div>';
		$goto_install[] = "false";
		echo '</div>';
		echo '</div>';
	
	}

}





/* collecting files and folders */


$check_this[] = SE_CONTENT . "/";
$check_this[] = SE_CONTENT . '/images';
$check_this[] = SE_CONTENT . '/files';
$check_this[] = SE_CONTENT . '/images_tmb';
$check_this[] = SE_CONTENT . "/avatars";
$check_this[] = SE_CONTENT . "/plugins";
$check_this[] = SE_CONTENT . "/SQLite";
$check_this[] = SE_CONTENT . "/galleries";
$check_this[] = SE_CONTENT . "/modules";
sort($check_this,SORT_NATURAL | SORT_FLAG_CASE);

$check_is_dir[] = "../styles/";
$check_is_dir[] = "../core/";
$check_is_dir[] = "../acp/";

/* minimum php version */

$needed_phpversion = "7.3";
$loaded_extensions = get_loaded_extensions();

/**
 * check if .htaccess exists
 * if not, rename _htaccess
 */

if(!is_file("../.htaccess")) {
	copy("../_htaccess","../.htaccess");
}

echo '<fieldset>';
echo '<legend>'.$lang['files_and_folders'].'</legend>';

foreach($check_this as $filepath){
	checkwritable("$filepath");
}

foreach($check_is_dir as $dir){
	checkexistingdir("$dir");
}

echo '</fieldset>';

echo '<fieldset>';
echo '<legend>'.$lang['system_requirements'].'</legend>';

$version = phpversion();

echo '<div class="row">';
echo '<div class="col-md-4">PHP Version</div>';
echo '<div class="col-md-8">';
	
if($version < $needed_phpversion) {
	echo '<div class="alert alert-danger">'.$icon['exclamation_triangle'].' ' . $lang['php_false'] . ' '.$needed_phpversion.'</div>';
	$goto_install[] = "false";
} else {
	echo '<div class="alert alert-success">'.$icon['check'].' ' . $lang['php_true'] . ' ('.$version.')</div>';
	$goto_install[] = "true";
}

echo '</div>';
echo '</div>';


echo '<div class="row">';
echo '<div class="col-md-4">PDO/SQLite</div>';
echo '<div class="col-md-8">';

if (in_array("pdo_sqlite", get_loaded_extensions())) {
	echo '<div class="alert alert-success">'.$icon['check'].' ' . $lang['pdo_true'] . '</div>';
	$goto_install[] = "true";
} else {
	echo '<div class="alert alert-danger">'.$icon['exclamation_triangle'].' '.$lang['pdo_false'].'</div>';
	$goto_install[] = "false";
}

echo '</div>';
echo '</div>';


if(!in_array("false",$goto_install)) {

	echo '<hr><form action="index.php" method="POST">';
	echo '<div class="row">';
	echo '<div class="col-md-4"></div>';
	echo '<div class="col-md-8">';

	echo '<input type="submit" class="btn btn-success" name="step2" value="'.$lang['next_step'].'">';

	echo '</div>';
	echo '</div>';
	
	echo '</form>';

}

echo '</fieldset>';