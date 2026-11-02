<?php

/**
 * SwiftyEdit /admin-xhr/categories/read/
 * active search keywords and the list of the categories (acp/templates/categories/list-items.twig)
 *
 * global variables
 * @var object $twig
 */

$writer_uri = '/admin-xhr/categories/write/';

// names, descriptions and search keywords are stored html-encoded, Twig escapes on output
$decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

if($_REQUEST['action'] == 'list_active_searches') {
    $keywords = array_filter(explode(" ", $_SESSION['categories_text_filter'] ?? ''));
    echo $twig->render('components/list/active-searches.twig', [
        'keywords' => array_map($decode, array_values($keywords)),
        'writer_uri' => $writer_uri
    ]);
}

if($_REQUEST['action'] == 'list') {

    // search: every keyword has to be part of the name, title, teaser, description or slug
    $keywords = array_filter(explode(" ", $decode($_SESSION['categories_text_filter'] ?? '')));

    $categories = [];
    foreach(se_get_categories() as $category) {

        $name = $decode($category['cat_name']);
        $title = $decode($category['cat_title']);
        // the teaser is stored as html (se_purify_html), the list shows it as plain text
        $teaser = trim(preg_replace('/\s+/', ' ', $decode(strip_tags((string) $category['cat_teaser']))));

        $haystack = implode(' ', [$name, $title, $teaser, $decode($category['cat_description']), $category['cat_name_clean']]);
        foreach($keywords as $keyword) {
            if(mb_stripos($haystack, $keyword) === false) {
                continue 2;
            }
        }

        // the first image as thumbnail, "null" is an empty value of older versions
        $images = array_filter(explode('<->', (string) $category['cat_thumbnail']), static fn($image) => $image != '' && $image != 'null');
        $image = (string) reset($images);
        $image = str_replace('../content/', '/', $image); // old path from SwiftyEdit 1.x
        $image = str_replace('../images/', '/images/', $image);

        $categories[] = [
            'id' => (int) $category['cat_id'],
            'name' => $name,
            // the fields of the "info" tab - the description belongs to the meta information
            'title' => ($title != $name) ? $title : '',
            'teaser' => mb_strimwidth($teaser, 0, 120, '…'),
            'slug' => $category['cat_name_clean'],
            'sort' => $category['cat_sort'],
            'lang' => $category['cat_lang'],
            'lang_flag' => return_language_flag_src($category['cat_lang']),
            'image' => $image
        ];
    }

    echo $twig->render('categories/list-items.twig', [
        'categories' => $categories,
        'writer_uri' => $writer_uri
    ]);
    exit;
}
