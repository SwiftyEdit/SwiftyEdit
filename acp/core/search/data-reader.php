<?php

/**
 * Global live-search for the ACP header (see acp/core/nav_top.php).
 *
 * Read-only HTMX endpoint: given a query string it returns an HTML fragment
 * with the matches grouped by content type, or an empty string when there
 * is nothing to show. There is deliberately no dedicated search results
 * page - this is only ever rendered into the header dropdown.
 *
 * Every content type is gated by the same se_hasPermission() check its own
 * module uses (see the router.php of each acp/core module), so this never
 * surfaces anything a restricted admin account couldn't already reach on
 * its own:
 * - pages/snippets: unrestricted, like their own list views
 * - products/blog/events: drm_can_publish
 * - users: drm_acp_user
 * Pages additionally mirror pages-list.php's per-page behaviour: every
 * match is shown, but the edit link is only included when the current user
 * has drm_acp_editpages or is listed in that page's page_authorized_users.
 *
 * @var object $db_content
 * @var object $db_posts
 * @var object $db_user
 * @var array $lang
 * @var array $icon
 * @var Twig\Environment $twig
 */

$q = sanitizeUserInputs(trim((string) ($_POST['q'] ?? '')));

$groups = [];
$limit = 5;

if (strlen($q) >= 2) {

    $like = "%$q%";

    // pages
    $pages = $db_content->select("se_pages", ["page_id", "page_title", "page_linkname", "page_authorized_users"], [
        "OR" => [
            "page_title[~]" => $like,
            "page_linkname[~]" => $like,
        ],
        "LIMIT" => $limit
    ]);

    if (!empty($pages)) {
        $items = [];
        foreach ($pages as $page) {
            $authorized_users = array_filter(explode(",", $page['page_authorized_users']));
            $can_edit = se_hasPermission('drm_acp_editpages') || in_array($_SESSION['user_nick'], $authorized_users, true);
            $items[] = [
                'label' => $page['page_title'] !== '' ? $page['page_title'] : $page['page_linkname'],
                // page_title is already sanitized at save time, like every other ACP list prints it unescaped
                'raw_label' => true,
                'href' => $can_edit ? '/admin/pages/edit/'.$page['page_id'].'/' : null,
            ];
        }
        $groups[] = ['label' => $lang['nav_btn_pages'], 'icon' => $icon['files'], 'items' => $items];
    }

    // snippets - list itself is unrestricted, same as snippets-list.php
    $snippets = $db_content->select("se_snippets", ["snippet_id", "snippet_title", "snippet_name"], [
        "OR" => [
            "snippet_title[~]" => $like,
            "snippet_name[~]" => $like,
        ],
        "LIMIT" => $limit
    ]);

    if (!empty($snippets)) {
        $items = [];
        foreach ($snippets as $snippet) {
            $items[] = [
                'label' => $snippet['snippet_title'] !== '' ? $snippet['snippet_title'] : $snippet['snippet_name'],
                'raw_label' => true,
                // snippets-edit.php only reads the id from $_POST, there is no GET route
                'form_action' => '/admin/snippets/edit/',
                'form_field' => 'snippet_id',
                'form_value' => $snippet['snippet_id'],
            ];
        }
        $groups[] = ['label' => $lang['nav_btn_snippets'], 'icon' => $icon['card_heading'], 'items' => $items];
    }

    // products
    if (se_hasPermission('drm_can_publish')) {
        $products = $db_posts->select("se_products", ["id", "title", "product_number"], [
            "OR" => [
                "title[~]" => $like,
                "product_number[~]" => $like,
            ],
            "LIMIT" => $limit
        ]);

        if (!empty($products)) {
            $items = [];
            foreach ($products as $product) {
                $items[] = [
                    'label' => $product['title'] !== '' ? $product['title'] : $product['product_number'],
                    'raw_label' => true,
                    'href' => '/admin/shop/edit/'.$product['id'].'/',
                ];
            }
            $groups[] = ['label' => $lang['nav_btn_products'], 'icon' => $icon['store'], 'items' => $items];
        }
    }

    // blog posts
    if (se_hasPermission('drm_can_publish')) {
        $posts = $db_posts->select("se_posts", ["post_id", "post_title"], [
            "post_title[~]" => $like,
            "LIMIT" => $limit
        ]);

        if (!empty($posts)) {
            $items = [];
            foreach ($posts as $post) {
                $items[] = [
                    'label' => $post['post_title'],
                    'raw_label' => true,
                    'href' => '/admin/blog/edit/'.$post['post_id'].'/',
                ];
            }
            $groups[] = ['label' => $lang['nav_btn_blog'], 'icon' => $icon['file_earmark_post'], 'items' => $items];
        }
    }

    // events - events-edit.php only reads the id from $_POST, there is no GET route
    if (se_hasPermission('drm_can_publish')) {
        $events = $db_posts->select("se_events", ["id", "title"], [
            "title[~]" => $like,
            "LIMIT" => $limit
        ]);

        if (!empty($events)) {
            $items = [];
            foreach ($events as $event) {
                $items[] = [
                    'label' => $event['title'],
                    'raw_label' => true,
                    'form_action' => '/admin/events/edit/',
                    'form_field' => 'id',
                    'form_value' => $event['id'],
                ];
            }
            $groups[] = ['label' => $lang['nav_btn_events'], 'icon' => $icon['calendar_event'], 'items' => $items];
        }
    }

    // users - users-edit.php only reads the id from $_POST, there is no GET route
    if (se_hasPermission('drm_acp_user')) {
        $users = $db_user->select("se_user", ["user_id", "user_nick", "user_mail", "user_firstname", "user_lastname"], [
            "user_class[!]" => "deleted",
            "OR" => [
                "user_nick[~]" => $like,
                "user_mail[~]" => $like,
                "user_firstname[~]" => $like,
                "user_lastname[~]" => $like,
            ],
            "LIMIT" => $limit
        ]);

        if (!empty($users)) {
            $items = [];
            foreach ($users as $user) {
                $display_name = trim($user['user_firstname'].' '.$user['user_lastname']);
                $items[] = [
                    'label' => $display_name !== '' ? $display_name.' ('.$user['user_nick'].')' : $user['user_nick'],
                    'form_action' => '/admin/users/edit/',
                    'form_field' => 'user_id',
                    'form_value' => $user['user_id'],
                ];
            }
            $groups[] = ['label' => $lang['nav_btn_user'], 'icon' => $icon['users'], 'items' => $items];
        }
    }
}

echo $twig->render('widgets/live-search-results.twig', [
    'groups' => $groups,
    'lang' => $lang,
    'csrf_token' => $_SESSION['token'],
]);
