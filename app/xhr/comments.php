<?php

/**
 * ajax comments
 *
 * @var object $db_content medoo database object
 * @var object $smarty
 * @var array $lang
 * @var string $cache_id
 * @var array $filter
 * @var array $thread
 * @var array $comments
 * @var int $cnt_comment
 */

if(isset($_POST['send_user_comment'])) {

    $save_comment = se_write_comment($_POST);
    if($save_comment > 0) {
        echo 'comment saved ...';
        header( "HX-Trigger: update_comments, update_comment_posted");
    }
    exit;
}

if($_REQUEST['form'] == 'comments') {

    if(isset($_GET['page_id'])) {
        $page_id = (int) $_GET['page_id'];
        $smarty->assign("page_id",$page_id);
    }

    if(isset($_GET['post_id'])) {
        $post_id = (int) $_GET['post_id'];
        $smarty->assign("post_id",$post_id);
    }

    if(isset($_GET['parent_id'])) {
        $parent_id = (int) $_GET['parent_id'];
        $smarty->assign("parent_id",$parent_id);
    }

    if(isset($_GET['relation_id'])) {
        $post_id = (int) $_GET['relation_id'];
        $smarty->assign("post_id",$post_id);
    }

    $smarty->assign("label_name",$lang['label_name']);
    $smarty->assign("label_mail",$lang['label_mail']);
    $smarty->assign("label_mail_helptext",$lang['label_mail_helptext']);
    $smarty->assign("btn_send_comment",$lang['btn_send_comment']);

    $smarty->assign("input_name",$_SESSION['user_nick']);
    $smarty->assign("input_mail",$_SESSION['user_mail']);

    $smarty->display('comment_form.tpl',$cache_id);
    exit;
}



$show_thread = false;

if(isset($_GET['post_id'])) {
    $filter['relation_id'] = (int) $_GET['post_id'];
    $filter['type'] = 'b';
}

if(isset($_GET['page_id'])) {
    $filter['relation_id'] = (int) $_GET['page_id'];
    $filter['type'] = 'p';
}

if(is_array($filter)) {

    $comments = se_get_comments(0,100,$filter);
    $cnt_comment = count($comments);
    $thread = [];
    foreach($comments as $e) {
        se_build_thread_array($thread, $e);
    }

    $smarty->assign('show_page_comments', 'true', true);
    $smarty->assign('comments', $thread, true);
    $smarty->assign('lang_answer', $lang['btn_send_answer'], true);
    //$comment_tpl = $smarty->fetch("comment_entry.tpl",$cache_id);

    $smarty->display('comment_entry.tpl',$cache_id);
    exit;
}