<?php

/**
 * orders in /admin/shop/orders/, markup in acp/templates/shop/orders.twig
 * the list, the totals and the details (modal) are loaded by the data-reader
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 */

echo $twig->render('shop/orders.twig', [
    'title' => $lang['nav_btn_shop'].' / '.$lang['nav_btn_orders'],
    'title_icon' => $icon['shop'],
    'reader_uri' => '/admin-xhr/shop/read/',
    'writer_uri' => '/admin-xhr/shop/write/',
    'search_name' => 'orders_text_filter',
    'searches_action' => 'list_active_searches_orders',
    'list_triggers' => 'update_orders_list from:body'
]);
