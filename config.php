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


/* allowed image uploads at the backend */
$se_upload_img_types = array('gif','jpg','jpe','jpeg','png','ico','webp');

/* allowed file uploads at the backend */
$se_upload_file_types = array('pdf','doc','docx','ppt','pptx','xls','xlsx','mp3','mp4','m4a','wav','mpg','mov','avi','epub','ogg');

/* allowed uploads at the frontend */
$se_upload_frontend_types = array('jpg','jpeg','png');

/* page types */
$se_page_types = array('normal', 'register', 'profile', 'search', 'password', '404', 'display_post', 'display_product', 'display_event', 'imprint', 'privacy_policy', 'legal', 'checkout', 'orders');

// limit login fails - integer || null
$se_failed_logins_limit = null;

/* define Folder structure */
const SE_ROOT = __DIR__ . DIRECTORY_SEPARATOR;
const SE_CONTENT = SE_ROOT . "data";
const SE_PUBLIC = SE_ROOT . "public";
const SE_ACP = "admin";

// url path for administration
$se_admin_url_path = '/admin/';
$se_admin_xhr_url_path = '/admin-xhr/';


/* database files if we use sqlite */
$se_db_content = SE_CONTENT . "/database/content.sqlite3";
$se_db_posts = SE_CONTENT . "/database/posts.sqlite3";
$se_db_user = SE_CONTENT . "/database/user.sqlite3";

/**
 * folders for uploaded content
 * images and other files
 */

$img_path = "assets/images";
$img_tmb_path = "assets/images_tmb";
$files_path = "assets/files";
$themes_path = "assets/themes";

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

/**
 * $se_mode 0 = self-hosting
 *          1 = self-hosting multisite
 *          2 = multisite hosting provided
 */
$se_mode = 0;

/**
 * $se_environment p = production
 *                 d = development
 */
$se_environment = 'p';

/**
 * limits
 * only relevant if you offer multisite hosting
 * limit the number of pages, snippets, posts ... that can be created
 * empty or integer values
 */
$se_limit_pages = '';
$se_limit_snippets = '';
$se_limit_shortcodes = '';
$se_limit_posts = '';
$se_limit_products = '';
$se_limit_features = '';
$se_limit_events = '';
$se_limit_uploads = '';
$se_limit_uploads_dir = ''; // filesize in MB
$se_limit_inbox = '';
$se_limit_users = '';
$se_limit_groups = '';
$se_limit_categories = '';
$se_limit_labels = '';


if(is_file(SE_CONTENT . '/config.php')) {
	include SE_CONTENT . '/config.php';
}

if(is_file(SE_CONTENT . '/config_smtp.php')) {
	include SE_CONTENT . '/config_smtp.php';
}