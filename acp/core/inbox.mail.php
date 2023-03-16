<?php
//error_reporting(E_ALL ^E_NOTICE);
//prohibit unauthorized access

/**
 * this is not ready for productive use
 * @todo    add salutation to se_mailbox
 *          encode mail content correct
 *          delete old/unused messages
 */

require 'core/access.php';
require 'core/functions_inbox.php';

/**
 * @var array $lang global language
 * @var string $hidden_csrf_token csrf token input field
 */

$section_url = '?tn=inbox&sub=mailbox';
$show_form = false;

echo '<div class="subHeader d-flex">';
echo '<div class="d-flex">E-Mails</div>';
echo '<form action="'.$section_url.'" method="post" class="d-inline ms-auto">';
echo '<button class="btn btn-default text-success" name="new_mail">'.$lang['label_new_email'].'</button>';
echo $hidden_csrf_token;
echo '</form>';
echo '</div>';

/**
 * send e-mail
 * we build the mail content via global function se_build_html_file()
 * we send the mail via global function se_send_mail()
 */
if(isset($_POST['send_mail'])) {
    $get_id = (int) $_POST['send_mail'];
    $get_message_data = se_inbox_get_message($get_id);

    $subject = $get_message_data['subject'];
    $content = $get_message_data['content'];

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
    foreach($recipients as $recipient) {

        $send_to['mail'] = $recipient['user_mail'];
        $send_to['name'] = $recipient['user_nick'];

        $mail_data['subject'] = $subject;
        $mail_data['preheader'] = $subject;
        $mail_data['title'] = $subject;
        $mail_data['body'] = $content;
        $mail_tpl = se_build_html_file($mail_data);
        $send_mail = se_send_mail($send_to,$subject,$mail_tpl);
    }


}

/**
 * save new message
 */
if(isset($_POST['save_mail'])) {

    $save_message = se_inbox_write_message($_POST);

    if($save_message > 0) {
        $edit_message_data = se_inbox_get_message((int) $save_message);
    }

    $show_form = true;
}

/**
 * update message
 */
if(isset($_POST['edit_message'])) {
    $message_id = (int) $_POST['edit_message'];
    $edit_message_data = se_inbox_get_message($message_id);
    $show_form = true;
}


if(isset($_POST['new_mail'])) {
   $show_form = true;
}

if(isset($_POST['form_close'])) {
    $show_form = false;
}

if($show_form == true) {

    $mail_form_tpl = file_get_contents('templates/mail-form.tpl');

    $btn_save = '<button class="btn btn-default w-100" name="save_mail" value="save">'.$lang['save'].'</button>';
    $btn_close = '<button class="btn btn-default w-100" name="form_close">'.$lang['btn_close'].'</button>';
    $btn_send = '';

    $mail_subject = '';
    $mail_content = '';
    $mail_id = '';
    $checked_all = '';
    $checked_marketing = '';

    if(is_array($edit_message_data)) {
        // we are in edit mode
        $mail_subject = $edit_message_data['subject'];
        $mail_content = $edit_message_data['content'];
        $mail_id = $edit_message_data['id'];
        $btn_save = '<button class="btn btn-default w-100" name="save_mail" value="update">'.$lang['update'].'</button>';
        $btn_send = '<button class="btn btn-primary" name="send_mail" value="'.$mail_id.'">'.$icon['paper_plane'].' '.$lang['btn_send_email'].'</button>';
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

    $mail_form_tpl = str_replace('{lang_subject}',$lang['label_subject'],$mail_form_tpl);
    $mail_form_tpl = str_replace('{lang_text}',$lang['label_text'],$mail_form_tpl);
    $mail_form_tpl = str_replace('{lang_recipients}',$lang['recipients'],$mail_form_tpl);
    $mail_form_tpl = str_replace('{label_all_users}',$lang['label_all_users'],$mail_form_tpl);
    $mail_form_tpl = str_replace('{label_marketing_users}',$lang['label_marketing_users'],$mail_form_tpl);

    echo $mail_form_tpl;
} else {

    // list messages
    $all_messages = se_inbox_get_messages();
    $cnt_messages = count($all_messages);

    echo '<div class="card p-3">';
    echo '<table class="table table-sm">';
    echo '<tr>';
    echo '<td>'.$lang['label_time_created'].'</td>';
    echo '<td>'.$lang['label_time_lastedit'].'</td>';
    echo '<td>'.$lang['f_meta_author'].'</td>';
    echo '<td>'.$lang['label_subject'].'</td>';
    echo '<td>'.$lang['recipients'].'</td>';
    echo '<td></td>';
    echo '</tr>';
    for($i=0;$i<$cnt_messages;$i++) {

        $format_time = $se_prefs['prefs_dateformat'].' '.$se_prefs['prefs_timeformat'];
        $time_created = date("$format_time",$all_messages[$i]['time_created']);
        $time_lastedit = date("$format_time",$all_messages[$i]['time_lastedit']);

        $edit_btn  = '<form action="'.$section_url.'" method="POST">';
        $edit_btn .= '<button class="btn btn-sm btn-default" name="edit_message" value="'.$all_messages[$i]['id'].'">'.$lang['edit'].'</button>';
        $edit_btn .= $hidden_csrf_token;
        $edit_btn .= '</form>';

        // recipients
        if(is_numeric($all_messages[$i]['recipients'])) {
            $get_group = se_get_usergroup_by_id($all_messages[$i]['recipients']);
            $show_recipient = $icon['user_friends'].' '.$get_group['group_name'];
        } else {
            $show_recipient = $lang['label_all_users'];
        }



        echo '<tr>';
        echo '<td>'.$time_created.'</td>';
        echo '<td>'.$time_lastedit.'</td>';
        echo '<td>'.$all_messages[$i]['autor'].'</td>';
        echo '<td>'.$all_messages[$i]['subject'].'</td>';
        echo '<td>'.$show_recipient.'</td>';
        echo '<td class="text-end">'.$edit_btn.'</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';

}