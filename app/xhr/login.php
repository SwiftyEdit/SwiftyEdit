<?php

/**
 * frontend login (form in loginbox.tpl)
 *
 * @var object $smarty
 * @var array $se_settings
 */

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
    // answer with the login box and its error message right away - the form's
    // hx-target (#user-box) takes it. No HX-Location here: htmx would push the
    // XHR url into the browser history and a reload would end on /xhr/se/statusbox/
    // se_user_login() started a new session with a new CSRF token, so the
    // hidden field from bootstrap.php is outdated and has to be rebuilt
    $hidden_csrf_token = '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
    $smarty->assign('hidden_csrf_token', $hidden_csrf_token, true);
    $_GET['error'] = 'login_failed';
    require __DIR__.'/statusbox.php';
} else {
    // success, reload the status box
    header("HX-Trigger: update_user_status");
}