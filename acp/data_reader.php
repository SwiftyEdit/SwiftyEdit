<?php

/**
* routing - read data
*/


require_once 'header.php';

$reader = match (true) {
    str_starts_with($_REQUEST['query'], 'pages/') => 'core/pages/data-reader.php',
    str_starts_with($_REQUEST['query'], 'snippets/') => 'core/snippets/data-reader.php',
    str_starts_with($_REQUEST['query'], 'blog/') => 'core/blog/data-reader.php',
    str_starts_with($_REQUEST['query'], 'shop/') => 'core/shop/data-reader.php',
    str_starts_with($_REQUEST['query'], 'events/') => 'core/events/data-reader.php',
    str_starts_with($_REQUEST['query'], 'inbox/') => 'core/inbox/data-reader.php',
    str_starts_with($_REQUEST['query'], 'users/') => 'core/users/data-reader.php',
    str_starts_with($_REQUEST['query'], 'settings/') => 'core/settings/data-reader.php',
    str_starts_with($_REQUEST['query'], 'categories/') => 'core/categories/data-reader.php',
    str_starts_with($_REQUEST['query'], 'tags/') => 'core/tags/data-reader.php',
    str_starts_with($_REQUEST['query'], 'dashboard/') => 'core/dashboard/data-reader.php',
    str_starts_with($_REQUEST['query'], 'uploads/') => 'core/uploads/data-reader.php',
    str_starts_with($_REQUEST['query'], 'update/') => 'core/update/data-reader.php',
    str_starts_with($_REQUEST['query'], 'counter/') => 'core/widgets/counters.php',
    str_starts_with($_REQUEST['query'], 'widgets/') => 'core/widgets/widgets.php',
    str_starts_with($_REQUEST['query'], 'docs/') => 'core/docs/data-reader.php',
    str_starts_with($_REQUEST['query'], 'addons/') => 'core/addons/data-reader.php',
    str_starts_with($_REQUEST['query'], 'search/') => 'core/search/data-reader.php',
    default => ''
};


if($reader != '') {
    include_once $reader;
    exit;
}