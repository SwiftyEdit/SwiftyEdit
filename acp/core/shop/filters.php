<?php

/**
 * overview of the product filters, markup in acp/templates/shop/filters.twig
 * the list and the categories in the sidebar are loaded by the data-reader
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

echo $twig->render('shop/filters.twig', [
    'title' => $lang['nav_btn_shop'],
    'title_icon' => $icon['shop'],
    'reader_uri' => '/admin-xhr/shop/read/',
    'writer_uri' => '/admin-xhr/shop/write/',
    'list_triggers' => 'update_filter_list from:body, updated_global_filter from:body'
]);
