<?php

/**
 * reactions (votes) in /admin/inbox/reactions/, markup in acp/templates/inbox/reactions.twig
 * the list and the filter are loaded by the inbox data-reader, deleted by the data-writer
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

echo $twig->render('inbox/reactions.twig', [
    'title' => $lang['nav_btn_reactions'],
    'title_icon' => $icon['star_outline'],
    'reader_uri' => '/admin-xhr/inbox/read/',
    'writer_uri' => '/admin-xhr/inbox/write/',
    'list_triggers' => 'update_reactions_list from:body'
]);
