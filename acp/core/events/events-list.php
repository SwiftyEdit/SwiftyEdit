<?php

/**
 * overview of the events, markup in acp/templates/events/list.twig
 * the list itself is loaded by the data-reader (action list_events)
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

echo $twig->render('events/list.twig', [
    'title' => $lang['nav_btn_events'],
    'title_icon' => $icon['calendar_event'],
    'new_url' => '/admin/events/new/',
    'reader_uri' => '/admin-xhr/events/read/',
    'writer_uri' => '/admin-xhr/events/write/',
    'search_name' => 'events_text_filter',
    'list_triggers' => 'update_events_list from:body, updated_global_filter from:body',
    'show_past_events' => (($_SESSION['show_past_events'] ?? '') == 'true')
]);
