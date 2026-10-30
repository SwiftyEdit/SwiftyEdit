<?php

/**
 * overview of the products, markup in acp/templates/shop/list.twig
 * the list, the filters and the checks in the sidebar are loaded by the data-reader
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

$sorting = $_SESSION['sorting_products'] ?? 'lastedit';
$sorting_options = [];
foreach([
    'priority' => $lang['label_priority'],
    'date' => $lang['label_data_submited'],
    'lastedit' => $lang['label_data_last_edit'],
    'product_price_net' => $lang['label_price']
] as $value => $label) {
    $sorting_options[] = ['value' => $value, 'label' => $label, 'selected' => ($value == $sorting)];
}

echo $twig->render('shop/list.twig', [
    'title' => $lang['nav_btn_shop'],
    'title_icon' => $icon['shop'],
    'new_url' => '/admin/shop/new/',
    'reader_uri' => '/admin-xhr/shop/read/',
    'writer_uri' => '/admin-xhr/shop/write/',
    'search_name' => 'products_text_filter',
    'list_triggers' => 'update_products_list from:body, updated_global_filter from:body',
    'sorting_options' => $sorting_options
]);
