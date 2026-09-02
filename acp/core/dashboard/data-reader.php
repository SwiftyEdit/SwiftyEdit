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

// This endpoint only reads from $_SESSION (header.php's own writes are
// already done by this point) and never writes to it. The dashboard fires
// ~13-15 of these hx-trigger="load" requests at once, all sharing one
// PHPSESSID - the default file session handler holds an exclusive lock on
// the session file for the whole request, so without closing it early here
// those requests queue up and run one at a time instead of concurrently.
// Do not add $_SESSION writes below without removing this first.
session_write_close();


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
        // page_title / page_meta_description are stored HTML-entity-encoded (se_return_clean_value()).
        // Decode here so Twig's autoescape encodes them exactly once instead of double-encoding
        // (which turns umlauts like "&Uuml;" into visible "&amp;Uuml;" in the browser).
        $page['page_title'] = html_entity_decode($page['page_title'], ENT_QUOTES, 'UTF-8');
        $page['page_meta_description'] = html_entity_decode($page['page_meta_description'], ENT_QUOTES, 'UTF-8');
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
        se_html_response('<div class="dash-empty-hint">' . $lang['msg_no_entries_found'] . '</div>');
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
        se_html_response('<div class="dash-empty-hint">' . $lang['msg_no_entries_found'] . '</div>');
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
        se_html_response('<div class="dash-empty-hint">' . $lang['msg_no_entries_found'] . '</div>');
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


// activated plugins

if ($_REQUEST['action'] === 'list_addons') {

    $activated = se_get_addons('plugin');
    $all_addons = se_get_all_addons();

    $addons = [];
    foreach ($activated as $a) {
        $dir = $a['addon_dir'];
        // First entry in the addon's own nav (its "overview" tab, by
        // convention always named 'start') is the closest thing to a
        // detail page for it - fall back to the addons list if a plugin
        // happens to ship no navigation at all.
        $nav_file = $all_addons[$dir]['navigation'][0]['file'] ?? null;
        $addons[] = [
            'dir' => $dir,
            'name' => $all_addons[$dir]['addon']['name'] ?? $dir,
            'version' => $all_addons[$dir]['addon']['version'] ?? '',
            'link' => $nav_file ? '/admin/addons/plugin/'.$dir.'/'.$nav_file.'/' : '/admin/addons/'
        ];
    }

    if (count($addons) < 1) {
        se_html_response('<div class="dash-empty-hint">' . $lang['msg_no_entries_found'] . '</div>');
    }

    $html = $twig->render('dashboard/table-addons.twig', [
        'addons' => $addons
    ]);

    se_html_response($html);
}


// orders

if ($_REQUEST['action'] === 'list_orders') {

    $get_orders = $db_content->select("se_orders", [
        "id", "order_nbr", "order_time", "order_invoice_mail", "order_price_total", "order_status"
    ], [
        "ORDER" => ["order_time" => "DESC"],
        "LIMIT" => 5
    ]);

    if (count($get_orders) < 1) {
        se_html_response('<div class="dash-empty-hint">' . $lang['msg_no_entries_found'] . '</div>');
    }

    $show_order_status = [
        "1" => $lang['status_order_received'],
        "2" => $lang['status_order_completed'],
        "3" => $lang['status_order_canceled']
    ];

    // Plain number_format() instead of se_post_print_currency() here - that
    // helper always wraps the price in <span class="price-predecimal/decimal">
    // for frontend styling, which would need |raw in the template to render
    // (it's also used by the frontend cart/checkout, so not worth changing
    // its contract just for this one plain-text row).
    foreach ($get_orders as &$order) {
        $order['order_time_formatted'] = se_format_datetime($order['order_time']);
        $order['order_price_formatted'] = number_format($order['order_price_total'], 2, ',', '.') . ' ' . $se_settings['posts_products_default_currency'];
        $order['order_status_label'] = $show_order_status[$order['order_status']] ?? '';
    }

    $html = $twig->render('dashboard/table-orders.twig', [
        'get_orders' => $get_orders
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
        se_html_response('<div class="dash-empty-hint">' . $lang['msg_no_entries_found'] . '</div>');
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
 * central cache management ("Cache" tab)
 * lists every cache the application writes, with its current size and
 * either a single "rebuild" action or (products, smarty) their existing
 * dedicated actions
 */

if ($_REQUEST['action'] === 'list_cache') {

    $caches = [
        [
            'target' => 'smarty',
            'label' => $lang['cache_label_smarty'],
            'size' => readable_filesize(se_dir_size(SE_CONTENT.'/cache/cache/') + se_dir_size(SE_CONTENT.'/cache/templates_c/')),
            'clear_only' => true
        ],
        [
            'target' => 'navigation',
            'label' => $lang['cache_label_navigation'],
            'size' => readable_filesize(se_dir_size(SE_CONTENT.'/cache/navigation/'))
        ],
        [
            'target' => 'urls',
            'label' => $lang['cache_label_urls'],
            'size' => readable_filesize(is_file(SE_CONTENT.'/cache/active_urls.json') ? filesize(SE_CONTENT.'/cache/active_urls.json') : 0)
        ],
        [
            'target' => 'categories',
            'label' => $lang['label_categories'],
            'size' => readable_filesize(se_dir_size(SE_CONTENT.'/cache/categories/'))
        ],
        [
            'target' => 'tags',
            'label' => $lang['label_keywords'],
            'size' => readable_filesize(se_dir_size(SE_CONTENT.'/cache/tags/'))
        ],
        [
            'target' => 'snippets',
            'label' => $lang['cache_label_snippets'],
            'size' => readable_filesize(se_dir_size(SE_CONTENT.'/cache/snippets/'))
        ],
        [
            'target' => 'preferences',
            'label' => $lang['cache_label_preferences'],
            'size' => readable_filesize(se_dir_size(SE_CONTENT.'/cache/preferences/'))
        ],
        [
            'target' => 'products',
            'label' => $lang['label_products'],
            'size' => readable_filesize(se_dir_size(SE_CONTENT.'/cache/products/')),
            'clear_and_rebuild' => true
        ]
    ];

    $html = $twig->render('dashboard/table-cache.twig', [
        'caches' => $caches
    ]);

    se_html_response($html);
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

    if (count($se_check_messages) < 1) {
        se_html_response('<div class="dash-empty-hint">' . $lang['msg_no_entries_found'] . '</div>');
    }

    // Same row shape as the dashboard's other list-based cards instead of a
    // full-weight .alert per message - a bank of alert boxes crammed into a
    // compact card looked heavier than the content warranted; a plain list
    // reads just as clearly here.
    echo '<div class="list-group list-group-flush">';
    foreach($se_check_messages as $alert) {
        echo '<div class="list-group-item">'.$alert.'</div>';
    }
    echo '</div>';
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
