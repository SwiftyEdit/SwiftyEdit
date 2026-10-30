<?php

/**
 * overview of the snippets, markup in acp/templates/snippets/list.twig
 * the list and the keyword filter are loaded by the data-reader
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

echo $twig->render('snippets/list.twig', [
    'title' => $lang['nav_btn_snippets'],
    'title_icon' => $icon['card_heading'],
    'docs_link' => se_print_docs_link('03-00-snippets.md'),
    'new_url' => '/admin/snippets/new/',
    'reader_uri' => '/admin-xhr/snippets/read/',
    'writer_uri' => '/admin-xhr/snippets/write/',
    'search_name' => 'snippets_text_filter',
    'list_triggers' => 'update_snippet_list from:body, updated_global_filter from:body'
]);
