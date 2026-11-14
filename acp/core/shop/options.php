<?php

/**
 * overview of the product options, markup in acp/templates/shop/options.twig
 * the list is loaded by the data-reader (action list_options)
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

echo $twig->render('shop/options.twig', [
    'title' => $lang['nav_btn_shop'],
    'title_icon' => $icon['shop'],
    'new_url' => '/admin/shop/options/new/',
    'reader_uri' => '/admin-xhr/shop/read/',
    'writer_uri' => '/admin-xhr/shop/write/',
    'search_name' => 'options_text_filter',
    'searches_action' => 'list_active_searches_options',
    'list_triggers' => 'update_options_list from:body, updated_global_filter from:body'
]);
