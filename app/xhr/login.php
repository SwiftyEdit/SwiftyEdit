<?php

$remember = false;
if(isset($_POST['remember_me'])) {
    $remember = true;
}

$login = se_user_login($_POST['login_name'],$_POST['login_psw'],$acp=FALSE,$remember);

if($login === 'failed') {
    header('HX-Location: {"path":"/xhr/se/statusbox/?error=login_failed","target":"#user-box","swap":"innerHTML"}');
} else {
    // success, reload the status box
    header("HX-Trigger: update_user_status");
}