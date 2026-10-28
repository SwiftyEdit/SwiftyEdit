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
$mail = [
    'id' => '',
    'subject' => '',
    'content' => '',
    'recipients' => ''
];

if(is_array($edit_message_data ?? null)) {
    // we are in edit mode
    $mail = [
        'id' => (int) $edit_message_data['id'],
        'subject' => $edit_message_data['subject'],
        'content' => $edit_message_data['content'],
        'recipients' => (string) $edit_message_data['recipients']
    ];
}

// group names are stored html-encoded (sanitizeUserInputs), Twig escapes on output
$user_groups = [];
foreach(se_get_usergroups() ?: [] as $group) {
    $user_groups[] = [
        'id' => (string) $group['group_id'],
        'name' => html_entity_decode((string) $group['group_name'], ENT_QUOTES, 'UTF-8')
    ];
}

echo $twig->render('inbox/mail-form.twig', [
    'mail' => $mail,
    'user_groups' => $user_groups
]);