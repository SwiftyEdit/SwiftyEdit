<?php

/**
 * overview of the pages, markup in acp/templates/pages/list.twig
 * the list, the keywords and the page types in the sidebar are loaded by the data-reader
 *
 * global variables
 * @var array $lang
 * @var array $icon
 * @var object $twig
 */

// sorted = the page tree, single = pages outside the tree
$set_type = $_GET['set_type'] ?? '';
if($set_type == 'sorted' || $set_type == 'single') {
    $_SESSION['show_page_type'] = $set_type;
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
}
if(($_SESSION['show_page_type'] ?? '') != 'single') {
    $_SESSION['show_page_type'] = 'sorted';
}
$show_page_type = $_SESSION['show_page_type'];

if($show_page_type == 'sorted') {
    $list_title = $lang['nav_btn_structured_pages'];
    $docs_link = se_print_docs_link('02-00-pages.md', null, 'ordered-pages');
} else {
    $list_title = $lang['nav_btn_single_pages'];
    $docs_link = se_print_docs_link('02-00-pages.md', null, 'single-pages');
}

// sorting of the single pages, the session holds the column (see data-writer)
$sorting_columns = [
    'lastedit' => 'page_lastedit',
    'linkname' => 'page_linkname',
    'priority' => 'page_priority'
];
$sorting = array_search($_SESSION['sorting_single_pages'] ?? '', $sorting_columns) ?: 'lastedit';
$sorting_options = [];
foreach([
    'lastedit' => $lang['label_data_last_edit'],
    'linkname' => $lang['label_link_name'],
    'priority' => $lang['label_priority']
] as $value => $label) {
    $sorting_options[] = ['value' => $value, 'label' => $label, 'selected' => ($value == $sorting)];
}

echo $twig->render('pages/list.twig', [
    'title' => $lang['nav_btn_pages'].' / '.$list_title,
    'title_icon' => $icon['file'],
    'docs_link' => $docs_link,
    'new_url' => se_hasPermission('drm_acp_editpages') ? '/admin/pages/new/' : null,
    'reader_uri' => '/admin-xhr/pages/read/',
    'writer_uri' => '/admin-xhr/pages/write/',
    'search_name' => 'pages_text_filter',
    'list_triggers' => 'update_pages_list from:body, updated_global_filter from:body',
    'list_type' => ($show_page_type == 'sorted') ? 1 : 2,
    'show_page_type' => $show_page_type,
    'sorting_options' => $sorting_options
]);
