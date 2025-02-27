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
header("X-Frame-Options: SAMEORIGIN");

$se_start_time = microtime(true);

require '../vendor/autoload.php';
use Smarty\Smarty;
/**
 * include the default config file
 * @var string $languagePack
 * @var array $se_page_types
 */
require '../config.php';

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

if(!is_file('../config_database.php') && !is_file("$se_db_content")) {
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

require SE_ROOT.'/core/database.php';

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

foreach ($se_get_preferences as $k => $v) {
    $key = $se_get_preferences[$k]['option_key'];
    $value = $se_get_preferences[$k]['option_value'];
    $se_prefs[$key] = $value;
    /* without the 'prefs_' prefix $se_prefs['pagetitle'] */
    if(substr($key,0,6) == 'prefs_') {
        $short_key = substr($key,6);
        $se_prefs[$short_key] = $value; // old
        $se_settings[$short_key] = $value; // new
    }
}


if($se_prefs['prefs_dateformat'] == '') {
    $se_prefs['prefs_dateformat'] = 'Y-m-d';
}

if($se_prefs['prefs_timeformat'] == '') {
    $se_prefs['prefs_timeformat'] = 'H:i:s';
}

if ($se_prefs['prefs_timezone'] != '') {
    date_default_timezone_set($se_prefs['prefs_timezone']);
}

/**
 * include the language file
 * @var string $lang_sign en or de ...
 * @var string $lang_desc english or deutsch ...
 */
$lang_dir = $se_prefs['prefs_default_language'];
require SE_ROOT.'languages/'.$lang_dir.'/index.php';
$languagePack = $lang_sign;

if(isset($_SESSION['user_class']) AND $_SESSION['user_class'] == "administrator") {
    $_SESSION['se_admin_helpers'] = array();
}


/**
 * reserved $_GET['p'] parameters
 */
$a_allowed_p = [
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
];

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

/**
 * query.controller.php
 * This is a very old include function.
 * However, we are leaving it in place for reasons of compatibility
 */
if(is_file(SE_CONTENT.'/includes/query.controller.php')) {
    include SE_CONTENT.'/includes/query.controller.php';
}

if($query == 'logout' OR (isset($_GET['goto']) && ($_GET['goto'] == 'logout'))) {
    $user_logout = se_end_user_session();
    $query = '/';
}

$swifty_slug = $query;
$requestPathParts = explode('/', trim($swifty_slug, '/'));

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
    $active_plugins[] = $active_mods[$i]['page_modul'];
    $permalink_length = strlen($mod_permalink);

    if(!empty($mod_permalink) && str_contains("$query", "$mod_permalink")) {

        if(strncmp($mod_permalink, $query, $permalink_length) == 0) {
            $mod_slug = substr($query, $permalink_length);
            $swifty_slug = substr("$query",0,$permalink_length);
            if($query_is_cached) {
                $swifty_slug = $query;
            }
        }
    }
}

require SE_ROOT."languages/index.php";

if($swifty_slug == '/' OR $swifty_slug == '') {
    list($page_contents,$se_nav) = se_get_content('portal','page_sort');
} else {
    list($page_contents,$se_nav) = se_get_content($swifty_slug,'permalink');
}

require SE_ROOT.'core/smarty.php';


// xhr routes for core /api/se/ and plugins /api/plugins/plugin/
if ($requestPathParts[0] === 'api') {
    if ($requestPathParts[1] === 'se') {
        // route for SwwiftyEdit
        include SE_ROOT.'/core/xhr/route.php';
    } elseif ($requestPathParts[1] === 'plugins' && isset($requestPathParts[2])) {
        // route for plugins
        // check if plugin is activated (in $active_mods)
        $plugin_name = basename($requestPathParts[2]);
        if(in_array($plugin_name, $active_plugins)) {
            $plugin_xhr = SE_ROOT.'/plugins/'.$plugin_name.'/global/xhr.php';
            if(is_file($plugin_xhr)) {
                include $plugin_xhr;
            }
            exit;
        } else {
            exit;
        }
    } else {
        http_response_code(404);
        exit;
    }
}

/* include (once) modules global/index.php if exists */
foreach($active_mods as $mods) {
    $clean_mods[] = $mods['page_modul'];
}

if(is_array($clean_mods)) {
    $clean_mods = array_unique($clean_mods);

    foreach ($clean_mods as $mod_dir) {
        if (is_file(SE_ROOT . '/plugins/' . $mod_dir . '/global/index.php')) {
            include_once SE_ROOT . '/plugins/' . $mod_dir . '/global/index.php';
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





if(!empty($page_contents['page_modul'])) {
    include SE_ROOT.'/plugins/'.basename($page_contents['page_modul']).'/index.php';
}




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


require '../core/user_management.php';
require '../core/switch.php';

if(is_file($themes_path.'/'.$se_template.'/php/options.php')) {
    include $themes_path.'/'.$se_template.'/php/options.php';
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
    include_once '../core/tracker.php';
}