<?php

// dashboard data reader

/**
 * pages
 */


if($_REQUEST['action'] == 'list_pages') {
    $getPages = $db_content->select("se_pages", ["page_id", "page_linkname", "page_title", "page_meta_description", "page_lastedit", "page_lastedit_from", "page_status"], [
        "ORDER" => ["page_lastedit" => "DESC"],
        "LIMIT" => 5
    ]);

    echo '<table class="table table-sm">';
    foreach($getPages as $page) {
        echo '<tr>';
        echo '<td class="text-nowrap">'.se_format_datetime($page['page_lastedit']).'</td>';
        echo '<td class="w-100"><h6 class="mb-0">'.$page['page_title'].'</h6><small>'.$page['page_meta_description'].'</small></td>';
        echo '<td>';
        echo '<form action="/admin/pages/edit/" method="post">';
        echo '<button class="btn btn-default" name="page_id" value="'.$page['page_id'].'">'.$icon['edit'].'</button>';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    exit;
}

/**
 * snippets
 */

if($_REQUEST['action'] == 'list_snippets') {

    $get_snippets = $db_content->select("se_snippets", ["snippet_id", "snippet_type", "snippet_name", "snippet_title", "snippet_lastedit"], [
        "OR" => [
            "snippet_type[~]" => ["snippet","snippet_core"]
        ],
        "ORDER" => ["snippet_lastedit" => "DESC"],
        "LIMIT" => 5
    ]);

    echo '<table class="table table-sm">';
    foreach($get_snippets as $snippet) {
        echo '<tr>';
        echo '<td class="text-nowrap">'.se_format_datetime($snippet['snippet_lastedit']).'</td>';
        echo '<td class="w-100"><kbd>'.$snippet['snippet_title'].'</kbd> <small>'.$snippet['snippet_title'].'</small></td>';
        echo '<td>';
        echo '<form action="/admin/snippets/edit/" method="post">';
        echo '<button class="btn btn-default" name="page_id" value="'.$snippet['snippet_id'].'">'.$icon['edit'].'</button>';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    exit;
}

/**
 * posts
 */

if($_REQUEST['action'] == 'list_posts') {
    echo 'POSTS';
    $get_posts = $db_posts->select("se_posts", ["post_id", "post_title", "post_teaser", "post_type", "post_lastedit"], [
        "OR" => [
            "post_type[~]" => ["m","v","i","g","f","l"]
        ],
        "ORDER" => ["post_lastedit" => "DESC"],
        "LIMIT" => 5
    ]);
    echo '<pre>';
    print_r($get_posts);
    echo '</pre>';
}

/**
 * products
 */

if($_REQUEST['action'] == 'list_products') {
    echo 'PRODUCTS';
    $get_products = $db_posts->select("se_products", ["id", "title", "teaser", "type", "lastedit"], [
        "type[~]" => "p",
        "ORDER" => ["lastedit" => "DESC"],
        "LIMIT" => 5
    ]);
    print_r($get_products);
}

/**
 * events
 */

if($_REQUEST['action'] == 'list_events') {
    echo 'EVENTS';
    $get_events = $db_posts->select("se_events", ["id", "title", "teaser", "lastedit"], [
        "id[!]" => NULL,
        "ORDER" => ["lastedit" => "DESC"],
        "LIMIT" => 5
    ]);
    print_r($get_events);
}

/**
 * comments
 */

if($_REQUEST['action'] == 'list_comments') {
    echo 'COMMENTS';
    $get_comments = $db_content->select("se_comments", ["comment_id", "comment_author", "comment_type", "comment_text", "comment_time"], [
        "ORDER" => ["comment_lastedit" => "DESC"],
        "LIMIT" => 5
    ]);
    print_r($get_comments);
}

/**
 * user
 */

if($_REQUEST['action'] == 'list_user') {
    echo 'USERES';
    $get_user = $db_user->select("se_user", ["user_id", "user_nick", "user_firstname", "user_lastname", "user_mail", "user_registerdate"], [
        "ORDER" => ["user_id" => "DESC"],
        "LIMIT" => 5
    ]);
    print_r($get_user);
}

/**
 * print smarty cache size
 */
if($_REQUEST['action'] == 'calculate_cache_size') {
    $cache_size = se_dir_size(SE_CONTENT.'/cache/cache/');
    $compile_size = se_dir_size(SE_CONTENT.'/cache/templates_c/');
    $complete_size = readable_filesize($cache_size+$compile_size);
    echo $complete_size;
}

/**
 * logfile
 */

if($_REQUEST['action'] == 'list_logfile') {
    $show_log = se_show_log(10);
    echo $show_log;
}


/**
 * checks and warnings
 */

if($_REQUEST['action'] == 'list_alerts') {
    $se_check_messages = array();
    $writable_items = array(
        '../sitemap.xml',
        SE_CONTENT.'/',
        SE_CONTENT.'/avatars/',
        SE_CONTENT.'/cache/',
        SE_CONTENT.'/cache/cache/',
        SE_CONTENT.'/cache/templates_c/',
        SE_CONTENT.'/files/',
        SE_CONTENT.'/images/',
        SE_CONTENT.'/SQLite/',
        SE_CONTENT.'/SQLite/content.sqlite3',
        SE_CONTENT.'/SQLite/user.sqlite3',
        SE_CONTENT.'/SQLite/index.sqlite3'
    );

    foreach($writable_items as $f) {

        if($db_type !== 'sqlite') {
            if($f == SE_CONTENT.'/SQLite/content.sqlite3') {
                continue;
            }
            if($f == SE_CONTENT.'/SQLite/user.sqlite3') {
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
        echo '<div class="alert alert-info mb-1">'.$alert.'</div>';
    }

}

/**
 * show some infos
 */

if($_REQUEST['action'] == 'list_infos') {
    echo '<table class="table table-sm">';
    echo '<tr><td>SERVER_NAME</td><td>'.$_SERVER['SERVER_NAME'].'</td></tr>';
    echo '<tr><td>PHP Version</td><td>'.phpversion().'</td></tr>';
    echo '<tr><td>Database</td><td>'.$db_type.'</td></tr>';
    echo '<tr><td>CMS Domain</td><td>'.$se_settings['cms_domain'].'</td></tr>';
    echo '<tr><td>SSL</td><td>'.$se_settings['cms_ssl_domain'].'</td></tr>';
    echo '<tr><td>Mail</td><td>'.$se_settings['mailer_adr'].'</td></tr>';
    echo '<tr><td>Mail Name</td><td>'.$se_settings['mailer_name'].'</td></tr>';
    echo '<tr>';
    echo '</table>';
}