<?php

/**
 * Password reset, step 2 (step 1 - requesting the mail - is app/xhr/password-reset.php)
 *
 * The link from the reset mail only shows a form to choose a new password.
 * Nothing is changed by opening the link (GET): mail scanners and link
 * prefetchers open links on their own, which used to reset the password.
 * The new password is set via POST (CSRF-checked in app/bootstrap.php).
 *
 * @var object $db_user
 * @var object $smarty
 * @var array $lang
 * @var array $se_settings
 * @var array $page_contents
 * @var string $languagePack
 */

$alert_reset = '';
$reset_token = '';
$reset_done = false;

if($page_contents['page_permalink'] != '') {
	$form_url = '/'.$page_contents['page_permalink'];
} else {
	$form_url = SE_INCLUDE_PATH . "/password/";
}

$token = $_POST['reset_token'] ?? $_GET['token'] ?? '';
$token = is_string($token) ? $token : '';

if($token !== '') {

	$userdata_array = get_userdata_by_token($token);

	if(!is_array($userdata_array)) {

		$smarty->assign("alert_text", $lang['msg_forgotten_psw_invalid_link']);
		$alert_reset = $smarty->fetch('alert/alert-danger.tpl');

	} else if(isset($_POST['set_new_psw'])) {

		$new_psw = is_string($_POST['new_psw'] ?? null) ? $_POST['new_psw'] : '';
		$new_psw_repeat = is_string($_POST['new_psw_repeat'] ?? null) ? $_POST['new_psw_repeat'] : '';

		if(trim($new_psw) === '' || $new_psw !== $new_psw_repeat) {

			// show the form again
			$reset_token = $token;
			$smarty->assign("alert_text", $lang['msg_register_pswrepeat_error']);
			$alert_reset = $smarty->fetch('alert/alert-danger.tpl');

		} else {

			// set the new password, invalidate the token and lift a login lock
			$db_user->update("se_user", [
				"user_psw_hash" => password_hash($new_psw, PASSWORD_DEFAULT),
				"user_reset_psw" => "",
				"user_reset_psw_expires" => 0,
				"user_failed_logins" => 0,
				"user_unlock_code" => "",
				"user_locked_until" => 0
			], [
				"user_id" => (int) $userdata_array['user_id']
			]);

			// inform the owner - without the password itself
			$email_content = se_get_snippet("mail_psw_updated","$languagePack",'content');
			if($email_content == '') {
				$email_content = $lang['forgotten_psw_mail_changed'];
			}
			$email_msg = str_replace("{USERNAME}", $userdata_array['user_nick'], $email_content);
			// older custom snippets may still contain the placeholder of the temporary password
			$email_msg = str_replace("{temp_psw}", "", $email_msg);

			$mail_data['tpl'] = 'mail.tpl';
			$mail_data['subject'] = $lang['forgotten_psw_mail_subject'].' '.$se_settings['pagetitle'];
			$mail_data['preheader'] = $lang['forgotten_psw_mail_subject'].' '.$se_settings['pagetitle'];
			$mail_data['title'] = $lang['forgotten_psw_mail_subject'].' '.$se_settings['pagetitle'];
			$mail_data['body'] = "$email_msg";

			$build_html_mail = se_build_html_file($mail_data);

			$recipient = array('name' => $userdata_array['user_nick'], 'mail' => $userdata_array['user_mail']);
			se_send_mail($recipient,$mail_data['subject'],$build_html_mail);

			$smarty->assign("alert_text", $lang['msg_forgotten_psw_changed']);
			$alert_reset = $smarty->fetch('alert/alert-success.tpl');
			$reset_done = true;
		}

	} else {
		// valid link - show the form for the new password
		$reset_token = $token;
	}
}

$smarty->assign("form_url", $form_url);
$smarty->assign("reset_token", htmlspecialchars($reset_token, ENT_QUOTES));
$smarty->assign("reset_done", $reset_done);
$smarty->assign("forgotten_psw","$lang[forgotten_psw]");
$smarty->assign("forgotten_psw_intro","$lang[forgotten_psw_intro]");
$smarty->assign("forgotten_psw_new_intro", $lang['forgotten_psw_new_intro']);
$smarty->assign("label_mail","$lang[label_mail]");
$smarty->assign("label_psw", $lang['label_psw']);
$smarty->assign("label_psw_repeat", $lang['label_psw_repeat']);
$smarty->assign("button_send","$lang[button_send]");
$smarty->assign("button_save", $lang['button_save']);
$smarty->assign("legend_ask_for_psw","$lang[legend_ask_for_psw]");
$smarty->assign("alert_reset","$alert_reset");

$output = $smarty->fetch("password.tpl");
$smarty->assign('page_content', $output);
