<?php

$mail = strip_tags($_POST['mail']);
$send_data = 'false';
$msg_mail_format = '';

//check existing E-Mail Adresses
$all_usermail_array = array();

if(!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    $msg_mail_format = $lang['msg_invalid_mail_format'];
} else {
    $all_usermail_array = get_all_usermail($se_db_user);

    foreach($all_usermail_array as $entry) {
        if($mail == $entry['user_mail']) {
            $send_data = "true";
            break;
        }
    }
}


// send E-Mail
if($send_data == "true") {

    $userdata_array = get_userdata_by_mail($mail);
    $user_nick = $userdata_array['user_nick'];
    $user_registerdate = $userdata_array['user_registerdate'];

    /* unique token user_registerdate + user_mail */
    $reset_token = bin2hex(random_bytes(16));
    $reset_link = $se_base_url."password/?token=$reset_token";

    /* input token */
    $db_user->update("se_user", [
        "user_reset_psw" => "$reset_token"
    ], [
        "user_mail" => $mail
    ]);

    /* generate the message */

    $email_content = se_get_textlib("account_reset_psw","$languagePack",'content');
    if($email_content == '') {
        $email_content = $lang['forgotten_psw_mail_info'];
    }

    $email_msg = str_replace("{USERNAME}","$user_nick",$email_content);
    $email_msg = str_replace("{RESET_LINK}","$reset_link",$email_msg);

    $mail_data['tpl'] = 'mail.tpl';
    $mail_data['subject'] = $lang['forgotten_psw_mail_subject'].' / '.$se_settings['pagetitle'];
    $mail_data['preheader'] = $lang['forgotten_psw_mail_subject'].' / '.$se_settings['pagetitle'];
    $mail_data['title'] = $lang['forgotten_psw_mail_subject'].' / '.$se_settings['pagetitle'];
    $mail_data['body'] = "$email_msg";

    $build_html_mail = se_build_html_file($mail_data);

    /* send register mail to the new user */

    $recipient = array('name' => $user_nick, 'mail' => $mail);
    $send_reset_mail = se_send_mail($recipient,$mail_data['subject'],$build_html_mail);

    $psw_message = $lang['msg_forgotten_psw_step1'];

} // eol send E-Mail

if($psw_message != "") {
    $smarty->assign("alert_text","$psw_message");
    $smarty->display('alert/alert-success.tpl');
}