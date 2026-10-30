<?php

/**
 * SwiftyEdit /admin-xhr/events/read/
 * active search keywords and the list of the events (acp/templates/events/list-items.twig)
 *
 * global variables
 * @var object $db_posts
 * @var object $twig
 */

$writer_uri = '/admin-xhr/events/write/';

// text fields are stored html-encoded (events data-writer), Twig escapes on output
$decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

if($_REQUEST['action'] == 'list_active_searches') {
    $keywords = array_filter(explode(" ", $_SESSION['events_text_filter'] ?? ''));
    echo $twig->render('components/list/active-searches.twig', [
        'keywords' => array_map($decode, array_values($keywords)),
        'writer_uri' => $writer_uri
    ]);
}

// list the events
if($_REQUEST['action'] == 'list_events') {

    // defaults
    $order_by = 'event_startdate';
    $order_direction = 'ASC';
    $limit_start = $_SESSION['pagination_events_page'] ?? 0;
    $nbr_show_items = 10;

    $match_str = $_SESSION['events_text_filter'] ?? '';
    $order_key = $_SESSION['sorting_events'] ?? $order_by;
    $order_direction = $_SESSION['sorting_events_direction'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    // show or hide past events
    if(($_SESSION['show_past_events'] ?? '') == 'true') {
        $filter_base = [
            "AND" => [
                "id[>]" => 0
            ]
        ];
    } else {
        $filter_base = [
            "AND" => [
                "event_startdate[>]" => time()
            ]
        ];
    }

    $filter_by_str = array();
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "title[~]" => "%$f%",
                    "teaser[~]" => "%$f%",
                    "text[~]" => "%$f%"
                ]
            ];
        }
    }

    $db_where = [
        "AND" => $filter_base+$filter_by_str
    ];

    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];

    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

    $events_data_cnt = $db_posts->count("se_events", $db_where);

    $events_data = $db_posts->select("se_events","*",
        $db_where+$db_order+$db_limit
    );

    $nbr_pages = ceil($events_data_cnt/$nbr_show_items);

    // categories by hash, for the category names in the rows
    $categories_by_hash = [];
    foreach(se_get_categories() as $cat) {
        $categories_by_hash[$cat['cat_hash']] = [
            'name' => $decode($cat['cat_name']),
            'description' => $decode($cat['cat_description'] ?? '')
        ];
    }

    $events = [];
    foreach($events_data as $post) {

        $teaser = html_entity_decode(se_return_first_chars($post['teaser'], 100, false), ENT_QUOTES, 'UTF-8');
        $teaser_plain = strip_tags(htmlspecialchars_decode((string) $post['teaser']));

        // the first image is the thumbnail
        $post_image = explode("<->", (string) $post['images']);
        $thumb = '';
        if(($post_image[1] ?? '') != '') {
            $thumb = str_replace(['../content/', '../images/'], ['/', '/images/'], $post_image[1]);
        }

        $categories = [];
        foreach(array_filter(explode('<->', (string) $post['categories'])) as $hash) {
            if(isset($categories_by_hash[$hash])) {
                $categories[] = $categories_by_hash[$hash];
            }
        }

        $labels = array_filter(
            se_get_label_checklist(explode(',', (string) $post['labels'])),
            static fn($label) => $label['checked']
        );

        $events[] = [
            'id' => (int) $post['id'],
            'title' => $decode($post['title']),
            'teaser' => $teaser,
            'teaser_trimmed' => (strlen($teaser_plain) > 100),
            'lang' => $post['event_lang'],
            'lang_flag' => return_language_flag_src($post['event_lang']),
            'is_draft' => ($post['status'] == '2'),
            'fixed' => ($post['fixed'] == '1'),
            'priority' => $post['priority'],
            'start' => se_format_datetime($post['event_startdate']),
            // the end date only when it differs from the start
            'end' => ($post['event_startdate'] != $post['event_enddate']) ? se_format_datetime($post['event_enddate']) : '',
            'date' => se_format_datetime($post['date']),
            'lastedit' => ($post['lastedit'] != '') ? se_format_datetime($post['lastedit']) : '',
            'releasedate' => se_format_datetime($post['releasedate']),
            'thumb' => $thumb,
            'categories' => $categories,
            'labels' => $labels
        ];
    }

    echo $twig->render('events/list-items.twig', [
        'pagination' => se_print_pagination($writer_uri, $nbr_pages, $_SESSION['pagination_events_page'] ?? 0),
        'events' => $events,
        'writer_uri' => $writer_uri
    ]);
}
