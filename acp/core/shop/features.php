<?php

/**
 * overview of the product features, markup in acp/templates/shop/features.twig
 * the list is loaded by the data-reader (action list_features)
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

echo $twig->render('shop/features.twig', [
    'title' => $lang['nav_btn_shop'],
    'title_icon' => $icon['shop'],
    'new_url' => '/admin/shop/features/new/',
    'reader_uri' => '/admin-xhr/shop/read/',
    'writer_uri' => '/admin-xhr/shop/write/',
    'search_name' => 'features_text_filter',
    'searches_action' => 'list_active_searches_features',
    'list_triggers' => 'update_feature_list from:body, updated_global_filter from:body'
]);
