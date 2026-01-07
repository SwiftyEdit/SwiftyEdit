<?php

require '../vendor/autoload.php';
use Medoo\Medoo;

const SE_SECTION = "backend";

if($_SESSION['user_class'] !== "administrator"){
    header("location:../../index.php");
    die("PERMISSION DENIED!");
}

require '../config.php';

if(is_file('../'.SE_CONTENT.'/config.php')) {
    include '../'.SE_CONTENT.'/config.php';
}

$loader = new \Twig\Loader\FilesystemLoader(SE_ROOT.'/acp/templates');
$twig = new \Twig\Environment($loader);

/**
 * connect the database
 * @var string $db_content
 * @var string $db_user
 * @var string $db_posts
 */

require SE_ROOT.'/app/database.php';

if(!empty($_POST) && $_POST['csrf_token'] !== $_SESSION['token']) {
    die('Error: CSRF Token is invalid');
}

require 'core/icons.php';
require_once 'core/functions.php';
require_once '../app/functions/functions.php';
include_once '../acp/core/templates.php';

$se_get_preferences = se_get_preferences();

foreach ($se_get_preferences as $k => $v) {
    $key = $se_get_preferences[$k]['option_key'];
    $value = $se_get_preferences[$k]['option_value'];

    /* $se_prefs['prefs_pagetitle'] */
    $se_prefs[$key] = $value;

    /* without the 'prefs_' prefix $se_prefs['pagetitle'] */
    if(substr($key,0,6) == 'prefs_') {
        $short_key = substr($key,6);
        //$se_prefs[$short_key] = $value;
        $se_settings[$short_key] = $value; // new
    }

}

/* build absolute URL */
if ($se_settings['cms_ssl_domain'] != '') {
    $se_base_url = $se_settings['cms_ssl_domain'] . $se_settings['cms_base'];
} else {
    $se_base_url = $se_settings['cms_domain'] . $se_settings['cms_base'];
}

/* set language */

$all_langs = get_all_languages();

if (!isset($_SESSION['lang'])) {
    if ($se_prefs['prefs_default_language'] != '') {
        $_SESSION['lang'] = $se_prefs['prefs_default_language'];
    } else {
        $_SESSION['lang'] = $languagePack;
    }
}

if (isset($_GET['set_lang'])) {
    $set_lang = sanitizeUserInputs($_GET['set_lang']);
    if (is_dir(SE_ROOT.'languages/'.$set_lang)) {
        $_SESSION['lang'] = "$set_lang";
    }
}

if (isset($_SESSION['lang'])) {
    $languagePack = basename($_SESSION['lang']);
}
require SE_ROOT.'/languages/'.$languagePack.'/index.php';
require SE_ROOT.'languages/index.php';

/**
 * $lang_codes (array) all available lang codes
 * hide languages from $prefs_deactivated_languages
 * all active languages are stored in $active_lang
 */
if (isset($se_prefs['prefs_deactivated_languages']) AND $se_prefs['prefs_deactivated_languages'] != '') {
    $arr_lang_deactivated = json_decode($se_prefs['prefs_deactivated_languages']);
}

foreach ($all_langs as $l) {
    if (isset($arr_lang_deactivated) && (in_array($l['lang_folder'], $arr_lang_deactivated))) {
        continue;
    }

    $langs[] = $l['lang_sign'];
}

$lang_codes = array_values(array_unique($langs));

if ($se_settings['timezone'] != '') {
    date_default_timezone_set($se_settings['timezone']);
}

$all_plugins = se_get_all_addons();

require_once SE_ROOT . 'app/hooks/hooks.php';
require_once SE_ROOT . 'app/hooks/hooks-meta.php';
require_once SE_ROOT . 'app/hooks/hooks-map-helper.php';
require_once SE_ROOT . 'app/hooks/hooks-backend.php';

// hooks - register meta information
foreach ($all_plugins as $pluginDir => $pluginData) {
    $metaPath = SE_ROOT . 'plugins/' . $pluginDir . '/hooks-backend/meta.php';
    if (!is_file($metaPath)) {
        continue;
    }

    // Load meta array from plugin file
    $meta = require $metaPath;

    // Skip invalid meta definitions
    if (!is_array($meta)) {
        continue;
    }

    // Register meta under plugin name (directory)
    se_register_backend_hook_meta($pluginDir, $meta);
}

// Load backend hook handlers for all plugins
foreach ($all_plugins as $pluginDir => $pluginData) {
    $backendHooksPath = SE_ROOT . 'plugins/' . $pluginDir . '/hooks-backend';
    if (!is_dir($backendHooksPath)) {
        continue;
    }

    foreach (glob($backendHooksPath . '/*.php') as $hookFile) {
        if (basename($hookFile) === 'meta.php') {
            continue;
        }
        require_once $hookFile;
    }
}


$twig->addGlobal('icon', $icon);
$twig->addGlobal('lang', $lang);
$twig->addGlobal('csrf_token', $_SESSION['token']);
$twig->addGlobal('se_settings', $se_settings);

$twig_globals = [
    'server_name' => $_SERVER['SERVER_NAME'],
    'request_uri' => $_SERVER['REQUEST_URI'],
];

$twig->addGlobal('global', $twig_globals);