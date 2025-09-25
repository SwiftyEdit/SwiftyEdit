<?php
/**
 * SwiftyEdit - Free, Open Source, Content Management System
 * Main Application Bootstrap
 * GNU General Public License (license.txt)
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

// Handle 404 page
if($p == "404") {
    list($page_contents,$se_nav) = se_get_content('404','type_of_use');
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
    include SE_ROOT.'/plugins/'.basename($page_contents['page_modul']).'/index.php';
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

// Handle 404
if($p == "404") {
    header("HTTP/1.0 404 Not Found");
    header("Status: 404 Not Found");

    if($page_contents['page_permalink'] == '') {
        $smarty->assign('page_title', "404 Page Not Found");
        $output = $smarty->fetch("404.tpl");
        $smarty->assign('page_content', $output);
    }
    $show_404 = "false";
}

// Final 404 check
if((in_array("$p", $a_allowed_p)) OR ($p == "")) {
    $show_404 = "false";
}

if(isset($show_404) AND $show_404 == "true") {
    $output = $smarty->fetch("404.tpl");
    $smarty->assign('page_content', $output);
}

// Include theme options if available
if(is_file($themes_path.'/'.$se_template.'/php/options.php')) {
    include $themes_path.'/'.$se_template.'/php/options.php';
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