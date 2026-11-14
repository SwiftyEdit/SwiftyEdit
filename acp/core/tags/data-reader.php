<?php

/**
 * SwiftyEdit /admin-xhr/tags/read/
 * active search keywords and the list of the tags (acp/templates/tags/list-items.twig)
 *
 * global variables
 * @var array $icon
 * @var object $db_content
 * @var object $db_posts
 * @var object $twig
 */

$writer_uri = '/admin-xhr/tags/write/';

// tag names and content titles are stored html-encoded, Twig escapes on output
$decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

if($_REQUEST['action'] == 'list_active_searches') {
    $keywords = array_filter(explode(" ", $_SESSION['tags_text_filter'] ?? ''));
    echo $twig->render('components/list/active-searches.twig', [
        'keywords' => array_map($decode, array_values($keywords)),
        'writer_uri' => $writer_uri
    ]);
}

if($_REQUEST['action'] == 'list') {

    /*
     * the content types a tag can be attached to:
     * table, id and title column, database and the url of the edit form
     */
    $content_types = [
        'page' => ['db' => $db_content, 'table' => 'se_pages', 'id' => 'page_id', 'title' => 'page_title', 'url' => '/admin/pages/edit/', 'icon' => $icon['files']],
        'post' => ['db' => $db_posts, 'table' => 'se_posts', 'id' => 'post_id', 'title' => 'post_title', 'url' => '/admin/blog/edit/', 'icon' => $icon['file_earmark_post']],
        'product' => ['db' => $db_posts, 'table' => 'se_products', 'id' => 'id', 'title' => 'title', 'url' => '/admin/shop/edit/', 'icon' => $icon['store']],
        'event' => ['db' => $db_posts, 'table' => 'se_events', 'id' => 'id', 'title' => 'title', 'url' => '/admin/events/edit/', 'icon' => $icon['calendar_event']]
    ];

    // all relations, grouped by tag and content type
    $relations = $db_content->select("se_tags_relations", ["tag_id", "content_type", "content_id"]);
    $content_ids = [];
    $relations_by_tag = [];
    foreach($relations as $relation) {
        if(!isset($content_types[$relation['content_type']])) {
            continue;
        }
        $content_ids[$relation['content_type']][] = $relation['content_id'];
        $relations_by_tag[$relation['tag_id']][] = $relation;
    }

    // titles of the tagged content, one query per content type
    $titles = [];
    foreach($content_ids as $type => $ids) {
        $config = $content_types[$type];
        $rows = $config['db']->select($config['table'], [$config['id'], $config['title']], [
            $config['id'] => array_values(array_unique($ids))
        ]);
        foreach($rows as $row) {
            $titles[$type][$row[$config['id']]] = $decode($row[$config['title']]);
        }
    }

    // search: every keyword has to be part of the name
    $keywords = array_filter(explode(" ", $decode($_SESSION['tags_text_filter'] ?? '')));
    $unused_only = (($_SESSION['tags_unused_only'] ?? '') == 'true');

    $tags = [];
    foreach(se_get_tags() as $tag) {

        $name = $decode($tag['tag_name']);

        $matches = true;
        foreach($keywords as $keyword) {
            if(mb_stripos($name, $keyword) === false) {
                $matches = false;
            }
        }
        if(!$matches) {
            continue;
        }

        $items = [];
        $types = [];
        foreach($relations_by_tag[$tag['tag_id']] ?? [] as $relation) {
            $type = $relation['content_type'];
            $types[$type] = ($types[$type] ?? 0) + 1;
            $items[] = [
                'icon' => $content_types[$type]['icon'],
                // content without a title (or already deleted) is shown with its id
                'title' => ($titles[$type][$relation['content_id']] ?? '') ?: '#'.$relation['content_id'],
                'url' => $content_types[$type]['url'].$relation['content_id'].'/'
            ];
        }

        if($unused_only && count($items) > 0) {
            continue;
        }

        // usage per content type, in the order of the sidebar
        $type_counts = [];
        foreach($content_types as $type => $config) {
            if(isset($types[$type])) {
                $type_counts[] = ['icon' => $config['icon'], 'count' => $types[$type]];
            }
        }

        usort($items, static fn($a, $b) => strnatcasecmp($a['title'], $b['title']));

        $tags[] = [
            'id' => (int) $tag['tag_id'],
            'name' => $name,
            'slug' => $tag['tag_name_clean'],
            'usage' => count($items),
            'types' => $type_counts,
            'items' => $items
        ];
    }

    // by name, or the most used tags first
    if(($_SESSION['tags_sort'] ?? 'name') == 'usage') {
        usort($tags, static fn($a, $b) => ($b['usage'] <=> $a['usage']) ?: strnatcasecmp($a['name'], $b['name']));
    } else {
        usort($tags, static fn($a, $b) => strnatcasecmp($a['name'], $b['name']));
    }

    echo $twig->render('tags/list-items.twig', [
        'tags' => $tags,
        'writer_uri' => $writer_uri
    ]);
    exit;
}
