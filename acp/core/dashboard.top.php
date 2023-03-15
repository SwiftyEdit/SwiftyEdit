<?php

//prohibit unauthorized access
require __DIR__.'/access.php';

$tpl_file = file_get_contents('templates/dashboard_top.tpl');

/* get latest infos from user database */

$user_result = $db_user->select("se_user", ["user_id", "user_nick", "user_firstname", "user_lastname", "user_mail", "user_registerdate"], [
    "ORDER" => ["user_id" => "DESC"]
]);


$cnt_user = count($user_result);

$tpl_file = str_replace('{cnt_all_user}', $cnt_user, $tpl_file);
$user_latest5 = '<table class="table">';


for ($i = 0; $i < 5; $i++) {

    if ($i >= $cnt_user) {
        continue;
    }

    $user_registerdate = @date("d.m.Y", intval($user_result[$i]['user_registerdate']));
    $user_id = $user_result[$i]['user_id'];
    $user_nick = $user_result[$i]['user_nick'];
    $user_name = $user_result[$i]['user_firstname'] . " " . $user_result[$i]['user_lastname'];

    $user_latest5 .= '<tr>';
    $user_latest5 .= '<td>' . $user_registerdate . '</td>';
    $user_latest5 .= '<td class="w-100">';
    $user_latest5 .= '<h6 class="mb-0">' . $user_nick . ' ('.$user_result[$i]['user_mail'].')</h6>';
    $user_latest5 .= '<small>' . $user_name . '</small> ';
    $user_latest5 .= '</td>';
    $user_latest5 .= '<td class="text-end">';
    $user_latest5 .= '<form class="inline" action="?tn=user&sub=edit" method="POST">';
    $user_latest5 .= '<button name="edituser" value=' . $user_id . '" class="btn btn-default btn-sm">' . $icon['edit'] . '</button>';
    $user_latest5 .= $hidden_csrf_token;
    $user_latest5 .= '</form>';
    $user_latest5 .= '</td>';
    $user_latest5 .= '</tr>';
}

$user_latest5 .= '</table>';


$allPages = $db_content->select("se_pages", ["page_id", "page_linkname", "page_title", "page_meta_description", "page_lastedit", "page_lastedit_from", "page_status"], [
    "ORDER" => ["page_lastedit" => "DESC"]
]);

$cnt_pages = count($allPages);
$tpl_file = str_replace('{cnt_all_pages}', $cnt_pages, $tpl_file);
$top5pages = '<table class="table">';
for ($i = 0; $i < 5; $i++) {

    if ($i >= $cnt_pages) {
        continue;
    }

    $page_id = $allPages[$i]['page_id'];

    $last_edit = @date("d.m.Y", $allPages[$i]['page_lastedit']);
    $page_linkname = $allPages[$i]['page_linkname'];
    $page_title = first_words($allPages[$i]['page_title'], 10);
    $page_teaser = first_words(strip_tags(html_entity_decode($allPages[$i]['page_meta_description'])), 10);
    $top5pages .= '<tr>';
    $top5pages .= '<td>'.$last_edit.'</td>';
    $top5pages .= '<td class="w-100"><h6 class="mb-0">' . $page_linkname . ' <small>'.$page_title.'</small></h6>'.$page_teaser.'</td>';
    $top5pages .= '<td class="text-end">';
    $top5pages .= '<form class="inline" action="?tn=pages&sub=edit" method="POST">';
    $top5pages .= '<button class="btn btn-default btn-sm" name="editpage" value="' . $allPages[$i]['page_id'] . '">' . $icon['edit'] . '</button>';
    $top5pages .= $hidden_csrf_token;
    $top5pages .= '</form>';
    $top5pages .= '</td>';

    $top5pages .= '</tr>';
}


$top5pages .= '</table>';

/* snippets */

$get_snippets = $db_content->select("se_snippets", ["snippet_id", "snippet_type", "snippet_name", "snippet_title", "snippet_lastedit"], [
    "OR" => [
        "snippet_type[~]" => ["snippet","snippet_core"]
    ],
    "ORDER" => ["snippet_lastedit" => "DESC"],
    "LIMIT" => 5
]);

$snippets_list = '<table class="table">';

foreach($get_snippets as $snippet) {
    $snippets_list .= '<tr>';
    $snippets_list .= '<td>'.date("d.m.Y",$snippet['snippet_lastedit']).'</td>';
    $snippets_list .= '<td class="w-100"><kbd>'.$snippet['snippet_name'].'</kbd> '.$snippet['snippet_name'].'</td>';
    $snippets_list .= '<td class="text-end">';
    $snippets_list .= '<form class="inline" action="?tn=pages&sub=snippets" method="POST">';
    $snippets_list .= '<button class="btn btn-default btn-sm" name="snip_id" value="' . $snippet['snippet_id'] . '">' . $icon['edit'] . '</button>';
    $snippets_list .= $hidden_csrf_token;
    $snippets_list .= '</form>';
    $snippets_list .= '</td>';
    $snippets_list .= '</tr>';
}

$snippets_list .= '</table>';

/* posts */

$allPosts = $db_posts->select("se_posts", ["post_id", "post_title", "post_teaser", "post_type", "post_lastedit"], [
    "OR" => [
        "post_type[~]" => ["m","v","i","g","f","l"]
    ],
    "ORDER" => ["post_releasedate" => "DESC"]
]);

$cnt_posts = count($allPosts);
$tpl_file = str_replace('{cnt_all_posts}', $cnt_posts, $tpl_file);
$top5posts = '<table class="table">';

for ($i = 0; $i < 5; $i++) {

    if ($i >= $cnt_posts) {
        continue;
    }

        $last_edit = @date("d.m.Y", $allPosts[$i]['post_lastedit']);
        $post_teaser = first_words(strip_tags(html_entity_decode($allPosts[$i]['post_teaser'])), 10);



    $top5posts .= '<tr>';
    $top5posts .= '<td>'.$last_edit.'</td>';
    $top5posts .= '<td class="w-100"><h6 class="mb-0">' . $allPosts[$i]['post_title'] . '</h6>'.$post_teaser.'</td>';
    $top5posts .= '<td class="text-end">';
    $top5posts .= '<form class="inline" action="?tn=posts&sub=edit" method="POST">';
    $top5posts .= '<button class="btn btn-default btn-sm" name="post_id" value="' . $allPosts[$i]['post_id'] . '">' . $icon['edit'] . '</button>';
    $top5posts .= $hidden_csrf_token;
    $top5posts .= '</form>';
    $top5posts .= '</td>';

    $top5posts .= '</tr>';


}

if ($cnt_posts < 1) {
    $top5posts = '<div class="alert alert-info">' . $lang['msg_no_entries_so_far'] . '</div>';
}

$top5posts .= '</table>';

/* products */

$allProducts = $db_posts->select("se_products", ["id", "title", "teaser", "type", "lastedit"], [
    "type[~]" => "p",
    "ORDER" => ["releasedate" => "DESC"]
]);
$cnt_products = count($allProducts);
$tpl_file = str_replace('{cnt_all_products}', $cnt_products, $tpl_file);
$list_product = '<table class="table">';

for ($i = 0; $i < 5; $i++) {
    if ($i >= $cnt_products) {
        continue;
    }

    $last_edit = @date("d.m.Y", $allProducts[$i]['lastedit']);
    $product_teaser = first_words(strip_tags(html_entity_decode($allProducts[$i]['teaser'])), 10);

    $list_product .= '<tr>';
    $list_product .= '<td>'.$last_edit.'</td>';
    $list_product .= '<td class="w-100"><h6 class="mb-0">' . $allProducts[$i]['title'] . '</h6>'.$product_teaser.'</td>';
    $list_product .= '<td class="text-end">';
    $list_product .= '<form class="inline" action="?tn=shop&sub=edit" method="POST">';
    $list_product .= '<button class="btn btn-default btn-sm" name="edit_id" value="' . $allProducts[$i]['id'] . '">' . $icon['edit'] . '</button>';
    $list_product .= $hidden_csrf_token;
    $list_product .= '</form>';
    $list_product .= '</td>';

    $list_product .= '</tr>';

}

$list_product .= '</table>';

if ($cnt_products < 1) {
    $list_product = '<div class="alert alert-info">' . $lang['msg_no_entries_so_far'] . '</div>';
}

$tpl_file = str_replace('{products_list}', $list_product, $tpl_file);

/* events */

$allEvents = $db_posts->select("se_events", ["id", "title", "teaser", "lastedit"], [
    "id[!]" => NULL,
    "ORDER" => ["date" => "DESC"]
]);

$cnt_events = count($allEvents);
$tpl_file = str_replace('{cnt_all_events}', $cnt_events, $tpl_file);

$list_events = '<table class="table">';

for ($i = 0; $i < 5; $i++) {
    if ($i >= $cnt_events) {
        continue;
    }

    $last_edit = @date("d.m.Y", $allEvents[$i]['lastedit']);
    $event_teaser = first_words(strip_tags(html_entity_decode($allEvents[$i]['teaser'])), 10);

    $list_events .= '<tr>';
    $list_events .= '<td>'.$last_edit.'</td>';
    $list_events .= '<td class="w-100"><h6 class="mb-0">' . $allEvents[$i]['title'] . '</h6>'.$event_teaser.'</td>';
    $list_events .= '<td class="text-end">';
    $list_events .= '<form class="inline" action="?tn=events&sub=edit" method="POST">';
    $list_events .= '<button class="btn btn-default btn-sm" name="id" value="' . $allEvents[$i]['id'] . '">' . $icon['edit'] . '</button>';
    $list_events .= $hidden_csrf_token;
    $list_events .= '</form>';
    $list_events .= '</td>';

    $list_events .= '</tr>';

}

$list_events .= '</table>';

if ($cnt_events < 1) {
    $list_events = '<div class="alert alert-info">' . $lang['msg_no_entries_so_far'] . '</div>';
}

$tpl_file = str_replace('{events_list}', $list_events, $tpl_file);

/* comments */

$allComments = $db_content->select("se_comments", ["comment_id", "comment_author", "comment_type", "comment_text", "comment_time"], [
    "ORDER" => ["comment_lastedit" => "DESC"]
]);

$cnt_comments = count($allComments);
$tpl_file = str_replace('{cnt_all_comments}', $cnt_comments, $tpl_file);

$top5comments = '<table class="table">';
for ($i = 0; $i < 5; $i++) {

    if ($i >= $cnt_comments) {
        continue;
    }

        $comment_time = @date("d.m.Y", intval($allComments[$i]['comment_time']));
        $comment_text = first_words(strip_tags(html_entity_decode($allComments[$i]['comment_text'])), 4);

        $top5comments .= '<tr>';
        $top5comments .= '<td>'.$comment_time.'</td>';
        $top5comments .= '<td class="w-100">';
        $top5comments .= '<h6 class="mb-0">' . $allComments[$i]['comment_author'] . ' '.$allComments[$i]['comment_type'].'</h6>';
        $top5comments .= '<small>' . $comment_text . '</small>';
        $top5comments .= '</td>';
        $top5comments .= '<td class="text-end">';
        $top5comments .= '<form class="inline" action="?tn=comments&sub=list#comid' . $allComments[$i]['comment_id'] . '" method="POST">';
        $top5comments .= '<button class="btn btn-default btn-sm" name="editid" value="' . $allComments[$i]['comment_id'] . '">' . $icon['edit'] . '</button>';
        $top5comments .= $hidden_csrf_token;
        $top5comments .= '</form>';
        $top5comments .= '</td>';
        $top5comments .= '</tr>';
}
$top5comments .= '</table>';

if ($cnt_comments < 1) {
    $top5comments = '<div class="alert alert-info">' . $lang['msg_no_entries_so_far'] . '</div>';
}




/* show logs */

$show_log = se_show_log(10);
$tpl_file = str_replace('{dashboard_logfile}', $show_log, $tpl_file);

/* system check messages */
$cnt_dashboard_messages = count($se_check_messages);

if($cnt_dashboard_messages < 1) {
    $dashboard_alerts = '<div class="alert alert-info">' . $lang['msg_no_entries_so_far'] . '</div>';
} else {
    $dashboard_alerts = '<table class="table table-sm">';
    for($i=0;$i<$cnt_dashboard_messages;$i++) {
        $dashboard_alerts .= '<tr>';
        $dashboard_alerts .= '<td>'.$se_check_messages[$i].'</td></tr>';
        $dashboard_alerts .= '</tr>';
    }
    $dashboard_alerts .= '</table>';
}

$tpl_file = str_replace('{dashboard_alerts}', $dashboard_alerts, $tpl_file);

$tpl_file = str_replace('{pages_list}', $top5pages, $tpl_file);
$tpl_file = str_replace('{snippets_list}', $snippets_list, $tpl_file);
$tpl_file = str_replace('{posts_list}', $top5posts, $tpl_file);
$tpl_file = str_replace('{comments_list}', $top5comments, $tpl_file);
$tpl_file = str_replace('{user_list}', $user_latest5, $tpl_file);
$tpl_file = str_replace('{pages_stats}', $pages_stats, $tpl_file);
$tpl_file = str_replace('{user_stats}', $user_stats, $tpl_file);

/* tabs */
$tpl_file = str_replace('{tab_pages}', $lang['nav_pages'], $tpl_file);
$tpl_file = str_replace('{tab_snippets}', $lang['nav_snippets'], $tpl_file);
$tpl_file = str_replace('{tab_products}', $lang['nav_products'], $tpl_file);
$tpl_file = str_replace('{tab_events}', $lang['nav_events'], $tpl_file);
$tpl_file = str_replace('{tab_orders}', $lang['nav_orders'], $tpl_file);

$tpl_file = str_replace('{tab_blog}', $lang['nav_blog'], $tpl_file);
$tpl_file = str_replace('{tab_comments}', $lang['nav_comments'], $tpl_file);

$tpl_file = str_replace('{tab_user}', $lang['tn_usermanagement'], $tpl_file);
$tpl_file = str_replace('{tab_user_stats}', $lang['h_status'], $tpl_file);

/* labels */

$tpl_file = str_replace('{label_pages}', $lang['nav_pages'], $tpl_file);
$tpl_file = str_replace('{label_user}', $lang['nav_user'], $tpl_file);
$tpl_file = str_replace('{label_posts}', $lang['nav_blog'], $tpl_file);
$tpl_file = str_replace('{label_products}', $lang['nav_products'], $tpl_file);
$tpl_file = str_replace('{label_events}', $lang['nav_events'], $tpl_file);
$tpl_file = str_replace('{label_comments}', $lang['nav_comments'], $tpl_file);

/* replace configs */
$tpl_file = str_replace('{val_server}', $_SERVER['SERVER_NAME'], $tpl_file);
$tpl_file = str_replace('{val_phpversion}', phpversion(), $tpl_file);
$tpl_file = str_replace('{val_database}', $db_type, $tpl_file);
$tpl_file = str_replace('{val_cms_domain}', $se_prefs['prefs_cms_domain'], $tpl_file);
$tpl_file = str_replace('{val_cms_ssl_domain}', $se_prefs['prefs_cms_ssl_domain'], $tpl_file);
$tpl_file = str_replace('{val_base_uri}', $se_prefs['prefs_cms_base'], $tpl_file);
$tpl_file = str_replace('{val_cms_mail}', $se_prefs['prefs_mailer_adr'], $tpl_file);
$tpl_file = str_replace('{val_cms_email_name}', $se_prefs['prefs_mailer_name'], $tpl_file);


$btn_page_overview = '<a href="?tn=pages" class="btn btn-default btn-sm w-100">' . $icon['sitemap'] . '</a>';
$btn_new_page = '<a href="?tn=pages&sub=new" class="btn btn-default btn-sm w-100">' . $icon['plus'] . ' ' . $lang['new'] . '</a>';

$btn_update_index = '<form action="?tn=dashboard" method="POST" class="d-inline"><button name="update_index" class="btn btn-default btn-sm w-100 text-nowrap">' . $icon['sync_alt'] . ' Index</button>' . $hidden_csrf_token . '</form>';
$btn_delete_cache = '<form action="?tn=dashboard" method="POST"><button name="delete_cache" class="btn btn-default btn-sm w-100 text-nowrap">' . $icon['trash_alt'] . ' Cache</button>' . $hidden_csrf_token . '</form>';

$btn_post_overview = '<a href="acp.php?tn=posts" class="btn btn-primary btn-sm w-100">' . $lang['tn_posts'] . '</a>';
$btn_new_post = '<a href="acp.php?tn=posts&sub=edit" class="btn btn-primary btn-sm w-100">' . $icon['plus'] . ' ' . $lang['new'] . '</a>';
$btn_comments_overview = '<a href="acp.php?tn=reactions" class="btn btn-primary btn-sm w-100">' . $lang['tn_comments'] . '</a>';

$btn_user_overview = '<a href="acp.php?tn=user" class="btn btn-primary btn-sm w-100">' . $lang['list_user'] . '</a>';
$btn_new_user = '<a href="acp.php?tn=user&sub=new" class="btn btn-primary btn-sm w-100">' . $icon['plus'] . ' ' . $lang['new_user'] . '</a>';

$tpl_file = str_replace('{btn_page_overview}', $btn_page_overview, $tpl_file);
$tpl_file = str_replace('{btn_new_page}', $btn_new_page, $tpl_file);
$tpl_file = str_replace('{btn_update_index}', $btn_update_index, $tpl_file);
$tpl_file = str_replace('{btn_delete_cache}', $btn_delete_cache, $tpl_file);

$tpl_file = str_replace('{btn_post_overview}', $btn_post_overview, $tpl_file);
$tpl_file = str_replace('{btn_new_post}', $btn_new_post, $tpl_file);
$tpl_file = str_replace('{btn_comments_overview}', $btn_comments_overview, $tpl_file);

$tpl_file = str_replace('{btn_user_overview}', $btn_user_overview, $tpl_file);
$tpl_file = str_replace('{btn_new_user}', $btn_new_user, $tpl_file);

echo $tpl_file;