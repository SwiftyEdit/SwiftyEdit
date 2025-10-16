<?php

use Twig\Environment;

/**
 * dashboard data reader
 *
 * @var object $db_content
 * @var object $db_user
 * @var object $db_posts
 * @var string $db_type
 * @var array $se_page_types
 * @var array $se_settings
 * @var array $lang
 * @var array $icon
 * @var Environment $twig
 */


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

    foreach ($getPages as &$page) {
        $page['page_se_format_datetime'] = se_format_datetime($page['page_lastedit']);
    }

    $html = $twig->render('dashboard/table-pages.twig', [
        'getPages' => $getPages
    ]);

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

    foreach ($get_snippets as &$snippet) {
        // date
        $snippet['snippet_se_format_datetime'] = se_format_datetime($snippet['snippet_lastedit']);

        // Content Preview
        $snippet_content = strip_tags($snippet['snippet_content']);
        if (strlen($snippet_content) > 150) {
            $snippet['snippet_content_preview'] = substr($snippet_content, 0, 100) . ' <small><i>(...)</i></small>';
        } else {
            $snippet['snippet_content_preview'] = $snippet_content;
        }
    }

    $html = $twig->render('dashboard/table-snippets.twig', [
        'get_snippets' => $get_snippets
    ]);

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

    foreach ($get_posts as &$post) {
        $post['post_lastedit_formatted'] = se_format_datetime($post['post_lastedit']);
        $post['post_teaser_trimmed'] = se_return_first_chars($post['post_teaser'], 100);
    }

    $html = $twig->render('dashboard/table-posts.twig', [
        'get_posts' => $get_posts
    ]);

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

    foreach ($get_products as &$product) {
        $product['lastedit_formatted'] = se_format_datetime($product['lastedit']);
        $product['teaser_trimmed'] = se_return_first_chars($product['teaser'], 100);
    }

    $html = $twig->render('dashboard/table-products.twig', [
        'get_products' => $get_products
    ]);

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

    foreach ($get_events as &$event) {
        $event['lastedit_formatted'] = se_format_datetime($event['lastedit']);
        $event['teaser_trimmed'] = se_return_first_chars($event['teaser'], 100);
    }

    $html = $twig->render('dashboard/table-events.twig', [
        'get_events' => $get_events
    ]);

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

    foreach ($get_user as &$user) {
        // Datum formatieren
        $user['user_registerdate_formatted'] = se_format_datetime($user['user_registerdate']);
    }

    $html = $twig->render('dashboard/table-users.twig', [
        'get_user' => $get_user
    ]);

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
    $se_check_messages = [];

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
        SE_ROOT.'/data/database/user.sqlite3'
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

        echo $twig->render('components/alert.twig', [
            'message' => $alert,
            'type' => 'info',
            'icon' => 'info-circle',
            'allow_html' => true,
            'dismissible' => false
        ]);
    }
    exit;
}


// show some infos

if ($_REQUEST['action'] === 'list_infos') {
    $html = $twig->render('dashboard/table-infos.twig', [
        'database' => $db_type,
        'php_version' => phpversion()
    ]);

    se_html_response($html);
}
