<?php

/**
 * global variables
 * @var array $lang
 * @var object $db_user
 */

if(($_SESSION['user_class'] != 'administrator') && isset($_COOKIE['identifier']) && isset($_COOKIE['securitytoken'])) {
	$identifier = $_COOKIE['identifier'];
	$securitytoken = $_COOKIE['securitytoken'];
		
	$token_row = $db_user->get("se_tokens",["securitytoken","identifier","user_id"],["identifier" => $identifier]);
	
	//Token is correct
	if(sha1($securitytoken) == $token_row['securitytoken']) {
		// update Token
		$new_securitytoken = randpsw($length=24);
		$new_securitytoken_hashed = sha1($new_securitytoken);
		
		$db_user->update("se_tokens",[
			"securitytoken" => "$new_securitytoken_hashed"
			],[
			"identifier" => $identifier
		]);

        setcookie("identifier", $identifier, [
            'expires' => time() + (3600 * 24 * 365),
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        setcookie("securitytoken", $new_securitytoken, [
            'expires' => time() + (3600 * 24 * 365),
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

		$user_data = $db_user->select("se_user","*",[
			"user_id" => $token_row['user_id']
		]);
		se_start_user_session($user_data);
				
		$_SESSION['user_class'] = 'administrator';
	} else {
		header("location:../index.php");
		die("PERMISSION DENIED");
	}
}

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
	if(empty($_POST['csrf_token'])) {
		die('Error: CSRF Token is empty');
	}
	if($_POST['csrf_token'] !== $_SESSION['token']) {
		die('Error: CSRF Token is invalid');	
	}
}

$hidden_csrf_token = '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';