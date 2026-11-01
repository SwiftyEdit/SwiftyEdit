<?php

/**
 * comments in /admin/inbox/comments/, markup in acp/templates/inbox/comments.twig
 * the list, the active search keywords and the edit modal are loaded by the inbox data-reader
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

echo $twig->render('inbox/comments.twig', [
    'title' => $lang['nav_btn_comments'],
    'title_icon' => $icon['comments'],
    'reader_uri' => '/admin-xhr/inbox/read/',
    'writer_uri' => '/admin-xhr/inbox/write/',
    'search_name' => 'comments_text_filter',
    'searches_action' => 'list_active_searches_comments',
    'list_triggers' => 'update_comments_list from:body'
]);
