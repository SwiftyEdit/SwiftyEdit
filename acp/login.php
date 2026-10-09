<?php
session_start();
//error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);
const SE_SECTION = "backend";
require '../vendor/autoload.php';
use Medoo\Medoo;

require '../config.php';
if(is_file(SE_CONTENT.'/config.php')) {
    include SE_CONTENT.'/config.php';
}

// only show errors when explicitly running in development mode
if ($se_environment === 'd') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

/**
 * connect the database
 * @var string $db_content
 * @var string $db_user
 * @var string $db_posts
 */

require SE_ROOT.'/app/database.php';
require '../languages/index.php';
$login = '';

$se_get_settings = se_get_preferences();

foreach ($se_get_settings as $k => $v) {
    $key = $se_get_settings[$k]['option_key'];
    $value = $se_get_settings[$k]['option_value'];
    if(substr($key,0,6) == 'prefs_') {
        $short_key = substr($key,6);
        $se_settings[$short_key] = $value; // new
    }
}

// same timezone as the rest of the backend - e.g. the "until" date of
// $se_2fa_bypass is meant in the site's local time
if(($se_settings['timezone'] ?? '') != '') {
    date_default_timezone_set($se_settings['timezone']);
}

if($se_settings['login_slug'] != '') {
    // check the url
    $form_path = '/admin/'.$se_settings['login_slug'];
    if($_REQUEST['query'] != $se_settings['login_slug']) {
        //redirect to startpage
        header('Location: /');
        exit;
    }
} else {
    $form_path = '/admin/';
}

if(empty($_SESSION['token'])) {
    se_generate_token();
}

$hidden_csrf_token = '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

/* stop all $_POST actions if csrf token is empty or invalid */
if(!empty($_POST)) {
    se_validate_token($_POST['csrf_token']);
}

if(isset($_POST['check']) && ($_POST['check'] == "Login")) {

    $remember = false;
    if(isset($_POST['remember_me'])) {
        $remember = true;
    }

    $login = se_user_login($_POST['login_name'],$_POST['login_psw'],$acp=TRUE,$remember);
}

/**
 * Second step of the backend login - two-factor authentication
 * (see app/functions/functions.twofactor.php). While a login is pending,
 * this page shows the code step, the setup or the recovery codes instead
 * of the password form.
 */
$twofa_view = '';
$twofa_alerts = [];
$twofa_user = null;

// password accepted, second factor required - send the first code right away
if($login === '2fa') {
    $twofa_user = se_2fa_pending_user();
    if($twofa_user !== null && $twofa_user['user_2fa_method'] === 'mail') {
        if(se_2fa_send_mail_code($twofa_user) === 'failed') {
            $twofa_alerts[] = ['danger', $lang['login_2fa_msg_send_failed']];
        }
    }
}

if(isset($_SESSION['2fa_pending'])) {

    $twofa_user = se_2fa_pending_user();

    if($twofa_user === null) {
        se_2fa_cancel_pending();
        $twofa_alerts[] = ['danger', $lang['login_2fa_msg_expired']];

    } elseif(isset($_POST['2fa_cancel'])) {
        se_2fa_cancel_pending();

    } else {

        $twofa_setup = $twofa_user['user_2fa_method'] === '';

        // setup: choose the method (only mail if the app method can't store its key)
        if($twofa_setup && !se_2fa_app_available()) {
            $_SESSION['2fa_pending']['setup_method'] = 'mail';
        }
        if($twofa_setup && isset($_POST['2fa_choose']) && in_array($_POST['2fa_choose'], ['mail', 'totp'], true)) {
            $_SESSION['2fa_pending']['setup_method'] = $_POST['2fa_choose'];
            if($_POST['2fa_choose'] === 'totp' && empty($_SESSION['2fa_pending']['totp_secret'])) {
                // the key only goes to the database once the first code from the app matches
                $_SESSION['2fa_pending']['totp_secret'] = se_2fa_new_totp_secret();
            }
        }
        if($twofa_setup && isset($_POST['2fa_back']) && se_2fa_app_available()) {
            unset($_SESSION['2fa_pending']['setup_method'], $_SESSION['2fa_pending']['totp_secret']);
        }
        $twofa_setup_method = $_SESSION['2fa_pending']['setup_method'] ?? '';

        // send a (new) code
        if(isset($_POST['2fa_send'])) {
            $send_result = se_2fa_send_mail_code($twofa_user);
            if($send_result === 'sent') {
                $twofa_alerts[] = ['success', $lang['login_2fa_msg_sent']];
            } elseif($send_result === 'wait') {
                $twofa_alerts[] = ['info', $lang['login_2fa_msg_wait']];
            } else {
                $twofa_alerts[] = ['danger', $twofa_setup ? $lang['login_2fa_msg_send_failed_setup'] : $lang['login_2fa_msg_send_failed']];
            }
            $twofa_user = se_2fa_pending_user();
        }

        // check the entered code
        if(isset($_POST['2fa_verify'])) {
            $twofa_input = is_string($_POST['2fa_code'] ?? null) ? $_POST['2fa_code'] : '';

            if(se_2fa_throttled()) {
                se_2fa_cancel_pending();
                $twofa_alerts[] = ['danger', $lang['login_2fa_msg_too_many']];

            } elseif($twofa_setup && $twofa_setup_method === 'mail' && se_2fa_check_mail_code($twofa_user, $twofa_input)) {
                // setup confirmed - show the recovery codes once, then log in
                $_SESSION['2fa_pending']['recovery_codes'] = se_2fa_save_setup($twofa_user, 'mail');

            } elseif($twofa_setup && $twofa_setup_method === 'totp'
                && ($twofa_step = se_2fa_totp_match($_SESSION['2fa_pending']['totp_secret'] ?? '', $twofa_input)) !== false) {
                $_SESSION['2fa_pending']['recovery_codes'] = se_2fa_save_setup($twofa_user, 'totp', $_SESSION['2fa_pending']['totp_secret'], $twofa_step);
                unset($_SESSION['2fa_pending']['totp_secret']);

            } elseif(!$twofa_setup && se_2fa_check_input($twofa_user, $twofa_input)) {
                if(!empty($_POST['2fa_trust'])) {
                    se_2fa_trust_device($twofa_user);
                }
                se_2fa_complete_login(se_2fa_pending_user());
                exit;

            } elseif(se_2fa_count_failed_attempt()) {
                $twofa_alerts[] = ['danger', $lang['login_2fa_msg_wrong_code']];
            } else {
                $twofa_alerts[] = ['danger', $lang['login_2fa_msg_too_many']];
            }
            $twofa_user = se_2fa_pending_user();
        }

        // recovery codes shown and saved - setup done
        if(isset($_POST['2fa_recovery_saved']) && !empty($_SESSION['2fa_pending']['recovery_codes'])) {
            if(!empty($_POST['2fa_trust'])) {
                se_2fa_trust_device($twofa_user);
            }
            se_2fa_complete_login(se_2fa_pending_user());
            exit;
        }
    }

    // what to show
    if(se_2fa_get_pending() !== null && $twofa_user !== null) {
        if(!empty($_SESSION['2fa_pending']['recovery_codes'])) {
            $twofa_view = 'recovery';
        } elseif($twofa_user['user_2fa_method'] === '') {
            $twofa_view = match ($_SESSION['2fa_pending']['setup_method'] ?? '') {
                'mail' => 'setup',
                'totp' => 'setup_app',
                default => 'choose'
            };
        } else {
            $twofa_view = 'verify';
        }
    }
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
        echo $lang['msg_login_false'];
        echo '</div>';
    }
    foreach($twofa_alerts as [$alert_type, $alert_text]) {
        echo '<div class="alert alert-'.$alert_type.'">'.htmlspecialchars($alert_text, ENT_QUOTES).'</div>';
    }
    ?>

    <?php if($twofa_view === 'recovery') { ?>

    <h5><?php echo $lang['login_2fa_recovery_title']; ?></h5>
    <p><?php echo $lang['login_2fa_recovery_intro']; ?></p>
    <pre class="border rounded p-3 text-center fs-5"><?php
        echo htmlspecialchars(implode("\n", $_SESSION['2fa_pending']['recovery_codes']), ENT_QUOTES);
    ?></pre>
    <form action="<?php echo $form_path; ?>" method="post">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="2fa_trust" value="1" id="twofaTrust">
            <label class="form-check-label" for="twofaTrust"><?php echo $lang['login_2fa_trust_device']; ?></label>
        </div>
        <input type="submit" class="btn btn-primary w-100" name="2fa_recovery_saved" value="<?php echo htmlspecialchars($lang['login_2fa_btn_recovery_saved'], ENT_QUOTES); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">
    </form>

    <?php } elseif($twofa_view !== '') {
        $twofa_mail = htmlspecialchars(se_2fa_mask_mail($twofa_user['user_mail']), ENT_QUOTES);
        $twofa_app = $twofa_view === 'setup_app' || ($twofa_view === 'verify' && $twofa_user['user_2fa_method'] === 'totp');
        // mail setup: the code field only appears once a code has been sent
        $twofa_code_sent = (int) $twofa_user['user_2fa_mail_expires'] > time();
        $twofa_show_code = $twofa_view === 'verify' || $twofa_view === 'setup_app' || ($twofa_view === 'setup' && $twofa_code_sent);
    ?>

    <h5><?php echo $lang['login_2fa_title']; ?></h5>

    <?php if($twofa_view === 'choose') { ?>

    <p><?php echo $lang['login_2fa_choose_method']; ?></p>
    <form action="<?php echo $form_path; ?>" method="post">
        <button type="submit" class="btn btn-default w-100 text-start mb-2" name="2fa_choose" value="mail">
            <strong><?php echo $lang['login_2fa_method_mail']; ?></strong><br>
            <small class="text-muted"><?php echo str_replace('{MAIL}', $twofa_mail, htmlspecialchars($lang['login_2fa_method_mail_help'], ENT_QUOTES)); ?></small>
        </button>
        <button type="submit" class="btn btn-default w-100 text-start mb-3" name="2fa_choose" value="totp">
            <strong><?php echo $lang['login_2fa_method_app']; ?></strong><br>
            <small class="text-muted"><?php echo $lang['login_2fa_method_app_help']; ?></small>
        </button>
        <input type="submit" class="btn btn-default w-100" name="2fa_cancel" value="<?php echo htmlspecialchars($lang['login_2fa_btn_cancel'], ENT_QUOTES); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">
    </form>

    <?php } else { ?>

    <p>
        <?php
        $twofa_intro = match (true) {
            $twofa_view === 'setup' => $lang['login_2fa_intro_setup'],
            $twofa_view === 'setup_app' => $lang['login_2fa_app_setup_intro'],
            $twofa_app => $lang['login_2fa_intro_verify_app'],
            default => $lang['login_2fa_intro_verify']
        };
        echo str_replace('{MAIL}', '<strong>'.$twofa_mail.'</strong>', htmlspecialchars($twofa_intro, ENT_QUOTES));
        ?>
    </p>

    <?php if($twofa_view === 'setup_app') {
        $twofa_secret = $_SESSION['2fa_pending']['totp_secret'];
        $twofa_uri = se_2fa_otpauth_uri($twofa_secret, $twofa_user['user_nick']);
    ?>
    <div class="bg-white p-2 rounded mx-auto mb-2" style="max-width:220px" data-otpauth="<?php echo htmlspecialchars($twofa_uri, ENT_QUOTES); ?>"></div>
    <p class="text-center small"><?php echo $lang['login_2fa_app_key']; ?>: <code class="user-select-all"><?php echo trim(chunk_split($twofa_secret, 4, ' ')); ?></code></p>
    <script type="module" src="/themes/administration/dist/twofa.js"></script>
    <?php } ?>

    <?php if($twofa_show_code) { ?>
    <form action="<?php echo $form_path; ?>" method="post" class="mb-3">
        <label class="form-label" for="twofaCode"><?php echo $lang['login_2fa_label_code']; ?></label>
        <input type="text" class="form-control mb-2" name="2fa_code" id="twofaCode" inputmode="numeric" autocomplete="one-time-code" autofocus="autofocus" required>
        <?php if($twofa_view === 'verify') { ?>
            <div class="form-text mb-2"><?php echo $twofa_app ? $lang['login_2fa_help_recovery_app'] : $lang['login_2fa_help_recovery']; ?></div>
        <?php } ?>
        <?php if($twofa_view === 'verify') { ?>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="2fa_trust" value="1" id="twofaTrust">
            <label class="form-check-label" for="twofaTrust"><?php echo $lang['login_2fa_trust_device']; ?></label>
        </div>
        <?php } ?>
        <input type="submit" class="btn btn-primary w-100" name="2fa_verify" value="<?php echo htmlspecialchars($lang['login_2fa_btn_verify'], ENT_QUOTES); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">
    </form>
    <?php } ?>

    <form action="<?php echo $form_path; ?>" method="post" class="d-flex gap-2">
        <?php if(!$twofa_app) {
            $twofa_send_label = ($twofa_view === 'verify' || $twofa_code_sent) ? $lang['login_2fa_btn_resend'] : $lang['login_2fa_btn_send']; ?>
            <input type="submit" class="btn btn-<?php echo ($twofa_view === 'setup' && !$twofa_code_sent) ? 'primary' : 'default'; ?> flex-fill" name="2fa_send" value="<?php echo htmlspecialchars($twofa_send_label, ENT_QUOTES); ?>">
        <?php } ?>
        <?php if(in_array($twofa_view, ['setup', 'setup_app'], true) && se_2fa_app_available()) { ?>
            <input type="submit" class="btn btn-default" name="2fa_back" value="<?php echo htmlspecialchars($lang['login_2fa_btn_back'], ENT_QUOTES); ?>">
        <?php } ?>
        <input type="submit" class="btn btn-default<?php echo $twofa_app ? ' flex-fill' : ''; ?>" name="2fa_cancel" value="<?php echo htmlspecialchars($lang['login_2fa_btn_cancel'], ENT_QUOTES); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">
    </form>

    <?php } ?>

    <?php } else { ?>

    <form action="<?php echo $form_path; ?>" method="post" class="">
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
        <?php
        // no "remember me" here anymore: the backend session lifetime is enforced
        // on the server - with 2FA, "trust this device" skips the second factor
        ?>
        <div class="row">
            <div class="offset-sm-3 col-sm-9">
                <input type="submit" class="btn btn-primary w-100" name="check" value="Login">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">
            </div>
        </div>
    </form>

    <?php } ?>
</div>
</body>
</html>