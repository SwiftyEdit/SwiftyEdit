<?php

/**
 * SwiftyEdit /admin-xhr/blog/read/
 * active search keywords, the filters of the sidebar (post types, categories),
 * the list of the posts (acp/templates/blog/list-items.twig) and the gallery thumbnails
 *
 * global variables
 * @var array $lang
 * @var object $db_posts
 * @var object $twig
 */

$writer_uri = '/admin-xhr/blog/write/';

// text fields are stored html-encoded (blog data-writer), Twig escapes on output
$decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

$post_types = [
    'm' => $lang['post_type_message'],
    'i' => $lang['post_type_image'],
    'g' => $lang['post_type_gallery'],
    'v' => $lang['post_type_video'],
    'l' => $lang['post_type_link'],
    'f' => $lang['post_type_file']
];

$action = $_REQUEST['action'] ?? '';

if($action == 'list_active_searches') {
    $keywords = array_filter(explode(" ", $_SESSION['posts_text_filter'] ?? ''));
    echo $twig->render('components/list/active-searches.twig', [
        'keywords' => array_map($decode, array_values($keywords)),
        'writer_uri' => $writer_uri
    ]);
}

// filter by category in the sidebar
if($action == 'list_categories') {
    $active_categories = explode(" ", $_SESSION['filter_posts_categories'] ?? '');
    $items = [];
    foreach(se_get_categories() as $category) {
        $items[] = [
            'value' => $category['cat_hash'],
            'label' => $decode($category['cat_name']),
            'active' => in_array($category['cat_hash'], $active_categories),
            'flag' => return_language_flag_src($category['cat_lang'])
        ];
    }
    echo $twig->render('components/list/filter-buttons.twig', [
        'name' => 'set_filter_cat',
        'items' => $items,
        'writer_uri' => $writer_uri
    ]);
}

// filter by post type in the sidebar
if($action == 'list_post_types') {
    $active_types = explode(",", $_SESSION['filter_posts_types'] ?? '');
    $items = [];
    foreach($post_types as $type => $label) {
        $items[] = [
            'value' => $type,
            'label' => $label,
            'active' => in_array($type, $active_types)
        ];
    }
    echo $twig->render('components/list/filter-buttons.twig', [
        'name' => 'set_filter_post_types',
        'items' => $items,
        'writer_uri' => $writer_uri
    ]);
}

if($action == 'list_posts') {

    $nbr_show_items = 10;
    $active_page = (int) ($_SESSION['pagination_posts_page'] ?? 0);

    // post types: the selected ones or all
    $types = array_intersect(explode(",", $_SESSION['filter_posts_types'] ?? ''), array_keys($post_types));
    $db_where = [
        "post_type" => $types ? array_values($types) : array_keys($post_types)
    ];

    // search: every keyword has to be part of the title, teaser or text
    $keywords = array_values(array_filter(explode(" ", $_SESSION['posts_text_filter'] ?? '')));
    foreach($keywords as $i => $keyword) {
        $db_where["OR #keyword$i"] = [
            "post_title[~]" => "%$keyword%",
            "post_teaser[~]" => "%$keyword%",
            "post_text[~]" => "%$keyword%"
        ];
    }

    // categories: posts in one of the selected categories
    $cat_filter = array_values(array_filter(explode(" ", $_SESSION['filter_posts_categories'] ?? '')));
    if($cat_filter) {
        $db_where["post_categories[~]"] = $cat_filter;
    }

    // global filter (top navigation), posts know public (1) and draft (2)
    $db_where += se_get_global_filter_where('post_lang', 'post_status', 'post_labels', [1 => '1', 2 => '2']);

    $posts_data_cnt = $db_posts->count("se_posts", ["AND" => $db_where]);
    $nbr_pages = (int) ceil($posts_data_cnt/$nbr_show_items);

    $posts_data = $db_posts->select("se_posts", "*", [
        "AND" => $db_where,
        "ORDER" => ["post_id" => "DESC"],
        "LIMIT" => [$active_page * $nbr_show_items, $nbr_show_items]
    ]);

    // categories by hash, for the category names in the rows
    $categories_by_hash = [];
    foreach(se_get_categories() as $cat) {
        $categories_by_hash[$cat['cat_hash']] = [
            'name' => $decode($cat['cat_name']),
            'description' => $decode($cat['cat_description'] ?? '')
        ];
    }

    $posts = [];
    foreach($posts_data as $post) {

        $teaser = $decode(se_return_first_chars($post['post_teaser'], 100, false));
        $teaser_plain = strip_tags(htmlspecialchars_decode((string) $post['post_teaser']));

        // images are stored as <->img1<->img2<->, the first one is the thumbnail
        $post_image = explode("<->", (string) $post['post_images']);
        $thumb = '';
        if(($post_image[1] ?? '') != '') {
            $thumb = str_replace(['../content/', '../images/'], ['/', '/images/'], $post_image[1]);
        }

        $categories = [];
        foreach(array_filter(explode('<->', (string) $post['post_categories'])) as $hash) {
            if(isset($categories_by_hash[$hash])) {
                $categories[] = $categories_by_hash[$hash];
            }
        }

        $labels = array_filter(
            se_get_label_checklist(explode(',', (string) $post['post_labels'])),
            static fn($label) => $label['checked']
        );

        $posts[] = [
            'id' => (int) $post['post_id'],
            'type' => $post['post_type'],
            'type_label' => $post_types[$post['post_type']] ?? $post['post_type'],
            'title' => $decode($post['post_title']),
            'teaser' => $teaser,
            'teaser_trimmed' => (strlen($teaser_plain) > 100),
            'lang' => $post['post_lang'],
            'lang_flag' => return_language_flag_src($post['post_lang']),
            'is_draft' => ($post['post_status'] == '2'),
            'fixed' => ($post['post_fixed'] == '1'),
            'priority' => $post['post_priority'],
            'date' => se_format_datetime($post['post_date']),
            'lastedit' => ($post['post_lastedit'] != '') ? se_format_datetime($post['post_lastedit']) : '',
            'releasedate' => se_format_datetime($post['post_releasedate']),
            'thumb' => $thumb,
            // counters of file downloads and link redirects, null for the other types
            'downloads' => ($post['post_type'] == 'f') ? (int) $post['post_file_attachment_hits'] : null,
            'redirects' => ($post['post_type'] == 'l') ? (int) $post['post_link_hits'] : null,
            'categories' => $categories,
            'labels' => $labels
        ];
    }

    echo $twig->render('blog/list-items.twig', [
        'posts' => $posts,
        'pagination' => se_print_pagination($writer_uri, $nbr_pages, $active_page),
        'writer_uri' => $writer_uri
    ]);
}

if(isset($_GET['gallery_thumbs'])) {
    $gallery_id = (int) $_GET['gallery_thumbs'];
    echo se_list_gallery_thumbs($gallery_id);
    exit;
}
