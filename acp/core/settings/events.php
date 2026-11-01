<?php

/**
 * event settings in /admin/settings/events/, markup in acp/templates/settings/events.twig
 * the sections are saved by the settings data-writer (save_settings=<section>)
 *
 * global variables
 * @var array $se_settings
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

require_once __DIR__ . '/functions.php';

// the settings are stored html-encoded, the form helpers print them as they are
$setting = static fn(string $key): string => (string) ($se_settings[$key] ?? '');

$inputs = [];
$inputs['entries_per_page'] = se_print_form_input([
    "input_name" => "prefs_events_entries_per_page",
    "input_value" => $setting('events_entries_per_page'),
    "label" => $lang['label_entries_per_page'],
    "type" => "number"
]);

// stored in seconds, entered in hours (see the "hours" type in se_settings_sections())
$inputs['time_offset'] = se_print_form_input([
    "input_name" => "prefs_posts_event_time_offset",
    "input_value" => (string) (int) round((int) $setting('posts_event_time_offset') / 3600),
    "label" => $lang['label_events_time_offset'],
    "form_text" => $lang['label_events_time_offset_hours_text'],
    "input_group_end_text" => "h",
    "type" => "number"
]);

$inputs['guestlist'] = se_print_form_input([
    "input_name" => "prefs_posts_default_guestlist",
    "input_value" => $setting('posts_default_guestlist'),
    "label" => $lang['label_guestlist'],
    "options" => [
        $lang['label_guestlist_status_off'] => '1',
        $lang['label_guestlist_status_registered'] => '2',
        $lang['label_guestlist_status_global'] => '3'
    ],
    "type" => "select"
]);

echo $twig->render('settings/events.twig', [
    'title' => $lang['nav_btn_settings'].' / '.$lang['nav_btn_events'],
    'title_icon' => $icon['gear'],
    'settings_nav' => se_settings_nav('events'),
    'writer_uri' => '/admin-xhr/settings/general/write/',
    // titles and help texts of the sections come from the docs
    'help' => se_docs_sections('08-00-settings.md', ['events-list', 'events-guestlist']),
    'inputs' => $inputs
]);
