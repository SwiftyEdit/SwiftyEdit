<?php

/**
 * @var object $smarty
 * @var array $lang
 */

// Doesn't touch $_SESSION at all - safe to release the session lock
// immediately. These live-validation checks fire on every keystroke in the
// registration form; without an early close here, the default file session
// handler would force concurrent checks to queue up and run one at a time.
// Do not add $_SESSION usage below without removing this first.
session_write_close();

/**
 * @var array $se_settings
 */

// The username/e-mail checks tell whether an account exists - only offer
// them while the registration form itself is available
$registration_open = ($se_settings['userregistration'] ?? '') == 'yes';
if(!$registration_open && in_array($_GET['check'] ?? '', ['username', 'email_exists'], true)) {
    exit;
}

// the same address must not be tested over and over (account enumeration)
if(in_array($_GET['check'] ?? '', ['username', 'email_exists'], true)) {
    if(se_rate_limit_exceeded('checks', 60, 600)) {
        exit;
    }
    se_rate_limit_add('checks', 600);
}

// check if username is valid and if it exists
if(isset($_GET['check']) && $_GET['check'] == "username") {

    $check_username = $_GET['username'];

    if(se_is_valid_username($check_username) === false) {
        $smarty->assign("alert_text",$lang['msg_register_userchars']);
        $smarty->display('alert/alert-danger.tpl');
        exit;
    }

    if(se_username_exists($check_username) === true) {
        $smarty->assign("alert_text",$lang['msg_register_existinguser']);
        $smarty->display('alert/alert-danger.tpl');
        exit;
    }
    exit;
}

// check if email exists
if(isset($_GET['check']) && $_GET['check'] == "email_exists") {
    if(se_email_exists($_GET['mail']) === true) {
        $smarty->assign("alert_text",$lang['msg_register_existingusermail']);
        $smarty->display('alert/alert-danger.tpl');
        exit;
    }
    exit;
}

// check if mail repeat is equal to mail
if(isset($_GET['check']) && $_GET['check'] == "email_repeat") {
    if($_GET['mail'] !== $_GET['mailrepeat']) {
        $smarty->assign("alert_text",$lang['msg_register_mailrepeat_error']);
        $smarty->display('alert/alert-danger.tpl');
        exit;
    }
    exit;
}

// check if password repeat is equal to password - passwords via POST only,
// so they never end up in URLs and access logs
if(isset($_GET['check']) && $_GET['check'] == "psw_repeat") {
    if(($_POST['psw'] ?? '') !== ($_POST['psw_repeat'] ?? '')) {
        $smarty->assign("alert_text",$lang['msg_register_pswrepeat_error']);
        $smarty->display('alert/alert-danger.tpl');
        exit;
    }
    exit;
}