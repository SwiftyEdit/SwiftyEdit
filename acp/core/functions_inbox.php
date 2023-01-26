<?php

/**
* prohibit unauthorized access
*/
if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])){
    die ();
}


/**
 * @param $data
 * @return integer
 */
function se_inbox_write_message($data) {

    global $db_posts;
    $time = time();

    $subject = $data['mail_subject'];
    $content = $data['mail_content'];
    $recipients = $data['mail_recipients'];

    if($data['save_mail'] == 'update') {

        if(is_numeric($data['mail_id'])) {
            $mail_id = (int) $data['mail_id'];
            $data = $db_posts->update("se_mailbox", [
                "time_lastedit" =>  $time,
                "subject" =>  $subject,
                "content" => $content,
                "autor" => $_SESSION['user_nick'],
                "recipients" => $recipients
            ], [
                "id" => $mail_id
            ]);

            return $mail_id;
        }

    } else {

        $db_posts->insert("se_mailbox", [
            "time_created" =>  $time,
            "time_lastedit" => $time,
            "time_send" => 0,
            "autor" => $_SESSION['user_nick'],
            "subject" => $subject,
            "content" => $content,
            "recipients" => $recipients
        ]);

        $message_id = $db_posts->id();
        return $message_id;
    }


}

/**
 * @return mixed
 */

function se_inbox_get_messages() {

    global $db_posts;
    $messages = array();

    $messages = $db_posts->select("se_mailbox","*");

    return $messages;
}

/**
 * @param integer $id
 * @return array
 */
function se_inbox_get_message($id) {

    global $db_posts;
    $message = $db_posts->get("se_mailbox","*",[
        'id' => $id
    ]);

    return $message;
}