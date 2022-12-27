<?php

/**
 * SwiftyEdit default Configuration file
 * this file will be replaced with every update
 *
 * you can expand/overwrite this config file
 * by adding your own config.php to SE_CONTENT_DIR (/content/config.php)
 */


/* default Language */
$languagePack = "en";
$lang = array();


/* allow image uploads in acp */
$se_upload_img_types = array('gif','jpg','jpe','jpeg','png','ico','webp');

/* allow file uploads in acp */
$se_upload_file_types = array('pdf','doc','docx','ppt','pptx','xls','xlsx','mp3','mp4','m4a','wav','mpg','mov','avi','epub','ogg');

/* page types */
$se_page_types = array('normal', 'register', 'profile', 'search', 'password', '404', 'display_post', 'display_product', 'display_event', 'imprint', 'privacy_policy', 'legal', 'checkout', 'orders');


/* define Folder structure */
const SE_ROOT = __DIR__ . DIRECTORY_SEPARATOR;
const SE_CONTENT = SE_ROOT . "content";
const SE_ACP = "acp";


/* database files if we use sqlite */
$se_db_content = SE_CONTENT . "/SQLite/content.sqlite3";
$se_db_posts = SE_CONTENT . "/SQLite/posts.sqlite3";
$se_db_user = SE_CONTENT . "/SQLite/user.sqlite3";
$se_db_index = SE_CONTENT . "/SQLite/index.sqlite3";


/**
 * folders for uploaded content
 * images and other files
 */
 
$img_path = "content/images";
$img_tmb_path = "content/images_tmb";
$files_path = "content/files";

/* deactivate the addons upload function */
$se_upload_addons = false;


$se_include_path = dirname($_SERVER['SCRIPT_NAME']);

if($se_include_path == "/") {
	$se_include_path = "";
}

if($se_include_path != "") {
	$se_include_path = "/$se_include_path";
	$se_include_path = str_replace('//','/',$se_include_path);
}

define('SE_INCLUDE_PATH',  $se_include_path);


if(is_file(SE_CONTENT . '/config.php')){
	include SE_CONTENT . '/config.php';
}

if(is_file(SE_CONTENT . '/config_smtp.php')){
	include SE_CONTENT . '/config_smtp.php';
}
