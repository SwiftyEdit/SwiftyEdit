<?php

require '../vendor/autoload.php';
use Medoo\Medoo;

const SE_SECTION = "backend";

if($_SESSION['user_class'] != "administrator"){
    header("location:../../index.php");
    die("PERMISSION DENIED!");
}

require '../config.php';

if(is_file('../'.SE_CONTENT.'/config.php')) {
    include '../'.SE_CONTENT.'/config.php';
}


if(is_file('../config_database.php')) {
    include '../config_database.php';
    $db_type = 'mysql';

    $database = new Medoo([
        'type' => 'mysql',
        'database' => "$database_name",
        'host' => "$database_host",
        'username' => "$database_user",
        'password' => "$database_psw",
        'charset' => 'utf8',
        'port' => $database_port,
        'prefix' => DB_PREFIX
    ]);

    $db_content = $database;
    $db_user = $database;
    $db_statistics = $database;

} else {
    $db_type = 'sqlite';

    define("CONTENT_DB", "$se_db_content");
    define("USER_DB", "$se_db_user");
    define("POSTS_DB", "$se_db_posts");

    $db_content = new Medoo([
        'type' => 'sqlite',
        'database' => CONTENT_DB
    ]);

    $db_user = new Medoo([
        'type' => 'sqlite',
        'database' => USER_DB
    ]);

    $db_posts = new Medoo([
        'type' => 'sqlite',
        'database' => POSTS_DB
    ]);
}

if(!empty($_POST) && $_POST['csrf_token'] !== $_SESSION['token']) {
    die('Error: CSRF Token is invalid');
}

require 'core/icons.php';
require 'core/functions.php';
require '../core/functions/functions.php';

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