<?php

/* PERMISSION DENIED */
if($_SESSION['user_nick'] == "") {

	$text = se_get_textlib("no_access",$languagePack,'all');
	$smarty->assign('page_content', $text, true);

} else {

	$get_my_userdata = get_my_userdata();

    /* update billing and delivery data */
    if(isset($_POST['update_ba_data'])) {
        foreach($_POST as $key => $val) {
            $$key = sanitizeUserInputs($val);
        }

        $update_ba_data = $db_user->update("se_user", [
            "ba_company" => "$ba_company",
            "ba_firstname" => "$ba_firstname",
            "ba_lastname" => "$ba_lastname",
            "ba_street" => "$ba_street",
            "ba_street_nbr" => "$ba_street_nbr",
            "ba_zip" => "$ba_zip",
            "ba_city" => "$ba_city",
            "ba_country" => "$ba_country"
        ], [
            "user_id" => (int) $_SESSION['user_id']
        ]);

        if($update_ba_data->rowCount() == 1){
            $smarty->assign("msg_status","alert alert-success",true);
            $smarty->assign("register_message",$lang['msg_update_profile'],true);
        } else {
            $smarty->assign("msg_status","alert alert-danger",true);
            $smarty->assign("register_message",$lang['msg_update_profile_error'],true);
        }
    }

    /* update address data */
    if(isset($_POST['update_address_data'])) {
        foreach ($_POST as $key => $val) {
            $$key = sanitizeUserInputs($val);
        }

        $update_address_data = $db_user->update("se_user", [
            "user_firstname" => "$s_firstname",
            "user_lastname" => "$s_lastname",
            "user_street" => "$s_street",
            "user_street_nbr" => "$s_nr",
            "user_zip" => "$s_zip",
            "user_city" => "$s_city",
            "user_public_profile" => "$about_you"
        ], [
            "user_id" => (int) $_SESSION['user_id']
        ]);
        if($update_address_data->rowCount() == 1){
            $smarty->assign("msg_status","alert alert-success",true);
            $smarty->assign("register_message",$lang['msg_update_profile'],true);
        } else {
            $smarty->assign("msg_status","alert alert-danger",true);
            $smarty->assign("register_message",$lang['msg_update_profile_error'],true);
        }
    }

    /* update password */
    if(isset($_POST['update_psw_data'])) {
        $user_psw_hash = $get_my_userdata['user_psw_hash'];
        if(isset($_POST['s_psw']) AND trim($_POST['s_psw']) != '') {
            /* USER SEND NEW PSW */
            if($_POST['s_psw'] == $_POST['s_psw_repeat']) {
                $user_psw_hash = password_hash($_POST['s_psw'], PASSWORD_DEFAULT);
            }
        }
        $update_psw = $db_user->update("se_user", [
            "user_psw_hash" => "$user_psw_hash"
        ], [
            "user_id" => (int) $_SESSION['user_id']
        ]);

        if($update_psw->rowCount() == 1){
            $smarty->assign("msg_status","alert alert-success",true);
            $smarty->assign("register_message",$lang['msg_update_profile'],true);
        } else {
            $smarty->assign("msg_status","alert alert-danger",true);
            $smarty->assign("register_message",$lang['msg_update_profile_error'],true);
        }
    }



        /* update email data */
    if(isset($_POST['update_email_data'])) {

        $new_user_mail = '';

        if(isset($_POST['set_mail']) AND trim($_POST['set_mail']) != '') {
            if($_POST['set_mail'] == $_POST['set_mail_repeat']) {
                $new_user_mail = sanitizeUserInputs($_POST['set_mail']);
            }
        }

        /* check if mail exists */
        $all_registered_mails = get_all_usermail();
        foreach ($all_registered_mails as $user_mails) {
            if($new_user_mail == $user_mails['user_mail']) {
                // the address is already registered - do nothing
                $new_user_mail = '';
            }
        }

        if($new_user_mail != '') {
            $update_mail = $db_user->update("se_user", [
                "user_mail" => "$new_user_mail"
            ], [
                "user_id" => (int) $_SESSION['user_id']
            ]);

            if($update_mail->rowCount() == 1){
                $smarty->assign("msg_status","alert alert-success",true);
                $smarty->assign("register_message",$lang['msg_update_profile'],true);
            } else {
                $smarty->assign("msg_status","alert alert-danger",true);
                $smarty->assign("register_message",$lang['msg_update_profile_error'],true);
            }

        }

    }


	/**
	 * upload avatar
	 * convert to png and square format
	 * rename file to md5(username)
	 */

	if(isset($_POST['upload_avatar'])) {
		
		$upload_avatar = se_upload_avatar($_FILES,$_SESSION['user_nick']);
		if($upload_avatar === true) {
			$smarty->assign("msg_status","alert alert-success",true);
			$smarty->assign("register_message",$lang['msg_upload_avatar_success'],true);
		} else {
			$smarty->assign("msg_status","alert alert-danger",true);
			$smarty->assign("register_message",$lang['msg_upload_avatar_filetype'],true);
		}
			
	}
	

	/**
	 * DELETE THE ACCOUNT
	 * We delete all informations, except the user name and user id
	 */
	
	if(isset($_POST['delete_my_account'])) {

		$delete_id = (int) $_SESSION['user_id'];
			
		$count = $db_user->update("se_user", [
				"user_firstname" => "",
				"user_lastname" => "",
				"user_street" => "",
				"user_street_nbr" => "",
				"user_zip" => "",
				"user_city" => "",
				"user_public_profile" => "",
				"user_psw_hash" => "",
				"user_psw" => "",
				"user_mail" => "",
				"user_registerdate" => "",
				"user_drm" => "",
				"user_verified" => "",
				"user_class" => "deleted"
					], [
				"user_id" => $delete_id
			]);
		
		if($count->rowCount() == 1) {
			$smarty->assign("msg_status","alert alert-success",true);
			$smarty->assign("register_message",$lang['msg_delete_account_success'],true);
			session_destroy();
			unset($_SESSION['user_nick']);
		} else {
			$smarty->assign("msg_status","alert alert-warning",true);
			$smarty->assign("register_message",$lang['msg_delete_account_error'],true);
		}
	}
	
	
	
	
	// show data in form	
	if(is_file("content/avatars/".md5($_SESSION['user_nick']) . ".png")) {

		$avatar_url = SE_INCLUDE_PATH . "/content/avatars/".md5($_SESSION['user_nick']) . ".png";
		$smarty->assign("avatar_url","$avatar_url");
		
		$link_avatar_delete_url = $se_base_url.'profile/';
		$link_avatar_delete = '<a href="'.$link_avatar_delete_url.'">'.$lang['link_delete_avatar'].'</a>';
		$link_avatar_delete_text = $lang['link_delete_avatar'];
		
		$smarty->assign("link_avatar_delete","$link_avatar_delete",true);
		$smarty->assign("link_avatar_delete_url","$link_avatar_delete_url",true);
		$smarty->assign("link_avatar_delete_text","$link_avatar_delete_text",true);
	}
	
	/* delete avatar */
	if(isset($_POST['delete_avatar'])) {
		unlink("content/avatars/".md5($_SESSION['user_nick']) . ".png");
		$smarty->assign("avatar_url","",true);
		$smarty->assign("link_avatar_delete","",true);
	}
	
	
	if($page_contents['page_permalink'] != '') {
		$smarty->assign("form_url", '/'.$page_contents['page_permalink']);
	} else {
		$form_url = SE_INCLUDE_PATH . "/profile/";
		$smarty->assign('form_url', $form_url);
	}
	
	
	$get_my_userdata = get_my_userdata();

    /**
     * billing and delivery address - countries
     * show as select if there are predefined countries or as text input if not
     */
    $prefs_delivery_countries = json_decode($se_prefs['prefs_delivery_countries'],true);

    if(is_array($prefs_delivery_countries) && count($prefs_delivery_countries) > 0) {
        // show select
        $smarty->assign("show_ba_country_input","select");
        $smarty->assign("ba_countries",$prefs_delivery_countries);
        if($get_my_userdata['ba_country'] != '') {
            // select the country
            $selected_country = 'selected_'.strtolower($get_my_userdata['ba_country']);
            $smarty->assign("$selected_country","selected");
        }
    } else {
        // show input type text
        $smarty->assign("show_ba_country_input","input");
    }

	$smarty->assign("user_nick",$_SESSION['user_nick'],true);

    $smarty->assign("ba_company",$get_my_userdata['ba_company'],true);
    $smarty->assign("ba_firstname",$get_my_userdata['ba_firstname'],true);
    $smarty->assign("ba_lastname",$get_my_userdata['ba_lastname'],true);
    $smarty->assign("ba_street",$get_my_userdata['ba_street'],true);
    $smarty->assign("ba_street_nbr",$get_my_userdata['ba_street_nbr'],true);
    $smarty->assign("ba_zip",$get_my_userdata['ba_zip'],true);
    $smarty->assign("ba_city",$get_my_userdata['ba_city'],true);
    $smarty->assign("ba_country",$get_my_userdata['ba_country'],true);

    $smarty->assign("get_mail_address",$get_my_userdata['user_mail'],true);
	$smarty->assign("get_firstname",$get_my_userdata['user_firstname'],true);
	$smarty->assign("get_lastname",$get_my_userdata['user_lastname'],true);
	$smarty->assign("get_street",$get_my_userdata['user_street'],true);
	$smarty->assign("get_nr",$get_my_userdata['user_street_nbr'],true);
	$smarty->assign("get_zip",$get_my_userdata['user_zip'],true);
	$smarty->assign("get_city",$get_my_userdata['user_city'],true);
	$smarty->assign("send_about",$get_my_userdata['user_public_profile'],true);
	
	$output = $smarty->fetch("profile_main.tpl",$cache_id);
	$smarty->assign('page_content', $output, true);

}