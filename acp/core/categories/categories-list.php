<?php

/**
 * overview of the categories, markup in acp/templates/categories/list.twig
 * the list is loaded by the data-reader (action list)
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

echo $twig->render('categories/list.twig', [
    'title' => $lang['categories'],
    'title_icon' => $icon['bookmarks_fill'],
    'docs_link' => se_print_docs_link('01-02-basics.md#categories'),
    'new_url' => '/admin/categories/new/',
    'reader_uri' => '/admin-xhr/categories/read/',
    'writer_uri' => '/admin-xhr/categories/write/',
    'search_name' => 'categories_text_filter',
    'list_triggers' => 'updated_categories from:body'
]);
