<?php

/**
 * SwiftyEdit /admin-xhr/inbox/write/
 * comments: search, pagination, edit, status and delete - reactions: filter and delete
 * - mails: save, send and delete
 *
 * global variables
 * @var array $lang
 * @var object $db_content
 */

// comments of pages (p), posts (b) and products (c), see the inbox data-reader
$comment_types = ['p', 'b', 'c'];

// pagination comments
if(isset($_POST['pagination_comments'])) {
    $_SESSION['pagination_comments'] = (int) $_POST['pagination_comments'];
    header("HX-Trigger: update_comments_list");
    exit;
}

// text search in comments
if(isset($_POST['comments_text_filter'])) {
    $add_text_filter = sanitizeUserInputs($_POST['comments_text_filter']);
    $text_filter = $_SESSION['comments_text_filter'] ?? '';
    if($add_text_filter != '' && !in_array($add_text_filter, explode(' ', $text_filter))) {
        $_SESSION['comments_text_filter'] = trim($text_filter.' '.$add_text_filter);
    }
    $_SESSION['pagination_comments'] = 0;
    header("HX-Trigger: update_comments_list");
    exit;
}

// remove keyword from comments filter list
if(isset($_POST['rmkey_comments'])) {
    // the keywords are stored sanitized (see above), the button sends the plain keyword
    $remove_keyword = sanitizeUserInputs($_POST['rmkey_comments']);
    $keywords = explode(' ', $_SESSION['comments_text_filter'] ?? '');
    $keywords = array_filter($keywords, static fn($keyword) => $keyword != '' && $keyword != $remove_keyword);
    $_SESSION['comments_text_filter'] = implode(' ', $keywords);
    $_SESSION['pagination_comments'] = 0;
    header("HX-Trigger: update_comments_list");
    exit;
}

// update comment, stored like the frontend stores comments (sanitizeUserInputs)
if(isset($_POST['update_comment'])) {

    $comment_mail = trim((string) ($_POST['comment_author_mail'] ?? ''));
    if($comment_mail != '' && !filter_var($comment_mail, FILTER_VALIDATE_EMAIL)) {
        show_toast($lang['msg_invalid_mail_format'], 'danger');
        exit;
    }

    $db_content->update("se_comments", [
        "comment_author" => sanitizeUserInputs((string) ($_POST['comment_author'] ?? '')),
        "comment_author_mail" => sanitizeUserInputs($comment_mail),
        "comment_text" => sanitizeUserInputs((string) ($_POST['comment_text'] ?? '')),
        "comment_lastedit" => time(),
        "comment_lastedit_from" => $_SESSION['user_nick']
    ], [
        "comment_id" => (int) $_POST['update_comment'],
        "comment_type" => $comment_types
    ]);

    show_toast($lang['msg_success_db_changed'], 'success');
    header("HX-Trigger: update_comments_list");
    exit;
}

// delete comment
if(isset($_POST['delete_comment'])) {
    $delete = $db_content->delete("se_comments", [
        "comment_id" => (int) $_POST['delete_comment'],
        "comment_type" => $comment_types
    ]);
    if($delete !== null && $delete->rowCount() > 0) {
        show_toast($lang['msg_success_entry_delete'], 'success');
    }
    header("HX-Trigger: update_comments_list");
    exit;
}

// save a message, a new one opens its edit page
if(isset($_POST['save_mail'])) {
    require_once __DIR__.'/../functions_inbox.php';

    if(trim((string) ($_POST['mail_subject'] ?? '')) === '') {
        show_toast($lang['msg_error_no_title'], 'danger');
        exit;
    }

    $is_new = ((int) ($_POST['mail_id'] ?? 0) < 1);
    $mail_id = se_inbox_write_message($_POST);
    if($is_new && $mail_id > 0) {
        header("HX-Redirect: /admin/inbox/mail/edit/$mail_id/");
        exit;
    }
    show_toast($lang['msg_success_db_changed'], 'success');
    exit;
}

// send the saved version of a message
if(isset($_POST['send_mail'])) {
    require_once __DIR__.'/../functions_inbox.php';

    $result = se_inbox_send_message((int) $_POST['send_mail']);
    if($result === null) {
        exit;
    }
    record_log($_SESSION['user_nick'], "sent mail #".(int) $_POST['send_mail']." (sent: ".$result['sent'].", failed: ".$result['failed'].")", 5);
    show_toast($lang['msg_success_mails_sent'].' ('.$result['sent'].' / '.($result['sent'] + $result['failed']).')', $result['failed'] > 0 ? 'warning' : 'success');
    exit;
}

// delete a message, the list removes the row itself
if(isset($_POST['delete_message'])) {
    require_once __DIR__.'/../functions_inbox.php';
    se_inbox_delete_message((int) $_POST['delete_message']);
    exit;
}

// filter of the reactions: "type:id" of an entry, empty for all
if(isset($_POST['reactions_filter'])) {
    $filter = (string) $_POST['reactions_filter'];
    $_SESSION['reactions_filter'] = preg_match('/^[pbes]:\d+$/', $filter) ? $filter : '';
    header("HX-Trigger: update_reactions_list");
    exit;
}

// delete the selected votes - only votes, never comments
if(isset($_POST['delete_reactions'])) {
    $delete_ids = array_map('intval', (array) ($_POST['bulk_delete'] ?? []));
    $deleted = 0;
    if($delete_ids) {
        $delete = $db_content->delete("se_comments", [
            "comment_id" => $delete_ids,
            "comment_type" => ["upv", "dnv"]
        ]);
        $deleted = ($delete !== null) ? $delete->rowCount() : 0;
    }
    show_toast($lang['msg_info_data_deleted'].' ('.$deleted.')', 'info');
    header("HX-Trigger: update_reactions_list");
    exit;
}

// change status of a comment: 1 = waiting for an administrator, 2 = shown
if(isset($_POST['change_status'])) {

    $comment_id = (int) $_POST['change_status'];
    $get_status = $db_content->get("se_comments", "comment_status", [
        "comment_id" => $comment_id,
        "comment_type" => $comment_types
    ]);
    if($get_status === null) {
        exit;
    }

    $db_content->update("se_comments", [
        "comment_status" => ((int) $get_status == 2) ? 1 : 2
    ], [
        "comment_id" => $comment_id
    ]);

    show_toast($lang['msg_success_db_changed'], 'success');
    header("HX-Trigger: update_comments_list");
    exit;
}
