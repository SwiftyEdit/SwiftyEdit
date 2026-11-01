<?php

/**
 * messages in /admin/inbox/mail/, markup in acp/templates/inbox/mails.twig
 * deleted by the inbox data-writer, the send log is loaded into the modal by the data-reader
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

require_once __DIR__.'/../functions_inbox.php';

$decode = static fn($value): string => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

$messages = [];
foreach(se_inbox_get_messages() as $message) {

    // recipients: all users or a user group
    $recipients = $lang['label_all_users'];
    if(ctype_digit((string) $message['recipients'])) {
        $group = se_get_usergroup_by_id((int) $message['recipients']);
        $recipients = $decode($group['group_name'] ?? '#'.$message['recipients']);
    }

    $messages[] = [
        'id' => (int) $message['id'],
        'created' => se_format_datetime($message['time_created']),
        'lastedit' => se_format_datetime($message['time_lastedit']),
        'sent' => ((int) $message['time_send'] > 0) ? se_format_datetime($message['time_send']) : '',
        'author' => $decode($message['autor']),
        'subject' => $decode($message['subject']),
        'recipients' => $recipients,
        'group' => ctype_digit((string) $message['recipients'])
    ];
}

echo $twig->render('inbox/mails.twig', [
    'messages' => $messages,
    'reader_uri' => '/admin-xhr/inbox/read/',
    'writer_uri' => '/admin-xhr/inbox/write/'
]);
