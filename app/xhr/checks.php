<?php

/**
 * @var object $smarty
 * @var array $lang
 */


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

// check if password repeat is equal to password
if(isset($_GET['check']) && $_GET['check'] == "psw_repeat") {
    if($_GET['psw'] !== $_GET['psw_repeat']) {
        $smarty->assign("alert_text",$lang['msg_register_pswrepeat_error']);
        $smarty->display('alert/alert-danger.tpl');
        exit;
    }
    exit;
}