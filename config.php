<?php

/**
 * SwiftyEdit default Configuration file
 * this file will be replaced with every update
 *
 * you can expand/overwrite this config file
 * by adding your own config.php to SE_CONTENT/config.php
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
$se_page_types = array('normal', 'register', 'profile', 'search', 'password', '404', 'display_post', 'display_product', 'display_event', 'imprint', 'privacy_policy', 'legal', 'checkout', 'orders', 'order_withdrawal', 'wishlist', 'tagged');

/* Lock an account after this many failed logins in a row (integer, or null
 * to switch it off). The lock lasts 1 minute and doubles with every further
 * failed attempt, up to 60 minutes; the owner gets a mail with an unlock link.
 * One failed attempt is forgotten per 10 minutes. To change it, add to
 * SE_CONTENT/config.php e.g.:
 * $se_failed_logins_limit = 10;   or   $se_failed_logins_limit = null;
 */
$se_failed_logins_limit = 5;

/* define Folder structure */
const SE_ROOT = __DIR__ . DIRECTORY_SEPARATOR;
const SE_CONTENT = SE_ROOT . "data";
const SE_PUBLIC = SE_ROOT . "public";
const SE_PLUGINS = SE_ROOT . "plugins";
const SE_THEMES = SE_PUBLIC . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "themes";
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

/* site branding (logo, page thumbnail, favicon set) - kept separate from
 * assets/images so it never shows up in the media library / uploads browser */
$se_branding_path = "assets/branding";

/* Plugin/Addon upload - disabled by default for security reasons
 * To enable, add the following to SE_CONTENT/config.php:
 * $se_upload_addons = true;
 */
$se_upload_addons = false;

/* Two-factor authentication for the backend - disabled by default.
 * To require it for every backend user, add the following to SE_CONTENT/config.php:
 * $se_2fa_required = true;
 *
 * Emergency (e.g. the only admin lost access to the second factor): skip 2FA
 * for one user until the given date (site timezone from the settings) -
 * remove it again after logging in:
 * $se_2fa_bypass = ['user' => 'username', 'until' => '2026-12-31 12:00'];
 */
$se_2fa_required = false;
$se_2fa_bypass = null;

/* Every login ends after this many seconds, however active the session is
 * (default 12 hours, 0 = no limit). A new password, withdrawn rights or a
 * deactivated account also end all other sessions of that account.
 * To change it, add the following to SE_CONTENT/config.php:
 * $se_session_max_lifetime = 28800;
 */
$se_session_max_lifetime = 43200;

/* Updates replace the SwiftyEdit core block of public/.htaccess (own rules
 * before and after it are kept, the previous file is saved to SE_CONTENT/backups/).
 * If the file is managed elsewhere, add the following to SE_CONTENT/config.php
 * so updates leave it alone:
 * $se_update_htaccess = false;
 */
$se_update_htaccess = true;

/* The update in the backend downloads and installs new versions from
 * swiftyedit.net. If you update by hand (e.g. via FTP/deployment) and want
 * to switch it off, add the following to SE_CONTENT/config.php:
 * $se_backend_updates = false;
 * New versions are still shown, the database update via /install/ still works.
 */
$se_backend_updates = true;

/* Sources whose iframes (videos, maps ...) are kept when HTML from the editor
 * is cleaned (se_purify_html()). Only https, the host must match exactly.
 * An entry with a path ("host/path") only allows addresses starting with it.
 * To allow more, add the following to SE_CONTENT/config.php:
 * $se_embed_hosts[] = 'www.facebook.com/plugins/';
 */
$se_embed_hosts = [
	'www.youtube.com',
	'www.youtube-nocookie.com',
	'player.vimeo.com',
	'www.google.com/maps/embed',
	'www.openstreetmap.org/export/embed.html'
];


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

$se_bot_list = [
    'googlebot','bingbot','slurp','duckduckbot','baiduspider',
    'yandex','sogou','exabot','facebot','ia_archiver',
    'semrush','ahrefs','mj12bot'
];


if(is_file(SE_CONTENT . '/config.php')) {
	include SE_CONTENT . '/config.php';
}

if(is_file(SE_CONTENT . '/config_smtp.php')) {
	include SE_CONTENT . '/config_smtp.php';
}

/* Permissions for folders SwiftyEdit creates (cache, uploads, thumbnails).
 * If the web server and your FTP user are different accounts and you need
 * write access to these folders via FTP, add to SE_CONTENT/config.php:
 * define('SE_DIR_PERMISSIONS', 0775);
 */
if(!defined('SE_DIR_PERMISSIONS')) {
	define('SE_DIR_PERMISSIONS', 0755);
}