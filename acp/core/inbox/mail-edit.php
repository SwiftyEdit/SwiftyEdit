<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['mailbox'].' '.$lang['nav_btn_mails'];
echo '</div>';

require '../acp/core/functions_inbox.php';

/**
 * send e-mail
 * we build the mail content via global function se_build_html_file()
 * we send the mail via global function se_send_mail()
 */
if(isset($_POST['send_mail'])) {
    $get_id = (int) $_POST['send_mail'];
    $get_message_data = se_inbox_get_message($get_id);

    $subject = htmlspecialchars($get_message_data['subject'],ENT_QUOTES, 'UTF-8');
    $content = htmlspecialchars($get_message_data['content'],ENT_QUOTES, 'UTF-8');

    if($get_message_data['recipients'] == 'all') {
        // get all active users
        $recipients = $db_user->select("se_user","*",[
            "user_verified" => "verified"
        ]);
    } else {
        // get users from user group
        $user_group_id = (int) $get_message_data['recipients'];
        $user_group_users = se_get_usergroup_by_id($user_group_id);
        $user_id = explode(" ", $user_group_users['group_user']);
        foreach($user_id as $id) {
            $recipients[] = se_get_userdata_by_id($id);
        }
    }
    /* loop through recipients and send the mail */
    $log = array();
    $x = 0;
    foreach($recipients as $recipient) {

        $send_to['mail'] = $recipient['user_mail'];
        $send_to['name'] = $recipient['user_nick'];

        $mail_data['subject'] = $subject;
        $mail_data['preheader'] = $subject;
        $mail_data['title'] = $subject;
        $mail_data['body'] = $content;
        $mail_tpl = se_build_html_file($mail_data);
        $send_mail = se_send_mail($send_to,$subject,$mail_tpl);

        if($send_mail == 1) {
            $log[$x]['status'] = 'sent';
        } else {
            $log[$x]['status'] = 'failed';
        }
        $log[$x]['time'] = time();
        $log[$x]['recipient'] = $send_to['mail'];
        $x++;
    }

    $json_log = json_encode($log,JSON_UNESCAPED_UNICODE);
    $db_posts->update("se_mailbox",[
        "log" => $json_log,
        "time_send" => time()
    ],[
        "id" => $get_id
    ]);

}

// save new mail
if(isset($_POST['save_mail'])) {
    $save_message = se_inbox_write_message($_POST);
    if($save_message > 0) {
        $edit_message_data = se_inbox_get_message((int) $save_message);
    }
}

// update mail
if(isset($_POST['edit_message'])) {
    $message_id = (int) $_POST['edit_message'];
    $edit_message_data = se_inbox_get_message($message_id);
}



// print the form
$mail_form_tpl = file_get_contents('../acp/templates/mail-form.tpl');
$section_url = '/admin/inbox/mail/edit/';

$btn_save = '<button class="btn btn-default w-100" name="save_mail" value="save">'.$lang['save'].'</button>';
$btn_close = '<button class="btn btn-default w-100" name="form_close">'.$lang['close'].'</button>';
$btn_send = '';

$mail_subject = '';
$mail_content = '';
$mail_id = '';
$checked_all = '';
$checked_marketing = '';

if(is_array($edit_message_data)) {
    // we are in edit mode
    $mail_subject = htmlspecialchars($edit_message_data['subject'],ENT_QUOTES, 'UTF-8');
    $mail_content = htmlspecialchars($edit_message_data['content'],ENT_QUOTES, 'UTF-8');
    $mail_id = (int) $edit_message_data['id'];
    $btn_save = '<button class="btn btn-default w-100" name="save_mail" value="update">'.$lang['update'].'</button>';
    $btn_send = '<button class="btn btn-primary" name="send_mail" value="'.$mail_id.'">'.$icon['paper_plane'].' '.$lang['btn_send_mail'].'</button>';
    if($edit_message_data['recipients'] == 'all') {
        $checked_all = 'checked';
    } else if($edit_message_data['recipients'] == 'marketing') {
        $checked_marketing = 'checked';
    }
    $mail_form_tpl = str_replace('{mail_form_status}',$lang['label_form_status_edit'],$mail_form_tpl);
}

$user_groups = se_get_usergroups();
if(is_array($user_groups) && count($user_groups) > 0) {
    foreach($user_groups as $group) {

        $sel = '';
        if($edit_message_data['recipients'] == $group['group_id']) {
            $sel = 'checked';
        }

        $list_usergroup .= '<div class="form-check">';
        $list_usergroup .= '<input class="form-check-input" type="radio" name="mail_recipients" value="'.$group['group_id'].'" id="'.md5($group['group_name']).'" '.$sel.'>';
        $list_usergroup .= '<label class="form-check-label" for="'.md5($group['group_name']).'">'.$group['group_name'].'</label>';
        $list_usergroup .= '</div>';
    }
}

$mail_form_tpl = str_replace('{mail_form_status}',$lang['label_form_status_new'],$mail_form_tpl);

$mail_form_tpl = str_replace('{mail_subject}',$mail_subject,$mail_form_tpl);
$mail_form_tpl = str_replace('{mail_content}',$mail_content,$mail_form_tpl);
$mail_form_tpl = str_replace('{mail_id}',$mail_id,$mail_form_tpl);
$mail_form_tpl = str_replace('{hidden_csrf}',$hidden_csrf_token,$mail_form_tpl);

$mail_form_tpl = str_replace('{btn_save_draft}',$btn_save,$mail_form_tpl);
$mail_form_tpl = str_replace('{btn_send}',$btn_send,$mail_form_tpl);
$mail_form_tpl = str_replace('{btn_close}',$btn_close,$mail_form_tpl);
$mail_form_tpl = str_replace('{formaction}',$section_url,$mail_form_tpl);
$mail_form_tpl = str_replace('{checked_all}',$checked_all,$mail_form_tpl);
$mail_form_tpl = str_replace('{checked_marketing}',$checked_marketing,$mail_form_tpl);
$mail_form_tpl = str_replace('{list_usergroups}',$list_usergroup,$mail_form_tpl);

$mail_form_tpl = str_replace('{lang_subject}',$lang['label_mail_subject'],$mail_form_tpl);
$mail_form_tpl = str_replace('{lang_text}',$lang['label_mail_text'],$mail_form_tpl);
$mail_form_tpl = str_replace('{lang_recipients}',$lang['label_mail_recipients'],$mail_form_tpl);
$mail_form_tpl = str_replace('{label_all_users}',$lang['label_mail_recipients_all'],$mail_form_tpl);
$mail_form_tpl = str_replace('{label_marketing_users}',$lang['label_mail_recipients_marketing'],$mail_form_tpl);

echo $mail_form_tpl;