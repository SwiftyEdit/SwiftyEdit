<?php

//prohibit unauthorized access
require 'core/access.php';

// defaults
$show_form = "true";
$db_status = "unlocked";

if($_POST['edituser'] != "") {
	$edituser = (int) $_POST['edituser'];
} else {
	unset($edituser);
}


/**
 * if we have custom fields
 * expand the array ($pdo_fields...)
 */
 
if(preg_match("/custom_/i", implode(",", array_keys($_POST))) ){
  $custom_fields = get_custom_user_fields();
  $cnt_result = count($custom_fields);
  
  for($i=0;$i<$cnt_result;$i++) {
  	if(substr($custom_fields[$i],0,7) == "custom_") {
  		$cf = $custom_fields[$i];
  		$custom_fields[] = $cf;
  	}
  }      
}


/**
 * delete user
 * remove data from the database
 */

if(isset($_POST['delete_the_user'])) {

		
		$columns_update = [
			"user_psw_hash" => "",
			"user_mail" => "",
			"user_verified" => "",
			"user_registerdate" => "",
			"user_drm" => "",
			"user_class" => "deleted",
			"user_firstname" => "",
			"user_lastname" => "",
			"user_company" => "",
			"user_street" => "",
			"user_street_nbr" => "",
			"user_zip" => "",
			"user_city" => ""
		];
		
		/* add the custom fields */
		foreach($custom_fields as $f) {
			$columns_update[$f] = "";
		}
										
		$cnt_changes = $db_user->update("se_user",$columns_update, [
			"user_id" => $edituser
		]);
	
	if($cnt_changes->rowCount() > 0) {
		$success_message = $lang['msg_success_entry_delete'].'<br />';
		$show_form = "false";
		record_log($_SESSION['user_nick'],"deleted user id: $edituser","9");
	}
	
	unset($edituser);

} // EOL delete user



/**
 * new user or update user
 */

if($_POST['save_the_user']) {

	foreach($_POST as $key => $val) {
		if(is_string($val)) {
			$$key = strip_tags($val);
		}
	}
	
	// drm -string- to save in database
	$drm_string = "$drm_acp_pages|$drm_acp_files|$drm_acp_user|$drm_acp_system|$drm_acp_editpages|$drm_acp_editownpages|$drm_moderator|$drm_can_publish|$drm_acp_sensitive_files";
	
	$user_psw_new	= $_POST['user_psw_new'];
	$user_psw_reconfirmation = $_POST['user_psw_reconfirmation'];
	
	// check psw entries
	$set_psw = 'false';
	
	if($_POST['user_psw_new'] != "") {

		if($_POST['user_psw_new'] != $_POST['user_psw_reconfirmation']) {
			$db_status = "locked";
			$error_message .= $lang['msg_error_psw_not_match'].'<br>';
		} else {
			//generate password hash
			$user_psw = password_hash($_POST['user_psw_new'], PASSWORD_DEFAULT);
			$success_message .= $lang['msg_success_psw_changed'].'<br>';
			$set_psw = 'true';
		}

	}	
	
	// modus update
	if(is_numeric($edituser)) {
		

		/* unique check for user_nick and e-mail */
				
		$check_user = $db_user->get("se_user", "user_nick", [
			"user_nick" => "$user_nick",
			"user_id[!]" => $edituser
		]);
		
		if(is_array($check_user)) {
			if(count($check_user) > 0) {
				$error_message .= $lang['msg_info_user_exists'].'<br>';
				$db_status = "locked";
			}			
		}

        // user_unlocked_by_admin
        $user_verified_by_admin = 'no';
        if($_POST['user_unlocked_by_admin'] == "yes") {
            $user_verified_by_admin = 'yes';
        }

			
		if($db_status == "unlocked") {
			$columns_update = [
				"user_nick" => "$user_nick",
				"user_mail" => "$user_mail",
				"user_verified" => "$user_verified",
                "user_verified_by_admin" => "$user_verified_by_admin",
				"user_registerdate" => "$user_registerdate",
				"user_drm" => "$drm_string",
				"user_class" => "$drm_acp_class",
				"user_firstname" => "$user_firstname",
				"user_lastname" => "$user_lastname",
				"user_company" => "$user_company",
				"user_street" => "$user_street",
				"user_street_nbr" => "$user_street_nbr",
				"user_zip" => "$user_zip",
				"user_city" => "$user_city",
                "ba_firstname" => "$ba_firstname",
                "ba_lastname" => "$ba_lastname",
                "ba_company" => "$ba_company",
                "ba_street" => "$ba_street",
                "ba_street_nbr" => "$ba_street_nbr",
                "ba_zip" => "$ba_zip",
                "ba_city" => "$ba_city",
                "ba_mail" => "$ba_mail",
                "ba_tax_id_number" => "$ba_tax_id_number",
                "ba_tax_number" => "$ba_tax_number"
			];
			
			if($set_psw == "true") {
				$columns_update['user_psw_hash'] = "$user_psw";
			}
			
			/* add the custom fields */
			foreach($custom_fields as $f) {
				$columns_update[$f] = "${$f}";
			}
			
			
			$cnt_changes = $db_user->update("se_user",$columns_update, [
				"user_id" => $edituser
			]);
			
			if($_POST['deleteAvatar'] == 'on') {
				$user_avatar_path = '../content/avatars/' . md5($user_nick) . '.png';
				if(is_file($user_avatar_path)) {
					unlink($user_avatar_path);
				}
			}
										
			if($cnt_changes->rowCount() > 0) {
				$success_message .= $lang['msg_success_db_changed'].'<br>';
				record_log($_SESSION['user_nick'],"update user id: $edituser via acp","5");
			}
		}
	
	
	}
	
	
	//modus new user
	if(!is_numeric($edituser)) {

		$user_registerdate = time();
		
		/* unique check for user_nick and e-mail */
				
		$check_user = $db_user->get("se_user", "user_nick", [
			"user_nick" => "$user_nick"
		]);
		
		if(count((array) $check_user) > 0) {
			$error_message .= $lang['msg_info_user_exists'].'<br>';
			$db_status = "locked";
		}
		
		$check_mail = $db_user->get("se_user", "user_mail", [
			"user_mail" => "$user_mail"
		]);
		
		if(count((array) $check_mail) > 0) {
			$error_message .= $lang['msg.info.usermail_exists'].'<br>';
			$db_status = "locked";
		}
		
		if($user_nick == '') {
			$error_message .= $lang['msg_error_mandatory'].'<br>';
			$db_status = "locked";			
		}
		
		if($db_status == "unlocked") {
			
			$columns_new = [
				"user_nick" => "$user_nick",
				"user_mail" => "$user_mail",
				"user_verified" => "$user_verified",
				"user_registerdate" => "$user_registerdate",
				"user_psw_hash" => "$user_psw",
				"user_drm" => "$drm_string",
				"user_class" => "$drm_acp_class",
				"user_firstname" => "$user_firstname",
				"user_lastname" => "$user_lastname",
				"user_company" => "$user_company",
				"user_street" => "$user_street",
				"user_street_nbr" => "$user_street_nbr",
				"user_zip" => "$user_zipcode",
				"user_city" => "$user_city"
			];
			
			/* add the custom fields */
			foreach($custom_fields as $f) {
				$columns_new[$f] = "${$f}";
			}
			
			$cnt_changes = $db_user->insert("se_user",$columns_new);
		
			$edituser = $db_user->id();
		
		
			if($edituser > 0) {
				$success_message .= $lang['msg_success_new_record'].'<br>';
				record_log($_SESSION['user_nick'],"new user <i>$user_nick</i>","5");
			} else {
				print_r($db_user->errorInfo);
			}
													
			// don't show the form after saving
			$show_form = "false";
		}
	}
	
	
	/**
	 * upload avatar
	 * convert to png and square format
	 * rename file to md5(username)
	 */

	if((isset($_FILES['avatar'])) && ($_FILES['full_path'] != '')) {
		se_upload_avatar($_FILES,$user_nick);
	}
	
	
	/**
	 * update table se_groups
	 */
	
	
	if($db_status == "unlocked") {

		if($edituser != "") {
			$enter_user_id = $edituser;
		} else {
			$enter_user_id = $db_user->id();
		}
		
		$user_groups = $_POST['user_groups'];
		$this_group = $_POST['this_group']; // not checked checkbox (hidden field)
		$nbr_of_groups = $_POST['nbr_of_groups'];
		
		
		for($i=0;$i<$nbr_of_groups;$i++) {
		
			if($user_groups[$i] == "") {
				$user_groups[$i] = $this_group[$i];
				$sign_out = "true"; // delete user from this list
			} else {
				$sign_out = "false";
			}

		}
	}
	
}

/* EOL write data */


if($db_status == "locked") {
	unset($success_message);
}


//print message(s)

if($success_message != ""){
	echo"<div class='alert alert-success'>$success_message</div>";
}

if($error_message != ""){
	echo"<div class='alert alert-danger'>$error_message</div>";
}


if(is_numeric($edituser)){
	// modus update user
		
	$get_user = $db_user->get("se_user", "*", [
		"user_id" => "$edituser"
	]);
	
	foreach($get_user as $k => $v) {
	   $$k = stripslashes($v);
	}
	
	$user_avatar_path = '../content/avatars/' . md5($user_nick) . '.png';
	
	echo '<div class="subHeader">';
	echo '<h3>'.$lang['status_edit'].' - '.$user_nick.' <small>ID: '.$user_id.'</small></h3>';
	echo '</div>';
	
	$submit_button = "<input class='btn btn-success w-100' type='submit' name='save_the_user' value='$lang[update]'>";
		
	//no delete_button for myself
	if($user_nick != $_SESSION['user_nick']){
		$delete_button = '<button class="btn btn-danger btn-sm w-100" type="submit" name="delete_the_user" value="'.$user_id.'" onclick="return confirm(\''.$lang['msg_confirm_delete_user'].'\')">'.$icon['trash_alt'].'</button>';
	}

} else {
	// modus new user
	echo '<div class="subHeader">';
	echo '<h3>'.$lang['status_new'].'</h3>';
	echo '</div>';
	
	$submit_button = "<input class='btn btn-success w-100' type='submit' name='save_the_user' value='$lang[save]'>";
	$delete_button = "";
}

if($show_form == "true") {
	include 'core/user.edit_form.php';
}

?>