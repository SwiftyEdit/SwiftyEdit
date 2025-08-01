<?php

// list text filters for comments
if($_REQUEST['action'] == 'list_active_searches_comments') {
    if(isset($_SESSION['comments_text_filter']) AND $_SESSION['comments_text_filter'] != "") {
        unset($all_filter);
        $all_filter_orders = explode(" ", $_SESSION['comments_text_filter']);

        foreach($all_filter_orders as $f) {
            if($_REQUEST['rm_keyword'] == "$f") { continue; }
            if($f == "") { continue; }
            $btn_remove_keyword .= '<button class="btn btn-sm btn-default m-1" name="rmkey_comments" value="'.$f.'" hx-post="/admin-xhr/inbox/write/" hx-trigger="click" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button>';
        }
        if(isset($btn_remove_keyword)) {
            echo '<div class="d-inline">'.$btn_remove_keyword.'</div>';
        }
    }
    exit;
}

// list the comments
if($_REQUEST['action'] == 'list_comments') {

    // defaults
    $order_by = 'comment_time';
    $order_direction = 'DESC';
    $limit_start = $_SESSION['pagination_comments'] ?? 0;
    $nbr_show_items = 25;

    $match_str = $_SESSION['comments_text_filter'] ?? '';
    $order_key = $_SESSION['sorting_comments'] ?? $order_by;
    $order_direction = $_SESSION['sorting_comments_direction'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    $filter_base = [
        "AND" => [
            "comment_id[>]" => 0
        ]
    ];

    $filter_by_str = array();
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "comment_author[~]" => "%$f%",
                    "comment_author_mail[~]" => "%$f%",
                    "comment_text[~]" => "%$f%"
                ]
            ];
        }
    }

    $db_where = [
        "AND" => $filter_base+$filter_by_str
    ];

    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];

    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

    $comments_data_cnt = $db_content->count("se_comments", $db_where);


    $comments_data = $db_content->select("se_comments","*",
        $db_where+$db_order+$db_limit
    );

    $nbr_pages = ceil($comments_data_cnt/$nbr_show_items);

    echo '<div class="card p-3">';
    echo se_print_pagination('/admin-xhr/inbox/write/',$nbr_pages,$_SESSION['pagination_comments'],'10','','pagination_comments');

    foreach($comments_data as $comment) {

        $comment_time = se_format_datetime($comment['comment_time']);
        $comment_id = $comment['comment_id'];
        $comment_relation_id = $comment['comment_relation_id'];
        $comment_status = $comment['comment_status'];

        $btn_status_class = 'text-success';
        $status_icon = $icon['check'];
        if($comment_status == 1) {
            $btn_status_class = '';
            $status_icon = $icon['clock'];
        }

        echo '<div class="card mb-1" id="comid'.$comment_id.'">';
        echo '<div class="card-title">';
        echo '<span class="badge">#'.$comment_id.'</span> <small>'.$comment_time.'</small> '.$comment['comment_author'].' ['.$comment['comment_author_mail'].']';
        echo '</div>';
        echo '<div class="card-body">';
        echo $comment['comment_text'];

        echo '<div class="row">';
        echo '<div class="col-md-9">';
        if($comment['comment_type'] == 'p') {
            echo '<div hx-get="/admin-xhr/inbox/read/?get_page_title='.$comment_relation_id.'" hx-trigger="load">Loading page data ...</div>';
        }

        if($comment['comment_type'] == 'b') {
            echo '<div hx-get="/admin-xhr/inbox/read/?get_post_title='.$comment_relation_id.'" hx-trigger="load">Loading post data ...</div>';
        }

        echo '</div>';
        echo '<div class="col-md-3 text-end">';

        echo '<div class="btn-group me-2">';
        echo '<button hx-get="/admin-xhr/inbox/read/?open_comment='.$comment_id.'" hx-target="#comment-modal" hx-trigger="click" data-bs-toggle="modal" data-bs-target="#comment-modal" class="btn btn-sm btn-default">'.$icon['edit'].'</button>';
        echo '<button hx-post="/admin-xhr/inbox/write/" hx-trigger="click" hx-trigger="click" name="change_status" value="'.$comment_id.'" hx-swap="none" class="btn btn-sm btn-default '.$btn_status_class.'">'.$status_icon.'</button>';
        echo '</div>';
        echo '<button hx-post="/admin-xhr/inbox/write/" hx-trigger="click" hx-trigger="click" name="delete_comment" value="'.$comment_id.'" hx-target="#inbox-response" class="btn btn-sm btn-default text-danger">'.$icon['trash_alt'].'</button>';
        echo '</div>';
        echo '</div>';

        echo '</div>';
        echo '</div>';

    }

    echo '<div id="comment-modal" class="modal modal-blur fade" style="display: none" aria-hidden="false" tabindex="-1">';
    echo '<div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content"></div></div>';
    echo '</div>';

    echo '</div>';
}

// open comment in modal
if(isset($_REQUEST['open_comment'])) {

    $comment_id = (int) $_REQUEST['open_comment'];
    $get_comment = $db_content->get("se_comments","*",["comment_id" => $comment_id]);


    echo '<div class="modal-dialog modal-xl modal-dialog-centered">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Edit: #'.$comment_id.'</h5>
    </div>
    <div class="modal-body">';

    echo '<div id="response"></div>';

    echo '<form hx-post="/admin-xhr/inbox/write/" hx-target="#response" method="POST">';
    echo '<div class="form-group">';
    echo '<label>'.$lang['label_name'].'</label>';
    echo '<input type="text" class="form-control" name="comment_author" value="'.$get_comment['comment_author'].'">';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label>'.$lang['label_mail'].'</label>';
    echo '<input type="text" class="form-control" name="comment_author_mail" value="'.$get_comment['comment_author_mail'].'">';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label>'.$lang['label_comment'].'</label>';
    echo '<textarea class="form-control" name="comment_text" rows="10">'.$get_comment['comment_text'].'</textarea>';
    echo '</div>';
    echo '<button type="submit" class="btn btn-sm btn-primary" name="update_comment" value="'.$comment_id.'">'.$lang['update'].'</button>';
    echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
    echo '</form>';

    echo '</div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="htmx.trigger(\'body\', \'update_comments_list\')">'.$lang['close'].'</button>
    </div>
  </div>
</div>';

}

if(isset($_GET['get_page_title'])) {
    $page_id = (int) $_GET['get_page_title'];
    $page_title = $db_content->get("se_pages","page_title",['page_id'=>$page_id]);
    echo '<p class="my-0"><span class="text-muted">Page: '.$page_title.'</span></p>';
    exit;
}

if(isset($_GET['get_post_title'])) {
    $post_id = (int) $_GET['get_post_title'];
    $page_title = $db_posts->get("se_posts","post_title",['post_id'=>$post_id]);
    echo 'Post: '.$page_title;
    exit;
}