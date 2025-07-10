<?php

$remember = false;
if(isset($_POST['remember_me'])) {
    $remember = true;
}

$login = se_user_login($_POST['login_name'],$_POST['login_psw'],$acp=FALSE,$remember);
header( "HX-Trigger: update_user_status");