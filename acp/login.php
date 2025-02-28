<?php
session_start();
error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);
const SE_SECTION = "backend";
require '../vendor/autoload.php';
use Medoo\Medoo;

require '../config.php';
if(is_file(SE_CONTENT.'/config.php')) {
    include SE_CONTENT.'/config.php';
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
    define("STATS_DB", "$se_db_stats");

    $db_content = new Medoo([
        'type' => 'sqlite',
        'database' => CONTENT_DB
    ]);

    $db_user = new Medoo([
        'type' => 'sqlite',
        'database' => USER_DB
    ]);

    $db_statistics = new Medoo([
        'type' => 'sqlite',
        'database' => STATS_DB
    ]);

}





require '../acp/core/functions.php';
require '../core/functions/func_userdata.php';
require '../languages/index.php';
$login = '';

if(isset($_POST['check']) && ($_POST['check'] == "Login")) {

    $remember = false;
    if(isset($_POST['remember_me'])) {
        $remember = true;
    }

    $login = se_user_login($_POST['login_name'],$_POST['login_psw'],$acp=TRUE,$remember);
}
?>

<!DOCTYPE html>
<html data-bs-theme="dark" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login <?php echo $_SERVER['SERVER_NAME']; ?></title>
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="/themes/administration/dist/backend.css" type="text/css" media="screen, projection">
    <style>
        .form-center {
            max-width: 475px;
            padding: 25px;
            background: var(--bs-widget-bg-300);
        }
        .icon {
            text-align: left;
            margin-top: -65px;
        }
        .icon img {
            width: 64px;
            height: auto;
            margin: 0 auto;
            filter: drop-shadow(1px 1px 5px rgb(0,0,0,.4));
        }
    </style>
</head>
<body class="d-flex h-100">
<div class="form-center w-100 m-auto border-info border-2 rounded shadow">
    <div class="icon">
        <img src="/themes/administration/images/swiftyedit_icon.svg" class="img-fluid">
    </div>

    <?php
    if($login == 'failed') {
        echo '<div class="alert alert-danger">';
        echo $lang['msg_error_login_data'];
        echo '</div>';
    }
    ?>

    <form action="/admin/" method="post" class="">
        <div class="row mb-2">
            <label class="col-sm-3 col-form-label"><?php echo $lang['label_username']; ?></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="login_name" autofocus="autofocus">
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-sm-3 col-form-label"><?php echo $lang['label_password']; ?></label>
            <div class="col-sm-9">
                <input type="password" class="form-control" name="login_psw">
            </div>
        </div>
        <div class="row mb-2">
            <div class="offset-sm-3 col-sm-9">
                <div class="form-check-inline">
                    <label class="form-check-label">
                        <input type="checkbox" name="remember_me"> <?php echo $lang['label_remember_me']; ?>
                    </label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="offset-sm-3 col-sm-9">
                <input type="submit" class="btn btn-primary w-100" name="check" value="Login">
            </div>
        </div>
    </form>
</div>
</body>
</html>