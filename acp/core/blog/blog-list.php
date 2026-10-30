<?php

/**
 * overview of the posts, markup in acp/templates/blog/list.twig
 * the list and the filters in the sidebar are loaded by the data-reader
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

echo $twig->render('blog/list.twig', [
    'title' => $lang['nav_btn_blog'],
    'title_icon' => $icon['files'],
    'new_url' => '/admin/blog/new/',
    'reader_uri' => '/admin-xhr/blog/read/',
    'writer_uri' => '/admin-xhr/blog/write/',
    'search_name' => 'blog_text_filter',
    'list_triggers' => 'update_posts_list from:body, updated_global_filter from:body'
]);
