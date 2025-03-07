<?php



/**
 * Return all registered user -> user_nick
 */


function get_all_usernames() {
	
	global $db_user;
	
	$user_nicks = $db_user->select("se_user", [
		"user_nick"
	]);
   
	return $user_nicks;
}




/**
 * Return all registered user -> user_mail
 */

function get_all_usermail() {
	
	global $db_user;
	
	$user_mails = $db_user->select("se_user", [
		"user_mail"
	]);
   
	return $user_mails;	
}



/**
 * Return all user informations
 * from the database
 */

function get_my_userdata() {

	global $db_user;
	
	$user_data = $db_user->get("se_user", "*", [
		"AND" => [
			"user_id" => $_SESSION['user_id'],
			"user_verified" => "verified"
		]
	]);

	return $user_data;
}




/**
 * Return user information
 * input = E-Mail Adress
 */


function get_userdata_by_mail($mail) {
	
	global $db_user;
	
	$user_data = $db_user->get("se_user", "*", [
		"AND" => [
			"user_mail" => "$mail",
			"user_verified" => "verified"
		]
	]);

	return $user_data;
}






/**
 * Return user information
 * input = password-reset-token
 */

function get_userdata_by_token($token) {

	global $db_user;
	
	$user_data = $db_user->get("se_user", "*", [
		"AND" => [
			"user_reset_psw" => "$token"
		]
	]);

	return $user_data;
}








/**
 * Generate random Password
 */


function randpsw($length=8) {
	
	$chars = '123456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';

	$random_s = '';
	$cnt_chars = strlen($chars);
	for($i=0;$i<$length;$i++) {
		$random_s .= $chars[mt_rand(0, $cnt_chars - 1)];
	}
	return $random_s;
}



/**
 * user login
 */
 
function se_user_login($user,$psw,$acp=NULL,$remember=NULL) {

    if($user == '') {
        return 'failed';
    }

	global $db_user;
	
	$hash = $db_user->get("se_user", ["user_psw_hash"], [
		"AND" => [
			"user_nick" => "$user",
			"user_verified" => "verified"
		]
	]);
	
	if(password_verify($psw, $hash['user_psw_hash'])) {
		/* valid psw */
		
		$result = $db_user->get("se_user", "*", [
			"AND" => [
				"user_nick" => "$user",
				"user_verified" => "verified"
			]
		]);
		
	}
	
	//$cnt_result = count($result);
	
	//if($cnt_result>1) {
    if(is_array($result)) {

		se_start_user_session($result);

		/* set cookie to remember user */
		if($remember == TRUE) {
			$identifier = randpsw($length=24);
			$securitytoken = randpsw($length=24);
			$securitytoken_hashed = sha1($securitytoken);
			$time = time();
			
			$db_user->insert("se_tokens", [
				"user_id" => $result['user_id'],
				"identifier" => "$identifier",
				"securitytoken" => "$securitytoken_hashed",
				"time" => "$time"
			]);			
						
			setcookie("identifier",$identifier,time()+(3600*24*365));
			setcookie("securitytoken",$securitytoken,time()+(3600*24*365));		
		}

        if($_SESSION['user_class'] == 'administrator') {
            record_log("$user","admin logged in",1);
        }
		
		
		if(($acp == TRUE) AND ($_SESSION['user_class'] == "administrator")) {
			header("location:/admin/");
		}
		
		
	} else {
		session_destroy();
        return "failed";
	}

}


/**
 * start new session with user data
 */
 
function se_start_user_session($ud) {
	
	/* reset session id */
	session_regenerate_id(true);

	$_SESSION['user_id'] = $ud['user_id'];
	$_SESSION['user_nick'] = $ud['user_nick'];
	$_SESSION['user_mail'] = $ud['user_mail'];
	$_SESSION['user_class'] = $ud['user_class'];
	$_SESSION['user_psw'] = $ud['user_psw'];
	$_SESSION['user_firstname'] = $ud['user_firstname'];
	$_SESSION['user_lastname'] = $ud['user_lastname'];
	$_SESSION['user_hash'] = md5($ud['user_nick']);
	
	/* CSRF Protection */
    if(empty($_SESSION['token'])) {
        $token = md5(uniqid(rand(), TRUE));
        $_SESSION['token'] = $token;
        $_SESSION['token_time'] = time();
    }
	
	$arr_drm = explode("|", $ud['user_drm']);
	
	if($arr_drm[0] == "drm_acp_pages")	{  $_SESSION['acp_pages'] = "allowed";  }
	if($arr_drm[1] == "drm_acp_files")	{  $_SESSION['acp_files'] = "allowed";  }
	if($arr_drm[2] == "drm_acp_user")	{  $_SESSION['acp_user'] = "allowed";  }
	if($arr_drm[3] == "drm_acp_system")	{  $_SESSION['acp_system'] = "allowed";  }
	if($arr_drm[4] == "drm_acp_editpages")	{  $_SESSION['acp_editpages'] = "allowed";  }
	if($arr_drm[5] == "drm_acp_editownpages")	{  $_SESSION['acp_editownpages'] = "allowed";  }
	if($arr_drm[6] == "drm_moderator")	{  $_SESSION['drm_moderator'] = "allowed";  }
	if($arr_drm[7] == "drm_can_publish")	{  $_SESSION['drm_can_publish'] = "true";  }
	if($arr_drm[8] == "drm_acp_sensitive_files")	{  $_SESSION['drm_acp_sensitive_files'] = "allowed";  }
	
}


/**
 * logout
 * end user session
 */

function se_end_user_session() {
	
	global $db_user;

	if(is_numeric($_SESSION['user_id'])) {
		// delete data from se_tokens
		$db_user->delete("se_tokens",[
			"AND" => [
			"user_id" => $_SESSION['user_id']
			]
		]);
		
		unset($_COOKIE['identifier']);
		unset($_COOKIE['securitytoken']);
		unset($_COOKIE['permit_cookies']);
		setcookie('identifier', '', 1);
		setcookie('securitytoken', '', 1);
		setcookie('permit_cookies', '', 1);
		$cookiesSet = array_keys($_COOKIE);
		for ($x=0;$x<count($cookiesSet);$x++) setcookie($cookiesSet[$x],"",1);
	}
	session_destroy();
	unset($_SESSION['user_nick']);
	setcookie("PHPSESSID", "", 1);
	
	return 'logout';
	
}