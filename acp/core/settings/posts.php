<?php

/**
 * blog settings in /admin/settings/posts/, markup in acp/templates/settings/posts.twig
 * the sections are saved by the settings data-writer (save_settings=<section>)
 *
 * global variables
 * @var array $se_settings
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

require_once __DIR__ . '/functions.php';

$inputs = [];
$inputs['entries_per_page'] = se_print_form_input([
    "input_name" => "prefs_posts_entries_per_page",
    "input_value" => (string) ($se_settings['posts_entries_per_page'] ?? ''),
    "label" => $lang['label_entries_per_page'],
    "type" => "number"
]);

echo $twig->render('settings/posts.twig', [
    'title' => $lang['nav_btn_settings'].' / '.$lang['nav_btn_posts'],
    'title_icon' => $icon['gear'],
    'settings_nav' => se_settings_nav('posts'),
    'writer_uri' => '/admin-xhr/settings/general/write/',
    // titles and help texts of the sections come from the docs
    'help' => se_docs_sections('08-00-settings.md', ['posts-entries']),
    'inputs' => $inputs
]);
