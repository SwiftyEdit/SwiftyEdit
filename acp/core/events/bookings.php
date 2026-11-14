<?php

/**
 * guestlist confirmations of the events, markup in acp/templates/events/bookings.twig
 * the list itself is loaded by the data-reader (action list_bookings)
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

echo $twig->render('events/bookings.twig', [
    'title' => $lang['nav_btn_bookings'],
    'title_icon' => $icon['calendar_event'],
    'reader_uri' => '/admin-xhr/events/read/',
    'writer_uri' => '/admin-xhr/events/write/',
    // update_events_list: the past events toggle
    'list_triggers' => 'update_bookings_list from:body, update_events_list from:body',
    'show_past_events' => (($_SESSION['show_past_events'] ?? '') == 'true')
]);
