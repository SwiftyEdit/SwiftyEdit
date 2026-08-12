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

// only show errors when explicitly running in development mode
if ($se_environment === 'd') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

$loader = new \Twig\Loader\FilesystemLoader(SE_ROOT.'/acp/templates');
$twig = new \Twig\Environment($loader, [
    'cache' => SE_CONTENT.'/cache/twig',
    // In dev, recompile on every change; in production, trust the cache
    // (mirrors the $se_environment check in acp/core/update/functions.php).
    'auto_reload' => $se_environment === 'd',
]);

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

// Scan plugins/ once and reuse the result below (hooks registration) and in
// se_bootstrap_editor_plugins() - both used to call se_get_all_addons() on
// their own, scanning the directory and re-parsing every info.json twice
// per request.
$all_plugins = se_get_all_addons();

/*
 * Populate the content-format editor registry (se_register_editor()) for
 * this request - needed both by data-writer.php (se_save_page()/
 * se_update_page() call se_freeze_editor_content() at save time) and
 * data-reader.php (rebuilding the content field on a format-switch). Mirrors
 * the same call in acp/index.php's own bootstrap.
 */
$se_editor_addons = se_bootstrap_editor_plugins($all_plugins);

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
    $set_lang = se_sanitize_lang_input($_GET['set_lang']);
    if ($set_lang !== '' && is_dir(SE_ROOT.'languages/'.$set_lang)) {
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

// Load global hook handlers (fire on both frontend and backend triggers)
// for all plugins
require_once SE_ROOT . 'app/hooks/hooks-global.php';

foreach ($all_plugins as $pluginDir => $pluginData) {
    $globalHooksPath = SE_ROOT . 'plugins/' . $pluginDir . '/hooks-global';
    if (!is_dir($globalHooksPath)) {
        continue;
    }

    foreach (glob($globalHooksPath . '/*.php') as $hookFile) {
        // Each file registers callbacks via se_add_global_hook(...)
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