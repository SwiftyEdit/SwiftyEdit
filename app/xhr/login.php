<?php

// login only per POST - anything else (e.g. a plain GET) has nothing to check
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

// no frontend login while the login form is switched off in the settings -
// hiding the form alone wouldn't stop requests to this endpoint
// (the backend has its own login, acp/login.php)
if(($se_settings['showloginform'] ?? '') !== 'yes') {
    http_response_code(403);
    exit;
}

// missing or non-string values (e.g. login_name[]=) count as a failed login
$login_name = is_string($_POST['login_name'] ?? null) ? $_POST['login_name'] : '';
$login_psw = is_string($_POST['login_psw'] ?? null) ? $_POST['login_psw'] : '';

$login = se_user_login($login_name,$login_psw,$acp=FALSE);

if($login === 'failed') {
    header('HX-Location: {"path":"/xhr/se/statusbox/?error=login_failed","target":"#user-box","swap":"innerHTML"}');
} else {
    // success, reload the status box
    header("HX-Trigger: update_user_status");
}