<?php
session_start();
error_reporting(0);

require '../../../core/vendor/autoload.php';
use Medoo\Medoo;

define("SE_SECTION", "backend");

if($_SESSION['user_class'] != "administrator"){
    header("location:../../index.php");
    die("PERMISSION DENIED!");
}

require '../../../config.php';
if(is_file('../../../'.SE_CONTENT.'/config.php')) {
    include '../../../'.SE_CONTENT.'/config.php';
}

if(is_file('../../../config_database.php')) {
    include '../../../config_database.php';
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

    $db_content = new Medoo([
        'type' => 'sqlite',
        'database' => CONTENT_DB
    ]);
}


if($_POST['csrf_token'] !== $_SESSION['token']) {
    die('Error: CSRF Token is invalid');
}

require '../functions.php';
require '../../../global/functions.php';

$se_get_preferences = se_get_preferences();

foreach ($se_get_preferences as $k => $v) {
    $key = $se_get_preferences[$k]['option_key'];
    $value = $se_get_preferences[$k]['option_value'];

    /* $se_prefs['prefs_pagetitle'] */
    $se_prefs[$key] = $value;

    /* without the 'prefs_' prefix $se_prefs['pagetitle'] */
    if(substr($key,0,6) == 'prefs_') {
        $short_key = substr($key,6);
        $se_prefs[$short_key] = $value;
    }

}

/* set language */

if (!isset($_SESSION['lang'])) {
    if ($se_prefs['prefs_default_language'] != '') {
        $_SESSION['lang'] = $se_prefs['prefs_default_language'];
    } else {
        $_SESSION['lang'] = $languagePack;
    }
}

if (isset($_GET['set_lang'])) {
    $set_lang = strip_tags($_GET['set_lang']);
    if (is_dir("../core/lang/$set_lang/")) {
        $_SESSION['lang'] = "$set_lang";
    }
}

if (isset($_SESSION['lang'])) {
    $languagePack = basename($_SESSION['lang']);
}

require '../../../core/lang/index.php';