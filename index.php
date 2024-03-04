<?php
/**
 * SwiftyEdit - Free, Open Source, Content Management System
 * GNU General Public License (license.txt)
 *
 * https://www.SwiftyEdit.com
 * support@SwiftyEdit.com
 */

ini_set("url_rewriter.tags", '');
session_start();
error_reporting(0);
//error_reporting(E_ALL ^E_NOTICE);
header("X-Frame-Options: SAMEORIGIN");

$se_start_time = microtime(true);

require 'core/vendor/autoload.php';

/**
 * include the default config file
 * @var string $languagePack
 * @var array $se_page_types
 */
require 'config.php';

/* resets */
$prepend_head_code = '';
$append_head_code = '';
$prepend_body_code = '';
$append_body_code = '';
$mod_slug = '';

const SE_SECTION = "frontend";

if(empty($_SESSION['token'])) {
	$_SESSION['token'] = md5(uniqid(rand(), TRUE));
    $_SESSION['token_time'] = time();
}

$hidden_csrf_token = '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

/* stop all $_POST actions if csrf token is empty or invalid */
if(!empty($_POST)) {
    if(empty($_POST['csrf_token'])) {
        die('Error: CSRF Token is empty');
    }
    if($_POST['csrf_token'] !== $_SESSION['token']) {
        die('Error: CSRF Token is invalid');
    }
}


/**
 * if there is no database config we start the installer
 * @var string $se_db_content SQLite file from config.php or /content/config.php
 * @var string $database_host is set in config_database
 */

if(!is_file('config_database.php') && !is_file("$se_db_content")) {
	header("location: /install/");
	die();
}

/**
 * connect the database
 * @var string $db_content
 * @var string $db_user
 * @var string $db_statistics
 * @var string $db_posts
 */

require SE_ROOT.'/database.php';


/**
 * maintenance mode
 */

if(is_file(SE_ROOT . "/maintenance.html")) {
	header("location:" . SE_INCLUDE_PATH . "/maintenance.html");
	die("We'll be back soon.");
}

/**
 * get the preferences
 * @var array $se_prefs generate for global use
 * the most important for the frontend are
 * default information and/or metas
 * $se_prefs['prefs_pagename'] $se_prefs['prefs_pagetitle'] $se_prefs['prefs_pagesubtitle'] $se_prefs['prefs_pagedescription'] $se_prefs['prefs_pagefavicon']
 * language
 * $se_prefs['prefs_default_language']
 * user management
 * $se_prefs['prefs_userregistration'] $se_prefs['prefs_showloginform']
 * templates
 * $se_prefs['prefs_template'] $se_prefs['prefs_template_layout'] $se_prefs['prefs_template_stylesheet']
 */

$se_get_preferences = se_get_preferences();


foreach($se_get_preferences as $value) {
    $option_key = $value['option_key'];
    $option_value = $value['option_value'];
    $se_prefs[$option_key] = $option_value;
}

if($se_prefs['prefs_dateformat'] == '') {
    $se_prefs['prefs_dateformat'] = 'Y-m-d';
}

if($se_prefs['prefs_timeformat'] == '') {
    $se_prefs['prefs_timeformat'] = 'H:i:s';
}


/**
 * include the language file
 * @var string $lang_sign en or de ...
 * @var string $lang_desc english or deutsch ...
 */
$lang_dir = $se_prefs['prefs_default_language'];
include 'core/lang/'.$lang_dir.'/index.php';
$languagePack = $lang_sign;

if(isset($_SESSION['user_class']) AND $_SESSION['user_class'] == "administrator") {
	$_SESSION['se_admin_helpers'] = array();
}

/**
 * reserved $_GET['p'] parameters
 */
$a_allowed_p = array(
    'register',
    'account',
    'profile',
    'search',
    'sitemap',
    'logout',
    'password',
    'display_post',
    'display_product',
    'display_event',
    'checkout',
    'orders'
);

/*
 * mod_rewrite
 * $query defined by the .htaccess file
 * RewriteRule ^(.*)$ index.php?query=$1 [L,QSA]
 *
 */

if(isset($_GET['query'])) {
    $query = se_clean_query($_GET['query']);
}

if(!isset($query)) {
	$query = '/';
}

if(is_file(SE_CONTENT.'/plugins/query.controller.php')) {
	include SE_CONTENT.'/plugins/query.controller.php';
}

if($query == 'logout' OR (isset($_GET['goto']) && ($_GET['goto'] == 'logout'))) {
	$user_logout = se_end_user_session();
	$query = '/';
}

$swifty_slug = $query;

$active_mods = se_get_active_mods();
$cnt_active_mods = count($active_mods);

/**
 * get existing url from cache file
 * @var array $existing_url
 */

if(is_file(SE_CONTENT . '/cache/active_urls.php')) {
    include SE_CONTENT . '/cache/active_urls.php';
}
$query_is_cached = false;
if(in_array("$query", (array) $existing_url)) {
    $query_is_cached = true;
}

/**
 * loop through installed modules
 */

for($i=0;$i<$cnt_active_mods;$i++) {
	
	$mod_permalink = $active_mods[$i]['page_permalink'];
	$mod_name = $active_mods[$i]['page_modul'];
	$permalink_length = strlen($mod_permalink);
	
	if(!empty($mod_permalink) && strpos("$query", "$mod_permalink") !== false) {
				
		if(strncmp($mod_permalink, $query, $permalink_length) == 0) {
			$mod_slug = substr($query, $permalink_length);
			$swifty_slug = substr("$query",0,$permalink_length);
			if($query_is_cached) {
  				$swifty_slug = $query;
			}
		}
	}	
}


if($swifty_slug == '/' OR $swifty_slug == '') {
	list($page_contents,$se_nav) = se_get_content('portal','page_sort');
} else {
	list($page_contents,$se_nav) = se_get_content($swifty_slug,'permalink');
}

/* include (once) modules index.php if exists */
foreach($active_mods as $mods) {
    $clean_mods[] = $mods['page_modul'];
}

if(is_array($clean_mods)) {
    $clean_mods = array_unique($clean_mods);

    foreach ($clean_mods as $mod_dir) {
        if (is_file(SE_CONTENT . '/modules/' . $mod_dir . '/global/index.php')) {
            include_once SE_CONTENT . '/modules/' . $mod_dir . '/global/index.php';
        }
    }
}

$p = $page_contents['page_id'];

if($p == "") {
	$p = "404";					
	foreach($a_allowed_p as $param) {
		if($query == "$param/") {
			$p = "$param";
		}
	}
	
	se_check_funnel_uri($swifty_slug);
	se_check_shortlinks($swifty_slug);
}

/**
 * show preview
 */

if(isset($_GET['preview']) AND ($_SESSION['user_class'] == "administrator")) {
	$p = (int) $_GET['preview'];
	list($page_contents,$se_nav) = se_get_content($p,'preview');
	unset($prefs_logfile);
}


if(isset($p) && preg_match('/[^0-9A-Za-z]/', $p)) {
	die('void id');
}


if(!is_array($page_contents) AND ($p != "")) {
	list($page_contents,$se_nav) = se_get_content($p);
}


/* no page contents -> switch to the homepage */
if($p == "" OR $p == "portal") {
	list($page_contents,$se_nav) = se_get_content('portal','page_sort');
}

/**
 * 404 page
 * if there is a page with type_of_use == 404, get the data
 * if not, we use the 404.tpl file
 */
if($p == "404") {
	list($page_contents,$se_nav) = se_get_content('404','type_of_use');
}


if($page_contents['page_type_of_use'] == 'register') {
	$p = 'register';
}

if($page_contents['page_type_of_use'] == 'checkout') {
	$p = 'checkout';
}

if($page_contents['page_type_of_use'] == 'orders') {
	$p = 'orders';
}


/* build absolute URL */
if($se_prefs['prefs_cms_ssl_domain'] != '') {
	$se_base_url = $se_prefs['prefs_cms_ssl_domain'] . $se_prefs['prefs_cms_base'];
} else {
	$se_base_url = $se_prefs['prefs_cms_domain'] . $se_prefs['prefs_cms_base'];
}


/* if is set page_redirect, we can stop here and go straight to the desired location */
if($page_contents['page_redirect'] != '') {
	include_once 'core/tracker.php';
	$redirect = $page_contents['page_redirect'];
	$redirect_code = (int) $page_contents['page_redirect_code'];
	header("location: $redirect",TRUE,$redirect_code);
	exit;
}


/* default $languagePack is defined in config.php */
if(is_dir("core/lang/$page_contents[page_language]") AND ($page_contents['page_language'] != '')) {
	$languagePack = $page_contents['page_language'];
}

/* include language */
require SE_ROOT . "/core/lang/index.php";




if(!empty($page_contents['page_modul'])) {
	include SE_CONTENT.'/modules/'.basename($page_contents['page_modul']).'/index.php';
}

/* START SMARTY */
//require_once('lib/Smarty/Smarty.class.php');
$smarty = new Smarty;
$smarty->setErrorReporting(0);
$smarty->compile_dir = 'content/cache/templates_c/';
$smarty->cache_dir = 'content/cache/cache/';
$cache_id = md5($swifty_slug.$mod_slug);

if($se_prefs['prefs_smarty_cache'] == 1) {
	$smarty->setCaching(Smarty::CACHING_LIFETIME_CURRENT);
	if(is_numeric($se_prefs['prefs_smarty_cache_lifetime'])) {
		$smarty->setCacheLifetime($se_prefs['prefs_smarty_cache_lifetime']);
	}
} else {
	$smarty->setCaching(Smarty::CACHING_OFF);
}

if($se_prefs['prefs_smarty_compile_check'] == 1) {
	$smarty->compile_check = true;
} else {
	$smarty->compile_check = false;
}

/* reset of the user-defined theme */
if(isset($_POST['reset_theme'])) {
	unset($_SESSION['prefs_template'],$_SESSION['prefs_template_stylesheet']);
}

/**
 * $prefs_usertemplate - off|on|overwrite
 * this option is intended for theme developers
 */

if($se_prefs['prefs_usertemplate'] == 'on' OR $se_prefs['prefs_usertemplate'] == 'overwrite') {
	
	/* set the theme - defined by the user */
	if(isset($_POST['set_theme'])) {
		$set_theme = 'styles/'.sanitizeUserInputs($_POST['set_theme']);
		if(is_dir($set_theme)) {
			$_SESSION['prefs_template'] = sanitizeUserInputs($_POST['set_theme']);
			unset($_SESSION['prefs_template_stylesheet']);
		}
	}
	
	/**
	 * set the theme and stylesheet - defined by the user
	 * example: $_POST['set_theme_stylesheet'] = './styles/default/css/dark.css';
	 */
	 
	if(isset($_POST['set_theme_stylesheet'])) {
		$set_theme_stylesheet = explode("/",$_POST['set_theme_stylesheet']);
		
		$set_theme_folder = $set_theme_stylesheet[2];
		$set_stylesheet = $set_theme_stylesheet[4];
		
		if(is_dir("./styles/$set_theme_folder")) {
			$_SESSION['prefs_template'] = sanitizeUserInputs($set_theme_folder);
		}
		
		if(is_file("./styles/$set_theme_folder/css/$set_stylesheet")) {
			$_SESSION['prefs_template_stylesheet'] = sanitizeUserInputs($set_stylesheet);
		}
	}
	
	
	if($_SESSION['prefs_template'] != '') {
        $se_prefs['prefs_template'] = $_SESSION['prefs_template'];
	}
	
	if($_SESSION['prefs_template_stylesheet'] != '') {
        $se_prefs['prefs_template_stylesheet'] = $_SESSION['prefs_template_stylesheet'];
	}

}

// default template
$se_template = $se_prefs['prefs_template'] ?: 'default';
$se_template_layout = $se_prefs['prefs_template_layout'] ?: 'layout_default.tpl';
$se_template_stylesheet = '';
if(isset($se_prefs['prefs_template_stylesheet'])) {
    $se_template_stylesheet = $se_prefs['prefs_template_stylesheet'];
}

if($page_contents['page_template'] == "use_standard") {
	$se_template = $se_prefs['prefs_template'] ?: 'default';
}

if($page_contents['page_template_layout'] == "use_standard") {
	$se_template_layout = $se_prefs['prefs_template_layout'] ?: 'layout_default.tpl';
}

/* page has its own theme/template */
if(is_dir('styles/'.$page_contents['page_template'].'/templates/')) {
	$se_template = $page_contents['page_template'];
	$se_template_layout = $page_contents['page_template_layout'];
	$se_template_stylesheet = $page_contents['page_template_stylesheet'];

	if($se_prefs['prefs_usertemplate'] == 'overwrite') {
		/* the user theme has the same tpl file, so we can overwrite */
		if(is_file('./styles/'.$_SESSION['prefs_template'].'/templates/'.$page_contents['page_template_layout'])) {
			$se_template = $_SESSION['prefs_template'];
			$se_template_layout = $page_contents['page_template_layout'];
			//$se_template_stylesheet = $se_template_stylesheet;
		}
	}
}

$se_template = basename($se_template);
$se_template_layout = basename($se_template_layout);
$se_template_stylesheet = basename($se_template_stylesheet);

$smarty->assign('hidden_csrf_token', "$hidden_csrf_token", true);

$smarty->assign('se_template', $se_template);
$smarty->assign('se_template_layout', $se_template_layout);

if($se_template_stylesheet != '') {
	$smarty->assign('se_template_stylesheet', $se_template_stylesheet);
}

if(is_file("styles/$se_template/php/index.php")) {
	include 'styles/'.$se_template.'/php/index.php';
}

$smart_template_dirs = array();

if($se_template != 'default') {
    $smart_template_dirs[] = 'styles/'.$se_template.'/templates/';
    $smart_template_dirs[] = 'styles/default/templates/';
} else {
    $smart_template_dirs[] = 'styles/default/templates/';
}

//$smarty->template_dir = 'styles/'.$se_template.'/templates/';
$smarty->template_dir = $smart_template_dirs;

/**
 * assign all translations to smarty
 * @var array $lang
 */
foreach($lang as $key => $val) {
	$smarty->assign("lang_$key", $val);
}

foreach($se_prefs as $key => $val) {
	$smarty->assign("$key", $val);
}

/**
 * check if we have 'page_posts_type' then display posts
 * check if we have 'page_type_of_use'
 */

if($page_contents['page_posts_types'] != '' OR $page_contents['page_type_of_use'] != 'normal') {
    $show_posts = true;

    foreach($se_page_types as $type) {
        if($page_contents['page_type_of_use'] == $type) {
            $show_posts = false;
        }
    }

    if($p == 'password' || $p == 'profile' || $p == 'orders' || $p == 'account' || $p == 'register') {
        $show_posts = false;
    }

    if($page_contents['page_posts_types'] != '') {
        $show_posts = true;
    }

    if($page_contents['page_posts_types'] == 'p' OR $page_contents['page_type_of_use'] == 'display_product') {
        $p = 'products';
        $show_posts = false;
    }
    if($page_contents['page_posts_types'] == 'e' OR $page_contents['page_type_of_use'] == 'display_event') {
        $p = 'events';
        $show_posts = false;
    }

    if($page_contents['page_type_of_use'] == 'display_post') {
        $p = 'posts';
    }

    if($page_contents['page_type_of_use'] == 'checkout') {
        $p = 'checkout';
    }

    if($show_posts === true) {
        $p = 'posts';
    }
}


$tyo_search = se_get_type_of_use_pages('search');
$smarty->assign("search_uri", '/'.$tyo_search['page_permalink']);

/* legal pages */
$legal_pages = se_get_legal_pages();
$cnt_legal_pages = count($legal_pages);
if($cnt_legal_pages > 0) {
	$smarty->assign('legal_pages', $legal_pages);
}



$smarty->assign('languagePack', $languagePack);
$smarty->assign("page_id", $page_contents['page_id']);

if(isset($user_logout) && ($user_logout != '')) {
	$smarty->assign("msg_status","alert alert-success",true);
	$smarty->assign('msg_text', $lang['msg_logout'],true);
	$output = $smarty->fetch("status_message.tpl");
	$smarty->assign('msg_content', $output);
}

/* get permalink for orders page */
$orders_page = se_get_type_of_use_pages('orders');
if($orders_page == NULL OR $orders_page['page_permalink'] == '') {
	$orders_uri = '/orders/';
} else {
	$orders_uri = '/'.$orders_page['page_permalink'];
}

$smarty->assign('orders_uri', $orders_uri);


if($se_prefs['prefs_posts_products_cart'] == 2 OR $se_prefs['prefs_posts_products_cart'] == 3) {
	/* add product to the shopping cart */
	if(isset($_POST['add_to_cart'])) {
		$se_cart = se_add_to_cart();
	}
	
	/* get permalink for shopping cart */
	$checkout_page = se_get_type_of_use_pages('checkout');
	if($checkout_page['page_permalink'] == '') {
		$sc_uri = '/checkout/';
	} else {
		$sc_uri = '/'.$checkout_page['page_permalink'];
	}
	
	$smarty->assign('shopping_cart_uri', $sc_uri);
	
	/* amount of items in the shopping cart */
	$cnt_items = se_return_cart_amount();
	if($cnt_items > 0) {
		$smarty->assign('cnt_shopping_cart_items', $cnt_items);
	}
}


require 'core/user_management.php';
require 'core/switch.php';

if(is_file('styles/'.$se_template.'/php/options.php')) {
	include 'styles/'.$se_template.'/php/options.php';
}

$smarty->assign("p","$p");
$smarty->assign("se_include_path", SE_INCLUDE_PATH);

$se_page_url = $se_base_url;
$se_base_href = $se_base_url;
if($swifty_slug != '' AND $swifty_slug != '/') {
	$se_page_url .= $swifty_slug;
}
if($mod_slug != '') {
	$se_page_url .= $mod_slug;
}
$smarty->assign('se_base_href', $se_base_href,true);
$smarty->assign('se_page_url', $se_page_url,true);

$se_end_time = microtime(true);
$se_pageload_time = round($se_end_time-$se_start_time,4);
$smarty->assign('se_start_time', $se_start_time,true);
$smarty->assign('se_end_time', $se_end_time,true);
$smarty->assign('se_pageload_time', $se_pageload_time,true);

$smarty->assign('prepend_head_code', $prepend_head_code);
$smarty->assign('append_head_code', $append_head_code);
$smarty->assign('prepend_body_code', $prepend_body_code);
$smarty->assign('append_body_code', $append_body_code);

$store = '';
if(isset($_SESSION['user_class']) AND $_SESSION['user_class'] == "administrator") {
	$store = $_SESSION['se_admin_helpers'];

	if(isset($store['snippet'])) {
		$smarty->assign('admin_helpers_snippets', $store['snippet']);
	}
	if(isset($store['plugin'])) {
		$store['plugin'] = array_unique($store['plugin']);
		$smarty->assign('admin_helpers_plugins', $store['plugin']);
	}
	if(isset($store['shortcodes'])) {
		$store['shortcodes'] = array_unique($store['shortcodes']);
		$smarty->assign('admin_helpers_shortcodes', $store['shortcodes']);
	}
	if(isset($store['images'])) {
		$store['images'] = array_unique($store['images']);
		$smarty->assign('admin_helpers_images', $store['images']);
	}
	if(isset($store['files'])) {
		$store['files'] = array_unique($store['files']);
		$smarty->assign('admin_helpers_files', $store['files']);
		
	}
}

if($se_prefs['prefs_maintenance_code'] != '') {
    if($_POST['maintenance-access-code'] == $se_prefs['prefs_maintenance_code']) {
        $_SESSION['access_to_maintenance'] = 'permitted';
    }
    if($_SESSION['access_to_maintenance'] == 'permitted') {
        $smarty->display('index.tpl',$cache_id);
    } else {
        $smarty->display('maintenance.tpl', $cache_id);
    }
} else {
    // display the template
    $smarty->display('index.tpl',$cache_id);
}




/* track the hits */
if(!isset($preview)) {
	include_once 'core/tracker.php';
}