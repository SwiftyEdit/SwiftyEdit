<?php

// dashboard data reader

// pages

if($_REQUEST['action'] === 'list_pages') {
    $getPages = $db_content->select("se_pages", [
        "page_id", "page_linkname", "page_title",
        "page_meta_description", "page_lastedit",
        "page_lastedit_from", "page_status"
    ], [
        "ORDER" => ["page_lastedit" => "DESC"],
        "LIMIT" => 10
    ]);

    $html = '<table class="table table-sm">';
    foreach ($getPages as $page) {
        $html .= '<tr>';
        $html .= '<td class="text-nowrap">' . se_format_datetime($page['page_lastedit']) . '</td>';
        $html .= '<td class="w-100"><h6 class="mb-0">' . $page['page_title'] . '</h6><small>' . $page['page_meta_description'] . '</small></td>';
        $html .= '<td>';
        $html .= '<form action="/admin/pages/edit/" method="post">';
        $html .= '<button class="btn btn-default" name="page_id" value="' . $page['page_id'] . '">' . $icon['edit'] . '</button>';
        $html .= '<input type="hidden" name="csrf_token" value="' . $_SESSION['token'] . '">';
        $html .= '</form>';
        $html .= '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    se_html_response($html);
}

// snippets

if ($_REQUEST['action'] === 'list_snippets') {

    $get_snippets = $db_content->select("se_snippets", [
        "snippet_id", "snippet_type", "snippet_name",
        "snippet_title", "snippet_content", "snippet_lastedit"
    ], [
        "OR" => [
            "snippet_type[~]" => ["snippet", "snippet_core"]
        ],
        "ORDER" => ["snippet_lastedit" => "DESC"],
        "LIMIT" => 10
    ]);

    $html = '<table class="table table-sm">';
    foreach ($get_snippets as $snippet) {

        $snippet_content = strip_tags($snippet['snippet_content']);
        if (strlen($snippet_content) > 150) {
            $snippet_content = substr($snippet_content, 0, 100) . ' <small><i>(...)</i></small>';
        }

        $html .= '<tr>';
        $html .= '<td class="text-nowrap">' . se_format_datetime($snippet['snippet_lastedit']) . '</td>';
        $html .= '<td class="w-100">';
        $html .= '<h6><span class="badge text-bg-secondary">' . $snippet['snippet_name'] . '</span> ' . $snippet['snippet_title'] . '</h6>';
        $html .= '<small>' . $snippet_content . '</small>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<form action="/admin/snippets/edit/" method="post">';
        $html .= '<button class="btn btn-default" name="snippet_id" value="' . $snippet['snippet_id'] . '">' . $icon['edit'] . '</button>';
        $html .= '<input type="hidden" name="csrf_token" value="' . $_SESSION['token'] . '">';
        $html .= '</form>';
        $html .= '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    se_html_response($html);
}

// posts

if ($_REQUEST['action'] === 'list_posts') {

    $get_posts = $db_posts->select("se_posts", [
        "post_id", "post_title", "post_teaser", "post_type", "post_lastedit"
    ], [
        "OR" => [
            "post_type[~]" => ["m", "v", "i", "g", "f", "l"]
        ],
        "ORDER" => ["post_lastedit" => "DESC"],
        "LIMIT" => 5
    ]);

    // no entries
    if (count($get_posts) < 1) {
        se_html_response('<div class="alert alert-info">' . $lang['msg_no_entries_found'] . '</div>');
    }

    $html = '<table class="table table-sm">';
    foreach ($get_posts as $post) {
        $trimmed_teaser = se_return_first_chars($post['post_teaser'], 100);
        $html .= '<tr>';
        $html .= '<td class="text-nowrap">' . se_format_datetime($post['post_lastedit']) . '</td>';
        $html .= '<td class="w-100"><h6 class="mb-0">' . $post['post_title'] . '</h6><small>' . $trimmed_teaser . '</small></td>';
        $html .= '<td>';
        $html .= '<form action="/admin/blog/edit/" method="post">';
        $html .= '<button class="btn btn-default" name="post_id" value="' . $post['post_id'] . '">' . $icon['edit'] . '</button>';
        $html .= '<input type="hidden" name="csrf_token" value="' . $_SESSION['token'] . '">';
        $html .= '</form>';
        $html .= '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    se_html_response($html);
}


// products

if ($_REQUEST['action'] === 'list_products') {

    $get_products = $db_posts->select("se_products", [
        "id", "title", "teaser", "type", "lastedit"
    ], [
        "type[~]" => "p",
        "ORDER" => ["lastedit" => "DESC"],
        "LIMIT" => 5
    ]);

    if (count($get_products) < 1) {
        se_html_response('<div class="alert alert-info">' . $lang['msg_no_entries_found'] . '</div>');
    }

    $html = '<table class="table table-sm">';
    foreach ($get_products as $product) {
        $trimmed_teaser = se_return_first_chars($product['teaser'], 100);

        $html .= '<tr>';
        $html .= '<td class="text-nowrap">' . se_format_datetime($product['lastedit']) . '</td>';
        $html .= '<td class="w-100"><h6 class="mb-0">' . $product['title'] . '</h6><small>' . $trimmed_teaser . '</small></td>';
        $html .= '<td>';
        $html .= '<form action="/admin/shop/edit/" method="post">';
        $html .= '<button class="btn btn-default" name="product_id" value="' . $product['id'] . '">' . $icon['edit'] . '</button>';
        $html .= '<input type="hidden" name="csrf_token" value="' . $_SESSION['token'] . '">';
        $html .= '</form>';
        $html .= '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    se_html_response($html);
}


// events

if ($_REQUEST['action'] === 'list_events') {

    $get_events = $db_posts->select("se_events", [
        "id", "title", "teaser", "lastedit"
    ], [
        "id[!]" => null,
        "ORDER" => ["lastedit" => "DESC"],
        "LIMIT" => 5
    ]);

    if (count($get_events) < 1) {
        se_html_response('<div class="alert alert-info">' . $lang['msg_no_entries_found'] . '</div>');
    }

    $html = '<table class="table table-sm">';
    foreach ($get_events as $event) {
        $trimmed_teaser = se_return_first_chars($event['teaser'], 100);

        $html .= '<tr>';
        $html .= '<td class="text-nowrap">' . se_format_datetime($event['lastedit']) . '</td>';
        $html .= '<td class="w-100"><h6 class="mb-0">' . $event['title'] . '</h6><small>' . $trimmed_teaser . '</small></td>';
        $html .= '<td>';
        $html .= '<form action="/admin/events/edit/" method="post">';
        $html .= '<button class="btn btn-default" name="id" value="' . $event['id'] . '">' . $icon['edit'] . '</button>';
        $html .= '<input type="hidden" name="csrf_token" value="' . $_SESSION['token'] . '">';
        $html .= '</form>';
        $html .= '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    se_html_response($html);
}


// comments

if($_REQUEST['action'] == 'list_comments') {
    $get_comments = $db_content->select("se_comments", ["comment_id", "comment_author", "comment_type", "comment_text", "comment_time"], [
        "ORDER" => ["comment_lastedit" => "DESC"],
        "LIMIT" => 5
    ]);

    // @todo
}

// user

if ($_REQUEST['action'] === 'list_user') {
    $get_user = $db_user->select("se_user", [
        "user_id", "user_nick", "user_firstname", "user_lastname", "user_mail", "user_registerdate"
    ], [
        "ORDER" => ["user_id" => "DESC"],
        "LIMIT" => 5
    ]);

    if (count($get_user) < 1) {
        se_html_response('<div class="alert alert-info">' . $lang['msg_no_entries_found'] . '</div>');
    }

    $html = '<table class="table table-sm">';
    foreach ($get_user as $user) {
        $html .= '<tr>';
        $html .= '<td class="text-nowrap">' . se_format_datetime($user['user_registerdate']) . '</td>';
        $html .= '<td class="w-100"><h6 class="mb-0">' . $user['user_nick'] . '</h6><small>' . $user['user_mail'] . '</small></td>';
        $html .= '<td>';
        $html .= '<form action="/admin/users/edit/" method="post">';
        $html .= '<button class="btn btn-default" name="user_id" value="' . $user['user_id'] . '">' . $icon['edit'] . '</button>';
        $html .= '<input type="hidden" name="csrf_token" value="' . $_SESSION['token'] . '">';
        $html .= '</form>';
        $html .= '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    se_html_response($html);
}


/**
 * print smarty cache size
 */

if($_REQUEST['action'] == 'calculate_cache_size') {
    $cache_size = se_dir_size(SE_CONTENT.'/cache/cache/');
    $compile_size = se_dir_size(SE_CONTENT.'/cache/templates_c/');
    $complete_size = readable_filesize($cache_size+$compile_size);
    se_plain_response($complete_size);
}

/**
 * logfile
 */

if($_REQUEST['action'] == 'list_logfile') {
    $show_log = se_show_log(10);
    se_html_response($show_log);
}


/**
 * checks and warnings
 */

if($_REQUEST['action'] === 'list_alerts') {
    $se_check_messages = array();
    $writable_items = array(
        SE_PUBLIC.'/sitemap.xml',
        SE_PUBLIC.'/',
        SE_PUBLIC.'/assets/avatars/',
        SE_ROOT.'/data/cache/',
        SE_ROOT.'/data/cache/cache/',
        SE_ROOT.'/data/cache/templates_c/',
        SE_PUBLIC.'/assets/files/',
        SE_PUBLIC.'/assets/images/',
        SE_ROOT.'/data/database/content.sqlite3',
        SE_ROOT.'/data/database/user.sqlite3',
        SE_ROOT.'/data/database/index.sqlite3'
    );

    foreach($writable_items as $f) {

        if($db_type !== 'sqlite') {
            if($f == SE_ROOT.'/data/database/content.sqlite3') {
                continue;
            }
            if($f == SE_ROOT.'/data/database/user.sqlite3') {
                continue;
            }
        }

        if(!is_writable($f)) {
            $se_check_messages[] = $lang['msg_error_not_writable']. ':<br><code>... '.basename($f).'</code>';
        }
    }

    foreach($se_page_types as $pt) {

        if($pt == 'normal') {
            continue;
        }

        $find_target_page = $db_content->select("se_pages", ["page_permalink","page_type_of_use"], [
            "page_type_of_use" => "$pt"
        ]);

        if(count($find_target_page) < 1) {
            $se_check_messages[] = 'Type of use <code>'.$pt.'</code> is not available ';
        }
    }

    foreach($se_check_messages as $alert) {
        se_html_response('<div class="alert alert-info mb-1">'.$alert.'</div>');
    }
    exit;
}


// show some infos

if ($_REQUEST['action'] === 'list_infos') {
    $html = '<table class="table table-sm">';
    $html .= '<tr><td>SERVER_NAME</td><td>' . $_SERVER['SERVER_NAME'] . '</td></tr>';
    $html .= '<tr><td>PHP Version</td><td>' . phpversion() . '</td></tr>';
    $html .= '<tr><td>Database</td><td>' . $db_type . '</td></tr>';
    $html .= '<tr><td>CMS Domain</td><td>' . $se_settings['cms_domain'] . '</td></tr>';
    $html .= '<tr><td>SSL</td><td>' . $se_settings['cms_ssl_domain'] . '</td></tr>';
    $html .= '<tr><td>Mail</td><td>' . $se_settings['mailer_adr'] . '</td></tr>';
    $html .= '<tr><td>Mail Name</td><td>' . $se_settings['mailer_name'] . '</td></tr>';
    $html .= '</table>';

    se_html_response($html);
}
