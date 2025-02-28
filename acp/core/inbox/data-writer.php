<?php

// pagination comments
if(isset($_POST['pagination_comments'])) {
    $_SESSION['pagination_comments'] = (int) $_POST['pagination_comments'];
    header( "HX-Trigger: update_comments_list");
    exit;
}

// text search in comments
if(isset($_POST['comments_text_filter'])) {
    $_SESSION['comments_text_filter'] = $_SESSION['comments_text_filter'] . ' ' . sanitizeUserInputs($_POST['comments_text_filter']);
    header( "HX-Trigger: update_comments_list");
    exit;
}

// remove keyword from comments filter list
if(isset($_POST['rmkey_comments'])) {
    $all_filter = explode(" ", $_SESSION['comments_text_filter']);
    $_SESSION['comments_text_filter'] = '';
    foreach($all_filter as $f) {
        if($_POST['rmkey_comments'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['comments_text_filter'] .= "$f ";
    }
    header( "HX-Trigger: update_comments_list");
}

// update comment
if(isset($_POST['update_comment'])) {

    $update_id = (int) $_POST['update_comment'];
    $lastedit = time();
    $lastedit_from = $_SESSION['user_nick'];

    $update = $db_content->update("se_comments", [
        "comment_author" => $_POST['comment_author'],
        "comment_author_mail" => $_POST['comment_author_mail'],
        "comment_text" => $_POST['comment_text'],
        "comment_lastedit" => $lastedit,
        "comment_lastedit_from" => $lastedit_from
    ],[
        "comment_id" => $update_id
    ]);

    echo '<div class="alert alert-success">'.$lang['msg_success_db_changed'].'</div>';
}

// delete comment
if(isset($_POST['delete_comment'])) {
    $delete_id = (int) $_POST['delete_comment'];
    $delete = $db_content->delete("se_comments", [
        "comment_id" => $delete_id
    ]);
    if($delete->rowCount() > 0) {
        echo '<div class="alert alert-success">'.$lang['msg_success_entry_delete'].'</div>';
    }
    header( "HX-Trigger: update_comments_list");
}

// change status of a comment
if(isset($_POST['change_status'])) {

    $get_status = $db_content->get("se_comments", "comment_status", [
        "comment_id" => (int) $_POST['change_status']
    ]);

    $set_status = 2;
    if($get_status == 2) {
        $set_status = 1;
    }

    $update = $db_content->update("se_comments", [
        "comment_status" => $set_status
    ],[
        "comment_id" => $_POST['change_status']
    ]);
    header( "HX-Trigger: update_comments_list");
}
