<?php

/**
 * @var array $lang install/index.php
 */

if(!defined('INSTALLER')) {
	header("location:../login.php");
	die("PERMISSION DENIED!");
}

include_once SE_ROOT.'app/functions/functions.password.php';

// the password messages live in frontend.json, which the installer doesn't
// load otherwise - English is the fallback until they are translated
$psw_lang = array_merge(
	(array) json_decode((string) file_get_contents(SE_ROOT.'languages/en/frontend.json'), true),
	(array) json_decode((string) file_get_contents(SE_ROOT.'languages/'.basename($_SESSION['lang'] ?? 'en').'/frontend.json'), true)
);
$psw_error_text = '';

$checked_user_data = array();

if(!isset($_SESSION['temp_username'])) {
    $_SESSION['temp_username'] = '';
}
if(!isset($_SESSION['temp_usermail'])) {
    $_SESSION['temp_usermail'] = '';
}
if(!isset($_SESSION['temp_userpsw'])) {
    $_SESSION['temp_userpsw'] = '';
}

if(isset($_POST['check_user_data'])) {
	
	if(($_POST['username'] ?? '') != '') {
		$_SESSION['temp_username'] = $_POST['username'];
	} else {
		$checked_user_data[] = 'error';
	}
	if(($_POST['mail'] ?? '') != '') {
		$_SESSION['temp_usermail'] = $_POST['mail'];
	} else {
		$checked_user_data[] = 'error';
	}
	$new_psw = is_string($_POST['psw'] ?? null) ? $_POST['psw'] : '';
	$psw_errors = se_password_check($new_psw, [$_SESSION['temp_username'], $_SESSION['temp_usermail']]);
	if($psw_errors === []) {
		$_SESSION['temp_userpsw'] = $new_psw;
	} else {
		$checked_user_data[] = 'error';
		$psw_error_text = se_password_error_text($psw_errors, $psw_lang);
	}
	
}
echo '<form action="index.php" method="POST">';
echo '<div class="card">';

echo '<h5 class="card-header">'.$lang['label_add_user'].'</h5>';
echo '<div class="card-body">';
echo '<div class="alert alert-info">'.$lang['description_add_user'].'</div>';


echo '<div class="mb-2">';
echo '<label class="form-label">'.$lang['username'].' <small>(A-Za-z0-9) / (min 2, max 20)</small></label>';
echo '<input type="text" class="form-control" name="username" value="'.htmlspecialchars($_SESSION['temp_username'], ENT_QUOTES).'">';
echo '</div>';

echo '<div class="mb-2">';
echo '<label class="form-label">'.$lang['email'].'</label>';
echo '<input type="mail" class="form-control" name="mail" value="'.htmlspecialchars($_SESSION['temp_usermail'], ENT_QUOTES).'">';
echo '</div>';

echo '<div class="mb-2">';
echo '<label class="form-label">'.$lang['password'].'</label>';
echo '<input type="password" class="form-control" name="psw" value="'.htmlspecialchars($_SESSION['temp_userpsw'], ENT_QUOTES).'">';
echo '<div class="form-text">'.se_password_requirements_text($psw_lang).'</div>';
if($psw_error_text !== '') {
	echo '<div class="alert alert-danger mt-2">'.$psw_error_text.'</div>';
}
echo '</div>';

echo '</div>';

echo '<div class="card-footer">';

if((in_array('error', $checked_user_data) OR $_SESSION['temp_username'] == '')) {
    echo '<input type="submit" class="btn btn-info" name="check_user_data" value="'.$lang['btn_enter_user'].'">';
} else {
    echo '<input type="submit" class="btn btn-success" name="step3" value="'.$lang['next_step'].'">';
}
echo '</div>';

echo '</div>';
echo '</form>';