<?php
/**
 * SwiftyEdit - Free, Open Source, Content Management System
 * Main Application Bootstrap
 * GNU General Public License (license.txt)
 *
 * variables
 * @var array $se_settings settings from bootstrap.php
 * @var array $requestPathParts from routing.php
 * @var array $page_contents page data
 * @var array $se_nav navigation data
 * @var string $se_base_url base url
 * @var string $se_template template name / directory
 * @var string $themes_path path to themes
 * @var string $cache_id cache id
 * @var string $query query string
 * @var string $swifty_slug swifty slug
 * @var string $languagePack
 * @var array $lang
 * @var array $a_allowed_p
 * @var object $smarty Smarty template engine
 * @var int|null $error_code
 * @var array $page_json_ld
 *
 */

// Bootstrap the application
require_once __DIR__.'/bootstrap.php';

// Handle URL routing and load content
require_once __DIR__.'/routing.php';

// Initialize Smarty
require_once __DIR__.'/smarty.php';

// Handle XHR/API requests early
if ($requestPathParts[0] === 'xhr' OR $requestPathParts[0] === 'api') {
    require_once __DIR__.'/handlers/xhr-routes.php';
    // Exit happens in xhr-routes.php
}

// Determine page ID and type
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

// Handle preview mode
if(isset($_GET['preview']) AND ($_SESSION['user_class'] == "administrator")) {
    $p = (int) $_GET['preview'];
    list($page_contents,$se_nav) = se_get_content($p,'preview');
    unset($prefs_logfile);
}

// Security check
if(isset($p) && preg_match('/[^0-9A-Za-z]/', $p)) {
    die('void id');
}

// Load page content if needed
if(!is_array($page_contents) AND ($p != "")) {
    list($page_contents,$se_nav) = se_get_content($p);
}

// Fallback to homepage
if($p == "" OR $p == "portal") {
    list($page_contents,$se_nav) = se_get_content('portal','page_sort');
}

// Handle redirects
if($page_contents['page_redirect'] != '') {
    include_once __DIR__.'/tracker.php';
    $redirect = $page_contents['page_redirect'];
    $redirect_code = (int) $page_contents['page_redirect_code'];
    header("location: $redirect",TRUE,$redirect_code);
    exit;
}

// Load plugin/module content
if(!empty($page_contents['page_modul'])) {
    include SE_ROOT.'/plugins/'.basename($page_contents['page_modul']).'/frontend/index.php';
}

// Include template setup and smarty configuration
require_once __DIR__.'/template-setup.php';

$valid_page_types = ['register', 'account', 'profile', 'search', 'password', 'unlock',
    'checkout', 'orders', 'products', 'events', 'posts', 'tagged','download'];

if (in_array($page_contents['page_type_of_use'], $valid_page_types)) {
    $p = $page_contents['page_type_of_use'];
}

if(isset($_GET['code']) && $_GET['code'] != "") {
    $p = 'unlock';
}

// start download from public/assets/files/
if(isset($_POST['download'])) {
    $p = 'download';
}

foreach($valid_page_types as $handler) {
    if($p == $handler) {
        $handler_file = __DIR__.'/handlers/'.$handler.'.php';
        if(is_file($handler_file)) {
            include $handler_file;
        }
        break;
    }
}

// if we have no page id and the slug is not a valid page type, show 404
if (empty($page_contents['page_id']) AND !in_array(rtrim($swifty_slug, '/'), $valid_page_types)) {
    $error_code = 404;
}

// handle errors
if ($error_code !== null) {
    include __DIR__.'/error.php';
}

// Include theme options if available
if(is_file($themes_path.'/'.$se_template.'/php/options.php')) {
    include $themes_path.'/'.$se_template.'/php/options.php';
}

if(is_array($page_json_ld)) {
    $json_ld = json_encode($page_json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $smarty->assign('json_ld', $json_ld,true);
}

// Display the template
if($se_settings['maintenance_code'] != '') {
    if($_POST['maintenance-access-code'] == $se_settings['maintenance_code']) {
        $_SESSION['access_to_maintenance'] = 'permitted';
    }
    if($_SESSION['access_to_maintenance'] == 'permitted') {
        $smarty->display('index.tpl',$cache_id);
    } else {
        $smarty->display('maintenance.tpl', $cache_id);
    }
} else {
    $smarty->display('index.tpl',$cache_id);
}

// Track the hits
if(!isset($preview)) {
    include_once __DIR__.'/tracker.php';
}

se_do_frontend_hook('page.display.after', [
    'page_data' => $page_contents,
    'product_data' => $product_data ?? null,
    'post_data' => $post_data ?? null,
    'event_data' => $event_data ?? null,
    'query' => $swifty_slug ?? null,
    'session' => array_filter($_SESSION ?? [], function($key) {
        return !in_array($key, ['token', 'token_time','se_admin_helpers']);  // Exclude
    }, ARRAY_FILTER_USE_KEY)
]);