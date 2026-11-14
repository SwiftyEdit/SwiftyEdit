<?php

/**
* prohibit unauthorized access
*/
if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])){
    die ();
}

/**
 * messages of the mailbox (/admin/inbox/mail/) - stored in se_mailbox (posts database)
 * subject: html-encoded (sanitizeUserInputs), content: html cleaned by se_purify_html(),
 * recipients: 'all' (all verified users) or the id of a user group
 */

/**
 * save a new message or update one
 *
 * @param array $data mail_subject, mail_content, mail_recipients, mail_id (update)
 * @return int id of the message, 0 if it could not be saved
 */
function se_inbox_write_message(array $data): int {

    global $db_posts;
    $time = time();

    $recipients = (string) ($data['mail_recipients'] ?? '');
    if($recipients !== 'all' && !(ctype_digit($recipients) && se_get_usergroup_by_id((int) $recipients))) {
        $recipients = 'all';
    }

    $values = [
        "time_lastedit" => $time,
        "autor" => $_SESSION['user_nick'],
        "subject" => sanitizeUserInputs((string) ($data['mail_subject'] ?? '')),
        "content" => se_purify_html($data['mail_content'] ?? ''),
        "recipients" => $recipients
    ];

    $mail_id = (int) ($data['mail_id'] ?? 0);
    if($mail_id > 0) {
        $db_posts->update("se_mailbox", $values, [
            "id" => $mail_id
        ]);
        return $mail_id;
    }

    $db_posts->insert("se_mailbox", $values + [
        "time_created" => $time,
        "time_send" => 0
    ]);
    return (int) $db_posts->id();
}

/**
 * @return array all messages, the newest first
 */
function se_inbox_get_messages(): array {

    global $db_posts;

    return $db_posts->select("se_mailbox", "*", [
        "ORDER" => ["id" => "DESC"]
    ]) ?: [];
}

/**
 * @param int $id
 * @return array|null
 */
function se_inbox_get_message(int $id): ?array {

    global $db_posts;

    $message = $db_posts->get("se_mailbox", "*", [
        'id' => $id
    ]);

    return is_array($message) ? $message : null;
}

/**
 * @param int $id
 * @return int number of deleted messages
 */
function se_inbox_delete_message(int $id): int {

    global $db_posts;

    $data = $db_posts->delete("se_mailbox", [
        'id' => $id
    ]);

    return $data !== null ? $data->rowCount() : 0;
}

/**
 * the users a message is sent to: all verified users or the members of a user group,
 * users without an e-mail address are left out
 *
 * @param string $recipients 'all' or the id of a user group
 * @return array list of ['mail' => ..., 'name' => ...]
 */
function se_inbox_get_recipients(string $recipients): array {

    global $db_user;

    if($recipients === 'all') {
        $users = $db_user->select("se_user", ["user_mail", "user_nick"], [
            "user_verified" => "verified"
        ]) ?: [];
    } else {
        $group = se_get_usergroup_by_id((int) $recipients);
        // the members are stored as space separated user ids
        $user_ids = array_filter(array_map('intval', explode(" ", (string) ($group['group_user'] ?? ''))));
        $users = $user_ids ? ($db_user->select("se_user", ["user_mail", "user_nick"], [
            "user_id" => array_values($user_ids)
        ]) ?: []) : [];
    }

    $list = [];
    foreach($users as $user) {
        if(filter_var((string) $user['user_mail'], FILTER_VALIDATE_EMAIL)) {
            $list[] = [
                'mail' => (string) $user['user_mail'],
                'name' => html_entity_decode((string) $user['user_nick'], ENT_QUOTES, 'UTF-8')
            ];
        }
    }
    return $list;
}

/**
 * send a message to its recipients and store the log (status, time and recipient of every mail)
 *
 * @param int $id id of the message
 * @return array ['sent' => int, 'failed' => int], null if there is no such message
 */
function se_inbox_send_message(int $id): ?array {

    global $db_posts;

    $message = se_inbox_get_message($id);
    if($message === null) {
        return null;
    }

    // the subject is stored html-encoded - fine for the html template, not for the mail header
    $subject_html = (string) $message['subject'];
    $subject = html_entity_decode($subject_html, ENT_QUOTES, 'UTF-8');

    // default template and footer of the theme (empty keys)
    $mail_tpl = se_build_html_file([
        'tpl' => '',
        'body_tpl' => '',
        'footer' => '',
        'salutation' => '',
        'subject' => $subject_html,
        'preheader' => $subject_html,
        'title' => $subject_html,
        'body' => (string) $message['content']
    ]);

    $log = [];
    $result = ['sent' => 0, 'failed' => 0];
    foreach(se_inbox_get_recipients((string) $message['recipients']) as $recipient) {
        $status = (se_send_mail($recipient, $subject, $mail_tpl) == 1) ? 'sent' : 'failed';
        $result[$status]++;
        $log[] = [
            'status' => $status,
            'time' => time(),
            'recipient' => $recipient['mail']
        ];
    }

    $db_posts->update("se_mailbox", [
        "log" => json_encode($log, JSON_UNESCAPED_UNICODE),
        "time_send" => time()
    ], [
        "id" => $id
    ]);

    return $result;
}
