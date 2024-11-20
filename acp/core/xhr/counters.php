<?php


if($_REQUEST['count'] == 'pages') {
    $count = $db_content->count("se_pages");
    echo se_covert_big_int($count);
    exit;
}

if($_REQUEST['count'] == 'snippets') {
    $count = $db_content->count("se_snippets");
    echo se_covert_big_int($count);
    exit;
}

if($_REQUEST['count'] == 'posts') {
    $count = $db_posts->count("se_posts",["post_type"=>["m","i","v","l","g","f"]]);
    echo se_covert_big_int($count);
    exit;
}

if($_REQUEST['count'] == 'products') {
    $count = $db_posts->count("se_posts",["post_type"=>"p"]);
    echo se_covert_big_int($count);
    exit;
}

if($_REQUEST['count'] == 'events') {
    $count = $db_posts->count("se_posts",["post_type"=>"e"]);
    echo se_covert_big_int($count);
    exit;
}

if($_REQUEST['count'] == 'comments') {
    $count = $db_content->count("se_comments");
    echo se_covert_big_int($count);
    exit;
}

if($_REQUEST['count'] == 'users') {
    $count = $db_user->count("se_user");
    echo se_covert_big_int($count);
    exit;
}

if($_REQUEST['count'] == 'count_global_filters') {

    $cnt_global_filters = 0;
    if($_SESSION['global_filter_label'] != '') {
        $cnt_global_filters++;
    }

    if($_SESSION['global_filter_languages'] != '') {
        $cnt_global_filters++;
    }

    if($_SESSION['global_filter_status'] != '') {
        $cnt_global_filters++;
    }

    echo $cnt_global_filters;
    exit;
}
