<?php

ini_set("url_rewriter.tags", '');
session_start();
error_reporting(0);


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

/**
 * if there is no database config we start the installer
 * @var string $se_db_content SQLite file from config.php or /content/config.php
 * @var string $database_host is set in config_database
 */

if(!is_file(SE_ROOT.'/config_database.php') && !is_file("$se_db_content")) {
    header("location: /install/");
    die();
}

/**
 * connect the database
 * @var string $db_content
 * @var string $db_user
 * @var string $db_posts
 */

require SE_ROOT.'/app/database.php';

if(empty($_SESSION['token'])) {
    se_generate_token();
}

$hidden_csrf_token = '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

/* stop all $_POST actions if csrf token is empty or invalid */
if(!empty($_POST)) {
    se_validate_token($_POST['csrf_token']);
}


// maintenance mode
if(is_file(SE_ROOT . "/maintenance.html")) {
    header("location:" . SE_INCLUDE_PATH . "/maintenance.html");
    die('maintenance mode');
}

/**
 * get the preferences
 * @var array $se_prefs (deprecated)
 * @var array $se_settings generate for global use
 */

$se_get_preferences = se_get_preferences();

foreach ($se_get_preferences as $k => $v) {
    $key = $se_get_preferences[$k]['option_key'];
    $value = $se_get_preferences[$k]['option_value'];
    $se_prefs[$key] = $value; // deprecated
    /* without the 'prefs_' prefix $se_prefs['pagetitle'] */
    if(substr($key,0,6) == 'prefs_') {
        $short_key = substr($key,6);
        $se_prefs[$short_key] = $value; // deprecated
        $se_settings[$short_key] = $value; // new
    }
}


if($se_settings['dateformat'] == '') {
    $se_settings['dateformat'] = 'Y-m-d';
}

if($se_settings['timeformat'] == '') {
    $se_settings['timeformat'] = 'H:i:s';
}

if($se_settings['timezone'] != '') {
    date_default_timezone_set($se_settings['timezone']);
}

// build absolute URL
if($se_settings['cms_ssl_domain'] != '') {
    $se_base_url = $se_settings['cms_ssl_domain'] . $se_settings['cms_base'];
} else {
    $se_base_url = $se_settings['cms_domain'] . $se_settings['cms_base'];
}

/**
 * include the language file
 * @var string $lang_sign en or de ...
 * @var string $lang_desc english or deutsch ...
 */
$lang_dir = $se_settings['default_language'];
require SE_ROOT.'languages/'.$lang_dir.'/index.php';
$languagePack = $lang_sign;

if(isset($_SESSION['user_class']) AND $_SESSION['user_class'] == "administrator") {
    $_SESSION['se_admin_helpers'] = array();
}

// translations
require SE_ROOT."languages/index.php";