<?php

/**
 * SwiftyEdit - backend main file
 *
 * global variables
 * @var array $lang from language files
 * @var string $languagePack
 * @var string $lang_sign from languages/%lang%/index.php
 * @var string $lang_desc from languages/%lang%/index.php
 * @var string $languagePackFallback from languages/%lang%/index.php
 * @var array $icon from icons.php
 * @var array $se_prefs preferences
 *
 * from config
 * @var string $se_db_content filepath to sqlite file
 * @var string $se_db_user filepath to sqlite file
 * @var string $se_db_posts filepath to sqlite file
 * @var string $img_path filepath to image/uploads directory
 * @var string $files_path filepath to files upload directory
 *
 * from versions.php
 * @var string $se_version_title SwiftyEdit version title
 * @var string $se_version_build build number
 *
 * from editors.php
 * @var string $editor_css_url        active theme editor content CSS URL
 * @var string $editor_img_list_url   TinyMCE image-list endpoint
 * @var string $editor_link_list_url  TinyMCE link-list endpoint
 *
 * from config.php
 * @var string $se_environment
 *
 * built here
 * @var array $se_settings preferences without the 'prefs_' prefix
 * @var object $twig from acp/core/twig.php
 *
 * The page frame is acp/templates/layout/page.twig, see the end of this file.
 */

require_once __DIR__.'/../app/session.php';
se_session_start();
const SE_SECTION = "backend";

require '../vendor/autoload.php';

use Medoo\Medoo;

$purifier_config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($purifier_config);

require '../acp/core/icons.php';

require '../config.php';
if (is_file(SE_CONTENT . '/config.php')) {
    include SE_CONTENT . '/config.php';
}
if (is_file(SE_CONTENT . '/config_smtp.php')) {
    include SE_CONTENT . '/config_smtp.php';
}

// only show errors when explicitly running in development mode
if ($se_environment === 'd') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

$version_file = file_get_contents(SE_ROOT.'version.json');
$se_version = json_decode($version_file, true);


/**
 * connect the database
 * @var string $db_content
 * @var string $db_user
 * @var string $db_posts
 */

require SE_ROOT.'/app/database.php';

define("IMAGES_FOLDER", "$img_path");
define("FILES_FOLDER", "$files_path");


require '../acp/core/functions.php';

// absolute lifetime / account changed since the login - back to the login
if (!se_validate_user_session()) {
    header("location:/admin/");
    exit;
}

require '../acp/core/access.php';

if (!isset($_SESSION['editor_class'])) {
    $_SESSION['editor_class'] = "wysiwyg";
}

/* switch editor - plain text or wysiwyg */
if (isset($_GET['editor'])) {

    if ($_GET['editor'] == 'wysiwyg') {
        $_SESSION['editor_class'] = "wysiwyg";
    } elseif ($_GET['editor'] == 'plain') {
        $_SESSION['editor_class'] = "plain";
    } else {
        $_SESSION['editor_class'] = "code";
    }

}

if ($_SESSION['editor_class'] == "wysiwyg") {
    $editor_class = "mceEditor";
    $editor_small_class = "mceEditor_small";
} elseif ($_SESSION['editor_class'] == "plain") {
    $editor_class = "plain";
    $editor_small_class = "plain";
} else {
    $editor_class = "aceEditor_html";
    $editor_small_class = "aceEditor_html";
}



if(isset($_GET['query'])) {
    $query = se_clean_query($_GET['query']);
}
if(!isset($query)) {
    $query = '/admin/';
}
$se_path = explode("/", $query);

$se_sections = [
    "pages","snippets",
    "addons","users","categories","tags",
    "settings","shop","events",
    "blog","inbox","uploads", "dashboard",
    "update"
];

$se_section = 'dashboard';
$maininc = "dashboard/router.php";

if(in_array($se_path[0], $se_sections)) {
    $se_section = $se_path[0];
    $maininc = $se_section."/router.php";
}


$all_plugins = se_get_all_addons();
$all_mods = $all_plugins;
$cnt_mods = count($all_mods);

/*
 * Content-format editors (mode "format", e.g. block-builder/markdown
 * plugins) are a separate axis from the WYSIWYG/code widget switcher below -
 * they don't wrap a <textarea> and must not become the default WYSIWYG
 * editor. se_bootstrap_editor_plugins() loads every editor addon's
 * global/index.php, where content-format plugins call se_register_editor().
 */
require_once SE_ROOT . 'app/functions/functions.editors.php';
$se_editor_addons = se_bootstrap_editor_plugins($all_plugins);

$se_wysiwyg_editor_addons = array_filter($se_editor_addons, static function ($editor_addon) {
    return ($editor_addon['mode'] ?? '') !== 'format';
});

/* default editor: first installed WYSIWYG/code editor (by order), else plain text */
$se_default_editor = 'plain';
if (!empty($se_wysiwyg_editor_addons)) {
    $se_first_editor = reset($se_wysiwyg_editor_addons);
    $se_default_editor = $se_first_editor['id'] ?? 'plain';
}

$se_labels = se_get_labels();
$cnt_labels = count($se_labels);
$all_langs = get_all_languages();

/**
 * read the settings
 * example use: $se_settings['default_language']
 */

$se_settings = [];
$se_get_preferences = se_get_preferences();

foreach ($se_get_preferences as $k => $v) {
    $key = $se_get_preferences[$k]['option_key'];
    $value = $se_get_preferences[$k]['option_value'];

    /* $se_settings['pagetitle'] */
    $se_prefs[$key] = $value;

    /* without the 'prefs_' prefix $se_prefs['pagetitle'] */
    if(substr($key,0,6) == 'prefs_') {
        $short_key = substr($key,6);
        $se_prefs[$short_key] = $value; // old
        $se_settings[$short_key] = $value; // new
    }
}

// server-side idle timeout - back to the login
if (se_acp_session_expired()) {
    header("location:/admin/");
    exit;
}

if ($se_settings['timezone'] != '') {
    date_default_timezone_set($se_settings['timezone']);
}

/* set language */

if (!isset($_SESSION['lang'])) {
    if ($se_settings['default_language'] != '') {
        $_SESSION['lang'] = $se_settings['default_language'];
    } else {
        $_SESSION['lang'] = $languagePack;
    }
}

if (isset($_GET['set_lang'])) {
    $set_lang = se_sanitize_lang_input($_GET['set_lang']);
    if (is_dir(SE_ROOT."languages/$set_lang/")) {
        $_SESSION['lang'] = "$set_lang";
    }
}

if (isset($_SESSION['lang'])) {
    $languagePack = basename($_SESSION['lang']);
}

// the session may still point to a language pack that no longer exists (e.g. renamed gr -> el)
if (!is_file(SE_ROOT.'languages/'.$languagePack.'/index.php')) {
    $languagePack = is_file(SE_ROOT.'languages/'.basename($se_settings['default_language']).'/index.php') ? basename($se_settings['default_language']) : 'en';
    $_SESSION['lang'] = $languagePack;
}

$languagePack = $purifier->purify($languagePack);
require SE_ROOT.'/languages/'.$languagePack.'/index.php';
require SE_ROOT.'/languages/index.php';


/**
 * $default_lang_code (string) the default language code
 */

if ($se_settings['default_language'] != '') {
    include SE_ROOT.'/languages/' . $se_settings['default_language'] . '/index.php';
    $default_lang_code = $lang_sign; // de|en|es ...
}

/**
 * $lang_codes (array) all available lang codes
 * hide languages from $prefs_deactivated_languages
 * all active languages are stored in $active_lang
 */
if (isset($se_settings['deactivated_languages']) AND $se_settings['deactivated_languages'] != '') {
    $arr_lang_deactivated = json_decode($se_settings['deactivated_languages']);
}

$langs = [];
foreach ($all_langs as $l) {
    if (isset($arr_lang_deactivated) && (in_array($l['lang_folder'], $arr_lang_deactivated))) {
        continue;
    }

    $langs[] = $l['lang_sign'];
}

$lang_codes = array_values(array_unique($langs));

$active_lang = [];
foreach($lang_codes as $l) {

    $lang_file = SE_ROOT.'/languages/' . $l . '/index.php';

    if(is_file($lang_file)) {
        include $lang_file;
        $active_lang[$l]['sign'] = $lang_sign;
        $active_lang[$l]['name'] = $lang_desc;
        $active_lang[$l]['flag'] = '/admin-xhr/images/?src=languages/' . $l . '/flag.png';
    }
}

require_once SE_ROOT . 'app/hooks/hooks-meta.php';
require_once SE_ROOT . 'app/hooks/hooks-map-helper.php';
require_once SE_ROOT . 'app/hooks/hooks-backend.php';



// hooks are only loaded for activated or page-integrated plugins,
// see se_get_hook_enabled_plugins()
$hook_plugins = se_get_hook_enabled_plugins($all_plugins);

// hooks - register meta information
foreach ($hook_plugins as $pluginDir => $pluginData) {
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

// Load backend hook handlers
foreach ($hook_plugins as $pluginDir => $pluginData) {
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
require_once SE_ROOT . 'app/hooks/hooks-global.php';

foreach ($hook_plugins as $pluginDir => $pluginData) {
    $globalHooksPath = SE_ROOT . 'plugins/' . $pluginDir . '/hooks-global';
    if (!is_dir($globalHooksPath)) {
        continue;
    }

    foreach (glob($globalHooksPath . '/*.php') as $hookFile) {
        // Each file registers callbacks via se_add_global_hook(...)
        require_once $hookFile;
    }
}

require SE_ROOT.'/acp/core/twig.php';

/* build absolute URL */
if ($se_settings['cms_ssl_domain'] != '') {
    $se_base_url = $se_settings['cms_ssl_domain'] . $se_settings['cms_base'];
} else {
    $se_base_url = $se_settings['cms_domain'] . $se_settings['cms_base'];
}

if (!isset($_COOKIE['acptheme'])) {
    setcookie("acptheme", "dark_mono", time() + (3600 * 24 * 365));
}

if (isset($_GET['theme']) && ($_GET['theme'] == 'light_mono')) {
    setcookie("acptheme", 'light_mono', time() + (3600 * 24 * 365));
    $set_acptheme = 'light_mono';
}

if (isset($_GET['theme']) && ($_GET['theme'] == 'dark_mono')) {
    setcookie("acptheme", 'dark_mono', time() + (3600 * 24 * 365));
    $set_acptheme = 'dark_mono';
}


if (isset($set_acptheme)) {
    $acptheme = $set_acptheme;
} else {
    $acptheme = $_COOKIE["acptheme"];
}

// Keep the <title> tag readable even if the configured page name is very long.
$se_title_pagename = se_return_first_chars($se_settings['pagename'], 30, false);
if (strlen($se_settings['pagename']) > 30) {
    $se_title_pagename .= '…';
}

/*
 * The page frame is acp/templates/layout/page.twig. The editor plugins, the
 * navigation and the modules print their markup themselves - it is captured
 * in the same order as before and passed to the template as html. The
 * includes stay in this scope, e.g. editors.php sets the variables the
 * init scripts of the editor plugins read.
 */

/* the <head> assets of every installed editor plugin */
ob_start();
foreach ($se_editor_addons as $editor_addon) {
    $se_editor_current = $editor_addon;
    $editor_head = SE_ROOT . 'plugins/' . $editor_addon['dir'] . '/backend/head.php';
    if (is_file($editor_head)) {
        include $editor_head;
    }
}
$editor_heads = (string) ob_get_clean();

include_once '../acp/core/templates.php';

ob_start();
include '../acp/core/nav_sidebar.php';
$nav_sidebar = (string) ob_get_clean();

ob_start();
require '../acp/core/nav_top_filter.php';
require '../acp/core/nav_top.php';
$nav_top = (string) ob_get_clean();

/* the module of the current section */
ob_start();
include '../acp/core/' . $maininc;
include '../acp/core/editors.php';
$content = (string) ob_get_clean();

/* the footer init of every installed editor plugin (registers window.seEditors) */
ob_start();
foreach ($se_editor_addons as $editor_addon) {
    $se_editor_current = $editor_addon;
    $editor_init = SE_ROOT . 'plugins/' . $editor_addon['dir'] . '/backend/init.js.php';
    if (is_file($editor_init)) {
        include $editor_init;
    }
}
$editor_inits = (string) ob_get_clean();

/* folders of the upload modal, e.g. images, images/gallery, files */
$upload_folders = [];
foreach ([IMAGES_FOLDER => $lang['images'], FILES_FOLDER => $lang['files']] as $upload_root => $upload_label) {
    $folders = [];
    foreach (array_merge([$upload_root], se_get_dirs_rec($upload_root)) as $upload_dir) {
        $folders[] = [
            'value' => $upload_dir,
            'label' => basename($upload_root) . substr($upload_dir, strlen($upload_root))
        ];
    }
    $upload_folders[] = ['label' => $upload_label, 'folders' => $folders];
}

/* the countdown to the automatic logout */
$maxlifetime = max((int) $se_settings['acp_session_lifetime'], (int) ini_get("session.gc_maxlifetime"));

echo $twig->render('layout/page.twig', [
    'lang_code' => $languagePack,
    'title_pagename' => $se_title_pagename,
    'section' => $se_section,
    'css_url' => se_acp_asset_url('backend.css'),
    'js_url' => se_acp_asset_url('backend.js'),
    'editor_heads' => $editor_heads,
    'editor_inits' => $editor_inits,
    'nav_sidebar' => $nav_sidebar,
    'nav_top' => $nav_top,
    'content' => $content,
    'maintenance' => is_file('../maintenance.html'),
    'footer_langs' => array_values($active_lang),
    'build' => $se_version['build'],
    'upload_folders' => $upload_folders,
    'default_editor' => $se_default_editor,
    'maxlifetime' => $maxlifetime
]);
