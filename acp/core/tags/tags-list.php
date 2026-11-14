<?php

/**
 * overview of the tags, markup in acp/templates/tags/list.twig
 * the list is loaded by the data-reader (action list)
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

echo $twig->render('tags/list.twig', [
    'title' => $lang['tags'],
    'title_icon' => $icon['tags'],
    'reader_uri' => '/admin-xhr/tags/read/',
    'writer_uri' => '/admin-xhr/tags/write/',
    'search_name' => 'tags_text_filter',
    'list_triggers' => 'updated_tags from:body',
    'sort' => $_SESSION['tags_sort'] ?? 'name',
    'unused_only' => (($_SESSION['tags_unused_only'] ?? '') == 'true')
]);
