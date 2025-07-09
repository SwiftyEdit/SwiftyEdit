<?php
/**
 * SwiftyEdit - Free, Open Source, Content Management System
 * GNU General Public License (license.txt)
 *
 * https://www.SwiftyEdit.com
 * support@SwiftyEdit.com
 */

require_once __DIR__.'/../app/bootstrap.php';


/**
 * reserved $_GET['p'] parameters
 */
$a_allowed_p = [
    'account',
    'checkout',
    'display_event',
    'display_post',
    'display_product',
    'logout',
    'orders',
    'password',
    'profile',
    'register',
    'search',
    'sitemap',
    'tagged',
    'unlock'
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



if($swifty_slug == '/' OR $swifty_slug == '') {
    list($page_contents,$se_nav) = se_get_content('portal','page_sort');
} else {
    list($page_contents,$se_nav) = se_get_content($swifty_slug,'permalink');
}

require SE_ROOT.'app/smarty.php';


// xhr routes for core /xhr/se/
// and plugins /xhr/plugins/plugin/
// and themes /xhr/themes/theme/
if ($requestPathParts[0] === 'xhr' OR $requestPathParts[0] === 'api') {
    if ($requestPathParts[1] === 'se') {
        // route for SwiftyEdit
        include SE_ROOT.'/app/xhr/route.php';
    } elseif ($requestPathParts[1] === 'plugins' && isset($requestPathParts[2])) {
        // route for (activated) plugins
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
    } elseif ($requestPathParts[1] === 'themes' && isset($requestPathParts[2])) {
        // route for themes
        $theme_name = basename($requestPathParts[2]);
        $theme_xhr = SE_PUBLIC.'/assets/themes/'.$theme_name.'/php/xhr.php';
        if(is_file($theme_xhr)) {
            include $theme_xhr;
        }
        exit;
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


/* if is set page_redirect, we can stop here and go straight to the desired location */
if($page_contents['page_redirect'] != '') {
    include_once 'app/tracker.php';
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

    if($p == 'password' || $p == 'profile' || $p == 'orders' || $p == 'account' || $p == 'register' || $p == 'unlock' || $p == 'tagged') {
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




if($se_prefs['prefs_posts_products_cart'] == 2 OR $se_prefs['prefs_posts_products_cart'] == 3) {

    $smarty->assign('show_shopping_cart',true);

    // add product to the shopping cart
    if(isset($_POST['add_to_cart'])) {
        $se_cart = se_add_to_cart();
    }

    // get permalink for shopping cart
    $checkout_page = se_get_type_of_use_pages('checkout');
    if($checkout_page['page_permalink'] == '') {
        $sc_uri = '/checkout/';
    } else {
        $sc_uri = '/'.$checkout_page['page_permalink'];
    }

    $smarty->assign('shopping_cart_uri', $sc_uri);
}


require '../app/switch.php';

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
    if(isset($store['products'])) {
        $store['products'] = array_unique($store['products']);
        $smarty->assign('admin_helpers_products', $store['products']);
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
    include_once '../app/tracker.php';
}