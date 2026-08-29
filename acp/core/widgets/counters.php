<?php

// Read-only w.r.t. $_SESSION (only compared below, never assigned) - safe to
// release the session lock immediately. These counter badges fire alongside
// the dashboard's other hx-trigger="load" widgets, all sharing one
// PHPSESSID; without an early close here, the default file session handler
// would force them to queue up and run one at a time instead of
// concurrently. Do not add $_SESSION writes below without removing this first.
session_write_close();

if($_REQUEST['count'] == 'pages') {
    $count = $db_content->count("se_pages");
    se_plain_response(se_covert_big_int($count));
}

if($_REQUEST['count'] == 'snippets') {
    $count = $db_content->count("se_snippets");
    se_plain_response(se_covert_big_int($count));
}

if($_REQUEST['count'] == 'posts') {
    $count = $db_posts->count("se_posts",["post_type"=>["m","i","v","l","g","f"]]);
    se_plain_response(se_covert_big_int($count));
}

if($_REQUEST['count'] == 'products') {
    $count = $db_posts->count("se_products");
    se_plain_response(se_covert_big_int($count));
}

if($_REQUEST['count'] == 'orders') {
    $count = $db_content->count("se_orders");
    se_plain_response(se_covert_big_int($count));
}

if($_REQUEST['count'] == 'events') {
    $count = $db_posts->count("se_events");
    se_plain_response(se_covert_big_int($count));
}

if($_REQUEST['count'] == 'comments') {
    $count = $db_content->count("se_comments");
    se_plain_response(se_covert_big_int($count));
}

if($_REQUEST['count'] == 'users') {
    $count = $db_user->count("se_user");
    se_plain_response(se_covert_big_int($count));
}

if($_REQUEST['count'] == 'addons') {
    // Same filter se_get_addons('plugin') uses - only activated plugins,
    // not every plugin installed under plugins/.
    $count = $db_content->count("se_addons", ["addon_type" => "plugin"]);
    se_plain_response(se_covert_big_int($count).' '.$lang['status_active']);
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

    se_plain_response($cnt_global_filters);
}
