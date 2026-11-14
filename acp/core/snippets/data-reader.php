<?php

/**
 * SwiftyEdit /admin-xhr/snippets/read/
 * active search keywords, the list of the snippets (acp/templates/snippets/list-items.twig)
 * and the keyword filter (acp/templates/snippets/keywords.twig)
 *
 * global variables
 * @var object $db_content
 * @var object $twig
 */

$writer_uri = '/admin-xhr/snippets/write/';

// most text fields are stored html-encoded (snippets data-writer), Twig escapes on output
$decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

// show list of keywords (search)
if($_REQUEST['action'] == 'list_active_searches') {
    $keywords = array_filter(explode(" ", $_SESSION['snippets_text_filter'] ?? ''));
    echo $twig->render('components/list/active-searches.twig', [
        'keywords' => array_map($decode, array_values($keywords)),
        'writer_uri' => $writer_uri
    ]);
}

// list the snippets
if($_REQUEST['action'] == 'list_snippets') {

    // defaults
    $order_by = 'snippet_lastedit';
    $order_direction = 'DESC';
    $limit_start = $_SESSION['pagination_snippets_page'] ?? 0;
    $nbr_show_items = (int) ($_SESSION['snippets_per_page'] ?? 10);

    $match_str = $_SESSION['snippets_text_filter'] ?? '';
    $keyword_str = $_SESSION['snippets_keyword_filter'] ?? '';
    $order_key = $_SESSION['sorting_snippets'] ?? $order_by;
    $order_direction = $_SESSION['sorting_snippet_direction'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    $filter_base = [
      "AND" => [
          "snippet_type" => ['snippet','snippet_core']
      ]
    ];

    $filter_by_str = array();
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "snippet_title[~]" => "%$f%",
                    "snippet_name[~]" => "%$f%",
                    "snippet_content[~]" => "%$f%",
                    "snippet_keywords[~]" => "%$f%"
                ]
            ];
        }
    }

    $filter_by_keyword = array();
    if($keyword_str != '') {
        $this_filter = explode(" ",$keyword_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_keyword = [
                    "snippet_keywords[~]" => "%$f%"
            ];
        }
    }

    $db_where = [
        "AND" => $filter_base+$filter_by_str+$filter_by_keyword
    ];

    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];

    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

    $snippet_data_cnt = $db_content->count("se_snippets", $db_where);

    $snippets_data = $db_content->select("se_snippets","*",
        $db_where+$db_order+$db_limit
    );

    $nbr_pages = ceil($snippet_data_cnt/$nbr_show_items);

    $snippets = [];
    foreach($snippets_data as $snippet) {

        // the content is stored as entered (html), the preview is plain text
        $content = html_entity_decode(strip_tags((string) $snippet['snippet_content']), ENT_QUOTES, 'UTF-8');
        $content_trimmed = (mb_strlen($content) > 150);
        if($content_trimmed) {
            $content = mb_substr($content, 0, 100);
        }

        $labels = array_filter(
            se_get_label_checklist(explode(',', (string) $snippet['snippet_labels'])),
            static fn($label) => $label['checked']
        );

        $images = [];
        foreach(array_filter(explode('<->', (string) $snippet['snippet_images'])) as $image) {
            $images[] = str_replace('../content/', '/', $image);
        }

        $snippets[] = [
            'id' => (int) $snippet['snippet_id'],
            'name' => $snippet['snippet_name'],
            'title' => $decode($snippet['snippet_title']),
            'content' => $content,
            'content_trimmed' => $content_trimmed,
            'lang' => $snippet['snippet_lang'],
            'lang_flag' => return_language_flag_src($snippet['snippet_lang']),
            'lastedit' => se_format_datetime($snippet['snippet_lastedit']),
            'classes' => array_values(array_filter(explode(' ', $decode($snippet['snippet_classes'])))),
            'labels' => $labels,
            'images' => $images,
            'link' => [
                'url' => $decode($snippet['snippet_permalink']),
                'title' => $decode($snippet['snippet_permalink_title']),
                'name' => $decode($snippet['snippet_permalink_name']),
                'classes' => $decode($snippet['snippet_permalink_classes'])
            ]
        ];
    }

    echo $twig->render('snippets/list-items.twig', [
        'pagination' => se_print_pagination($writer_uri, $nbr_pages, $_SESSION['pagination_snippets_page'] ?? 0),
        'items_per_page' => $nbr_show_items,
        'snippets' => $snippets,
        'writer_uri' => $writer_uri
    ]);
}

// keyword filter, active keywords are highlighted
if($_REQUEST['action'] == 'list_keywords') {
    $keyword_filter = $_SESSION['snippets_keyword_filter'] ?? '';
    $keywords = [];
    foreach(se_get_snippet_keywords() as $keyword => $count) {
        $keyword = trim($keyword);
        $keywords[] = [
            'name' => $decode($keyword),
            'count' => $count,
            'active' => ($keyword_filter != '' && str_contains($keyword_filter, $keyword))
        ];
    }
    echo $twig->render('snippets/keywords.twig', [
        'keywords' => $keywords,
        'writer_uri' => $writer_uri
    ]);
}
