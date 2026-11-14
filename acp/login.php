<?php

/**
 * backend login, included by public/admin.php for everyone who is not logged in as administrator -
 * password form and two-factor authentication, markup in acp/templates/login.twig
 *
 * global variables (config.php, languages/index.php)
 * @var string $se_environment
 * @var array $lang
 * @var array $icon
 * @var object $twig
 */

require_once __DIR__.'/../app/session.php';
se_session_start();
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
require_once SE_ROOT.'/app/functions/functions.helpers.php';
$login = '';

$se_settings = [];
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

/* stop all $_POST actions if csrf token is empty or invalid */
if(!empty($_POST)) {
    se_validate_token($_POST['csrf_token']);
}

if(isset($_POST['check']) && ($_POST['check'] == "Login")) {

    // missing or non-string values (e.g. login_name[]=) count as a failed login
    $login_name = is_string($_POST['login_name'] ?? null) ? $_POST['login_name'] : '';
    $login_psw = is_string($_POST['login_psw'] ?? null) ? $_POST['login_psw'] : '';

    $login = se_user_login($login_name,$login_psw,$acp=TRUE);
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
// (also for the setup: it always starts by confirming the stored e-mail address)
if($login === '2fa') {
    $twofa_user = se_2fa_pending_user();
    if($twofa_user !== null && !se_2fa_account_locked($twofa_user) && !se_2fa_mail_blocked($twofa_user)
        && in_array($twofa_user['user_2fa_method'], ['mail', ''], true)) {
        if(se_2fa_send_mail_code($twofa_user) === 'failed') {
            $twofa_alerts[] = ['danger', $twofa_user['user_2fa_method'] === '' ? $lang['login_2fa_msg_send_failed_setup'] : $lang['login_2fa_msg_send_failed']];
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

    } elseif(se_2fa_account_locked($twofa_user)) {
        // too many wrong codes for this account - no code is checked or sent until the lock ends
        se_2fa_cancel_pending();
        $twofa_alerts[] = ['danger', $lang['login_2fa_msg_locked']];

    } else {

        $twofa_setup = $twofa_user['user_2fa_method'] === '';

        // setup: the stored e-mail address has to be confirmed with a mail code
        // first - otherwise the password alone would be enough to pair an app
        // (e.g. after a reset of 2FA) and lock the owner out
        $twofa_mail_confirmed = $twofa_setup && !empty($_SESSION['2fa_pending']['mail_confirmed']);

        // setup: choose the method - mail is done right away, its address was just confirmed
        if($twofa_mail_confirmed && isset($_POST['2fa_choose']) && $_POST['2fa_choose'] === 'mail') {
            $_SESSION['2fa_pending']['recovery_codes'] = se_2fa_save_setup($twofa_user, 'mail');
        }
        if($twofa_mail_confirmed && isset($_POST['2fa_choose']) && $_POST['2fa_choose'] === 'totp' && se_2fa_app_available()) {
            $_SESSION['2fa_pending']['setup_method'] = 'totp';
            if(empty($_SESSION['2fa_pending']['totp_secret'])) {
                // the key only goes to the database once the first code from the app matches
                $_SESSION['2fa_pending']['totp_secret'] = se_2fa_new_totp_secret();
            }
        }
        if($twofa_mail_confirmed && isset($_POST['2fa_back'])) {
            unset($_SESSION['2fa_pending']['setup_method'], $_SESSION['2fa_pending']['totp_secret']);
        }
        $twofa_setup_method = $_SESSION['2fa_pending']['setup_method'] ?? '';

        // send a (new) code - not while a recovery code is required after a password reset
        if(isset($_POST['2fa_send']) && !se_2fa_mail_blocked($twofa_user)) {
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

            } elseif($twofa_setup && !$twofa_mail_confirmed && se_2fa_check_mail_code($twofa_user, $twofa_input)) {
                // e-mail address confirmed - now the method can be chosen;
                // without the app method, mail is the only one: setup done
                $_SESSION['2fa_pending']['mail_confirmed'] = true;
                if(se_2fa_app_available()) {
                    $twofa_alerts[] = ['success', $lang['login_2fa_msg_mail_confirmed']];
                } else {
                    // show the recovery codes once, then log in
                    $_SESSION['2fa_pending']['recovery_codes'] = se_2fa_save_setup($twofa_user, 'mail');
                }

            } elseif($twofa_mail_confirmed && $twofa_setup_method === 'totp'
                && ($twofa_step = se_2fa_totp_match($_SESSION['2fa_pending']['totp_secret'] ?? '', $twofa_input)) !== false) {
                $_SESSION['2fa_pending']['recovery_codes'] = se_2fa_save_setup($twofa_user, 'totp', $_SESSION['2fa_pending']['totp_secret'], $twofa_step);
                unset($_SESSION['2fa_pending']['totp_secret']);

            } elseif(!$twofa_setup && se_2fa_check_input($twofa_user, $twofa_input)) {
                if(!empty($_POST['2fa_trust'])) {
                    se_2fa_trust_device($twofa_user);
                }
                se_2fa_complete_login(se_2fa_pending_user());
                exit;

            } else {
                $twofa_alerts[] = ['danger', match (se_2fa_count_failed_attempt($twofa_user)) {
                    'active' => $lang['login_2fa_msg_wrong_code'],
                    'locked' => $lang['login_2fa_msg_locked'],
                    default => $lang['login_2fa_msg_too_many']
                }];
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
            if(empty($_SESSION['2fa_pending']['mail_confirmed'])) {
                $twofa_view = 'setup';
            } else {
                $twofa_view = ($_SESSION['2fa_pending']['setup_method'] ?? '') === 'totp' ? 'setup_app' : 'choose';
            }
        } else {
            $twofa_view = 'verify';
        }
    }
}

/*
 * values of the 2FA step for acp/templates/login.twig
 */
$twofa = ['view' => $twofa_view];

if($twofa_view === 'recovery') {
    $twofa['recovery_codes'] = $_SESSION['2fa_pending']['recovery_codes'];
    $twofa['recovery_mail_hint'] = ((se_2fa_pending_user()['user_2fa_method'] ?? '') === 'mail');

} elseif($twofa_view !== '') {
    $twofa_app = $twofa_view === 'setup_app' || ($twofa_view === 'verify' && $twofa_user['user_2fa_method'] === 'totp');
    // mail setup: the code field only appears once a code has been sent
    $twofa_code_sent = (int) $twofa_user['user_2fa_mail_expires'] > time();
    // after a password reset by e-mail only a recovery code is accepted (see se_2fa_mail_blocked())
    $twofa_blocked = $twofa_view === 'verify' && se_2fa_mail_blocked($twofa_user);

    $twofa += [
        'mail' => se_2fa_mask_mail($twofa_user['user_mail']),
        'app' => $twofa_app,
        'code_sent' => $twofa_code_sent,
        'show_code' => $twofa_view === 'verify' || $twofa_view === 'setup_app' || ($twofa_view === 'setup' && $twofa_code_sent),
        'blocked' => $twofa_blocked,
        'intro' => match (true) {
            $twofa_view === 'setup' => $lang['login_2fa_intro_setup'],
            $twofa_view === 'setup_app' => $lang['login_2fa_app_setup_intro'],
            $twofa_blocked => $lang['login_2fa_intro_verify_blocked'],
            $twofa_app => $lang['login_2fa_intro_verify_app'],
            default => $lang['login_2fa_intro_verify']
        }
    ];

    if($twofa_view === 'setup_app') {
        $twofa_secret = $_SESSION['2fa_pending']['totp_secret'];
        $twofa['otpauth_uri'] = se_2fa_otpauth_uri($twofa_secret, $twofa_user['user_nick']);
        // grouped in fours for typing it into the app
        $twofa['secret'] = trim(chunk_split($twofa_secret, 4, ' '));
    }
}

require SE_ROOT.'/acp/core/icons.php';
require SE_ROOT.'/acp/core/twig.php';

echo $twig->render('login.twig', [
    'form_path' => $form_path,
    'css_url' => se_acp_asset_url('backend.css'),
    'twofa_js_url' => se_acp_asset_url('twofa.js'),
    'login_failed' => ($login == 'failed'),
    'alerts' => array_map(static fn($alert) => ['type' => $alert[0], 'text' => $alert[1]], $twofa_alerts),
    'twofa' => $twofa
]);
