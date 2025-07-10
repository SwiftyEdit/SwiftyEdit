<?php
//error_reporting(E_ALL ^E_NOTICE);

/**
 * Generate new temp Password
 * inform user via mail
 */

if($_GET['token'] != "") {

	$token = strip_tags($_GET['token']);
	
	unset($userdata_array);
	$userdata_array = get_userdata_by_token($token);
	
	if(!is_array($userdata_array)) {
		die('Error: unauthorized access');
	}
	
	$temp_psw = randpsw();
	
	$user_id = $userdata_array['user_id'];
	$user_nick = $userdata_array['user_nick'];
	$user_mail = $userdata_array['user_mail'];
	
	$update_user_psw = password_hash($temp_psw, PASSWORD_DEFAULT);
	
	
	$db_user->update("se_user", [
		"user_psw_hash" => "$update_user_psw",
		"user_reset_psw" => ""
	], [
		"user_id" => $user_id
	]);

    $email_content = se_get_textlib("mail_psw_updated","$languagePack",'content');
    if($email_content == '') {
        $email_content = $lang['forgotten_psw_mail_update'];
    }

	$email_msg = str_replace("{USERNAME}","$user_nick",$email_content);
	$email_msg = str_replace("{temp_psw}","$temp_psw",$email_msg);

	/* send register mail to the new user */

    $mail_data['tpl'] = 'mail.tpl';
    $mail_data['subject'] = $lang['forgotten_psw_mail_subject'].' '.$se_settings['pagetitle'];
    $mail_data['preheader'] = $lang['forgotten_psw_mail_subject'].' '.$se_settings['pagetitle'];
    $mail_data['title'] = $lang['forgotten_psw_mail_subject'].' '.$se_settings['pagetitle'];
    //$mail_data['salutation'] = "New Password | $user_nick";
    $mail_data['body'] = "$email_msg";

    $build_html_mail = se_build_html_file($mail_data);

	$recipient = array('name' => $user_nick, 'mail' => $user_mail);
	$send_reset_mail = se_send_mail($recipient,$mail_data['subject'],$build_html_mail);
	
	$psw_message = $lang['msg_forgotten_psw_step2'];
	
	if($psw_message != "") {
		$smarty->assign("msg_status","alert alert-info");
		$smarty->assign("psw_message","$psw_message");
	}


}

if($page_contents['page_permalink'] != '') {
	$smarty->assign("form_url", '/'.$page_contents['page_permalink']);
} else {
	$form_url = SE_INCLUDE_PATH . "/password/";
	$smarty->assign("form_url","$form_url");
}

$smarty->assign("forgotten_psw","$lang[forgotten_psw]");
$smarty->assign("forgotten_psw_intro","$lang[forgotten_psw_intro]");
$smarty->assign("label_mail","$lang[label_mail]");
$smarty->assign("button_send","$lang[button_send]");
$smarty->assign("legend_ask_for_psw","$lang[legend_ask_for_psw]");

$output = $smarty->fetch("password.tpl");
$smarty->assign('page_content', $output);

?>