<?php

/**
 * global variables
 * @var array $lang
 * @var object $db_user
 */

// Note: a "remember me" cookie login used to live here. It was unreachable
// (public/admin.php and admin_xhr.php only load the backend for sessions that
// are already administrators) and would have granted administrator rights to
// any valid token holder - removed.

if(!isset($_SESSION['user_class']) OR $_SESSION['user_class'] != "administrator"){
	//move back to site or die
	header("location:../index.php");
	die("PERMISSION DENIED!");
}


// check if token is set
if(!isset($_SESSION['token'])) {
	die('Error: CSRF Token is invalid');
}

// stop all $_POST actions if csrf token is empty or invalid
if(!empty($_POST)) {
	if(empty($_POST['csrf_token']) || !is_string($_POST['csrf_token'])) {
		die('Error: CSRF Token is empty');
	}
	if(!hash_equals((string) $_SESSION['token'], $_POST['csrf_token'])) {
		die('Error: CSRF Token is invalid');
	}
}

$hidden_csrf_token = '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
