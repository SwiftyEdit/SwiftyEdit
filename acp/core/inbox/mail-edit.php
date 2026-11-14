<?php

/**
 * new or edited message in /admin/inbox/mail/new/ and /admin/inbox/mail/edit/{id}/,
 * markup in acp/templates/inbox/mail-form.twig - saved and sent by the inbox data-writer
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var string $query
 * @var object $twig
 */

require_once __DIR__.'/../functions_inbox.php';

// the id is the last part of the url
$segments = explode('/', rtrim((string) parse_url($query, PHP_URL_PATH), '/'));
$message_id = (int) end($segments);
$message = $message_id > 0 ? se_inbox_get_message($message_id) : null;

$mail = [
    'id' => 0,
    'subject' => '',
    'recipients' => 'all',
    'sent' => ''
];
if($message !== null) {
    $mail = [
        'id' => (int) $message['id'],
        'subject' => html_entity_decode((string) $message['subject'], ENT_QUOTES, 'UTF-8'),
        'recipients' => (string) $message['recipients'],
        'sent' => ((int) $message['time_send'] > 0) ? se_format_datetime($message['time_send']) : ''
    ];
}

// the content is html (se_purify_html), the editor field gets it encoded
$content_input = se_print_form_input([
    "input_name" => "mail_content",
    "input_value" => htmlspecialchars((string) ($message['content'] ?? ''), ENT_QUOTES, 'UTF-8'),
    "label" => $lang['label_mail_text'],
    "type" => "textarea",
    "mode" => "wysiwyg"
]);

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
    'content_input' => $content_input,
    'user_groups' => $user_groups,
    'writer_uri' => '/admin-xhr/inbox/write/'
]);
