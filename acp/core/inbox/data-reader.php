<?php

/**
 * SwiftyEdit /admin-xhr/inbox/read/
 * comments: active search keywords, the list (acp/templates/inbox/comments-items.twig)
 * and the edit form in the modal
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $db_content
 * @var object $db_posts
 * @var object $twig
 */

$list_writer_uri = '/admin-xhr/inbox/write/';

// text fields are stored html-encoded (sanitizeUserInputs), Twig escapes on output
$decode = static fn($value): string => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

$action = $_REQUEST['action'] ?? '';

// comments of pages (p), posts (b) and products (c) - votes and guest list
// confirmations are stored in the same table, but they are no comments
$comment_types = ['p', 'b', 'c'];

// list active keywords from search input
if($action == 'list_active_searches_comments') {
    $keywords = array_filter(explode(" ", $_SESSION['comments_text_filter'] ?? ''));
    echo $twig->render('components/list/active-searches.twig', [
        'keywords' => array_map($decode, array_values($keywords)),
        'remove_name' => 'rmkey_comments',
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

// list the comments
if($action == 'list_comments') {

    $nbr_show_items = 25;
    $active_page = (int) ($_SESSION['pagination_comments'] ?? 0);

    $db_where = [
        "comment_type" => $comment_types
    ];

    // search: every keyword has to be part of the author, the mail or the text
    $search = array_values(array_filter(explode(" ", $_SESSION['comments_text_filter'] ?? '')));
    foreach($search as $i => $keyword) {
        $db_where["OR #search$i"] = [
            "comment_author[~]" => "%$keyword%",
            "comment_author_mail[~]" => "%$keyword%",
            "comment_text[~]" => "%$keyword%"
        ];
    }

    $comments_data_cnt = $db_content->count("se_comments", ["AND" => $db_where]);
    $comments_data = $db_content->select("se_comments", "*", [
        "AND" => $db_where,
        "ORDER" => ["comment_time" => "DESC"],
        "LIMIT" => [$active_page * $nbr_show_items, $nbr_show_items]
    ]);

    // titles of the pages, posts and products, one query per type
    $relation_ids = ['p' => [], 'b' => [], 'c' => []];
    foreach($comments_data as $comment) {
        $relation_ids[$comment['comment_type']][] = (int) $comment['comment_relation_id'];
    }
    $titles = [
        'p' => $relation_ids['p'] ? $db_content->select("se_pages", ["page_id", "page_title"], ["page_id" => $relation_ids['p']]) : [],
        'b' => $relation_ids['b'] ? $db_posts->select("se_posts", ["post_id", "post_title"], ["post_id" => $relation_ids['b']]) : [],
        'c' => $relation_ids['c'] ? $db_posts->select("se_products", ["id", "title"], ["id" => $relation_ids['c']]) : []
    ];
    $titles = [
        'p' => array_column($titles['p'], 'page_title', 'page_id'),
        'b' => array_column($titles['b'], 'post_title', 'post_id'),
        'c' => array_column($titles['c'], 'title', 'id')
    ];
    $relation_labels = ['p' => $lang['nav_btn_pages'], 'b' => $lang['nav_btn_blog'], 'c' => $lang['nav_btn_shop']];

    $comments = [];
    foreach($comments_data as $comment) {
        $type = $comment['comment_type'];
        $comments[] = [
            'id' => (int) $comment['comment_id'],
            'time' => se_format_datetime($comment['comment_time']),
            'author' => $decode($comment['comment_author']),
            'mail' => $decode($comment['comment_author_mail']),
            'text' => $decode($comment['comment_text']),
            // 1 = waiting for an administrator, 2 = shown
            'approved' => ((int) $comment['comment_status'] !== 1),
            'parent_id' => (int) $comment['comment_parent_id'],
            'relation' => [
                'type' => $relation_labels[$type],
                'title' => $decode($titles[$type][(int) $comment['comment_relation_id']] ?? '')
            ]
        ];
    }

    echo $twig->render('inbox/comments-items.twig', [
        'comments' => $comments,
        'pagination' => se_print_pagination($list_writer_uri, (int) ceil($comments_data_cnt / $nbr_show_items), $active_page, 10, '', 'pagination_comments'),
        'reader_uri' => '/admin-xhr/inbox/read/',
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

// edit form of a comment in the modal
if(isset($_REQUEST['open_comment'])) {

    $get_comment = $db_content->get("se_comments", "*", [
        "comment_id" => (int) $_REQUEST['open_comment'],
        "comment_type" => $comment_types
    ]);
    if(!is_array($get_comment)) {
        exit;
    }

    echo $twig->render('inbox/comment-modal.twig', [
        'comment' => [
            'id' => (int) $get_comment['comment_id'],
            'author' => $decode($get_comment['comment_author']),
            'mail' => $decode($get_comment['comment_author_mail']),
            'text' => $decode($get_comment['comment_text'])
        ],
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}
