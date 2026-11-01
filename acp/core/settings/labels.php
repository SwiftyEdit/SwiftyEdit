<?php

/**
 * labels in /admin/settings/labels/, markup in acp/templates/settings/labels.twig
 * the list is loaded by the settings data-reader, labels are saved by the data-writer
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

require_once __DIR__ . '/functions.php';

echo $twig->render('settings/labels.twig', [
    'title' => $lang['nav_btn_settings'].' / '.$lang['labels'],
    'title_icon' => $icon['gear'],
    'settings_nav' => se_settings_nav('labels'),
    'reader_uri' => '/admin-xhr/settings/labels/read/',
    'writer_uri' => '/admin-xhr/settings/labels/write/',
    // titles and help texts of the sections come from the docs
    'help' => se_docs_sections('08-00-settings.md', ['labels-list', 'labels-new'])
]);
