<?php

/**
 * @var object $db_user
 * @var object $smarty
 * @var array $lang
 * @var array $se_settings
 * @var string $se_base_url
 * @var string $languagePack
 */

// Doesn't touch $_SESSION at all - safe to release the session lock
// immediately, so this request doesn't block other widgets sharing the
// same PHPSESSID (default file session handler serializes them otherwise).
// Do not add $_SESSION usage below without removing this first.
session_write_close();

$mail = trim(strip_tags(is_string($_POST['mail'] ?? null) ? $_POST['mail'] : ''));

if(!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    $smarty->assign("alert_text", $lang['msg_invalid_mail_format']);
    $smarty->display('alert/alert-danger.tpl');
    exit;
}

// throttle: per client and per address, so neither a single client nor
// many clients together can flood an inbox with reset mails
if(se_rate_limit_exceeded('reset', 5, 900) || se_rate_limit_exceeded('reset-mail:'.strtolower($mail), 3, 3600, false)) {
    $smarty->assign("alert_text", $lang['msg_forgotten_psw_throttled']);
    $smarty->display('alert/alert-danger.tpl');
    exit;
}
se_rate_limit_add('reset', 900);
se_rate_limit_add('reset-mail:'.strtolower($mail), 3600, false);

$userdata_array = get_userdata_by_mail($mail);

// send E-Mail - only for existing, verified accounts, but the answer below is
// the same either way, so this form can't be used to test which addresses exist
if(is_array($userdata_array)) {

    $user_nick = $userdata_array['user_nick'];

    // the link carries the token, the database only a hash of it - valid for one hour
    $reset_token = bin2hex(random_bytes(32));
    $reset_link = $se_base_url."password/?token=$reset_token";

    $db_user->update("se_user", [
        "user_reset_psw" => hash('sha256', $reset_token),
        "user_reset_psw_expires" => time() + 3600
    ], [
        "user_id" => (int) $userdata_array['user_id']
    ]);

    /* generate the message */
    $email_content = se_get_snippet("account_reset_psw","$languagePack",'content');
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

    $recipient = array('name' => $user_nick, 'mail' => $mail);
    se_send_mail($recipient,$mail_data['subject'],$build_html_mail);
}

$smarty->assign("alert_text", $lang['msg_forgotten_psw_step1']);
$smarty->display('alert/alert-success.tpl');
