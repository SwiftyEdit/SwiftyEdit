<?php
	
require 'core/access.php';

/**
 * we can choose between different content files
 * store your files as array in your own config.php
 * example: $se_content_files array (	array (	'file'	=> 'content.sqlite3',	'desc'	=> 'Standard SQLite Database' ), array ( ... ) );
 */

if(!isset($_SESSION['se_db_content'])) {
	$_SESSION['se_db_content'] = SE_CONTENT . '/SQLite/'.$se_content_files[0]['file'];
}

if(isset($_POST['switchContent'])) {
	$switchContentId = (int) $_POST['switchContent'];
	$switchContentFile = SE_CONTENT . '/SQLite/'.$se_content_files[$switchContentId]['file'];
	
	if(is_file($switchContentFile)) {
		$_SESSION['se_db_content'] = $switchContentFile;
	}
}

if(isset($_SESSION['se_db_content'])) {
	if(is_file($_SESSION['se_db_content'])) {
		$se_db_content = $_SESSION['se_db_content'];
	}
}


$se_content_switch = '<button class="btn btn-primary btn-sm" data-bs-target="#contentSwitchContainer" data-bs-toggle="collapse">'.$icon['angle_down'].' '.basename($se_db_content).'</button>';

$se_content_switch .= '<div id="contentSwitchContainer" class="collapse">';
$se_content_switch .= '<div class="well well-sm">';

$i=0;
foreach($se_content_files as $files) {
	$btn_class = 'btn-outline-secondary';
	
	if($files['file'] == basename($se_db_content)) {
		$btn_class = 'btn-secondary';
	}

    $se_content_switch .= '<form action="?tn=dashboard" method="POST" class="d-inline me-2">';
    $se_content_switch .= '<button type="submit" name="switchContent" value="'.$i.'" class="btn '.$btn_class.'">'.$icon['database'].' '.$files['file'].'</button>';
    $se_content_switch .= $hidden_csrf_token;
    $se_content_switch .= '</form>';
    $i++;
}


$se_content_switch .= '</div>';
$se_content_switch .= '</div>';

?>