<?php

/**
 * media library in /admin/uploads/, markup in acp/templates/uploads/list.twig
 * the folder select, the list, the active search keywords and the stats are loaded by the uploads data-reader
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

$sorting = $_SESSION['sorting_media_list'] ?? 'media_lastedit';
$sorting_options = [];
foreach([
    'media_lastedit' => $lang['date_of_change'],
    'media_file' => $lang['filename'],
    'media_filesize' => $lang['filesize']
] as $value => $label) {
    $sorting_options[] = ['value' => $value, 'label' => $label, 'selected' => ($value == $sorting)];
}

echo $twig->render('uploads/list.twig', [
    'title' => $lang['uploads'],
    'title_icon' => $icon['images'],
    'reader_uri' => '/admin-xhr/uploads/read/',
    'writer_uri' => '/admin-xhr/uploads/write/',
    'search_name' => 'uploads_text_filter',
    'list_triggers' => 'update_uploads_list from:body, updated_global_filter from:body',
    'sorting_options' => $sorting_options
]);
