<?php

/**
* routing - read data
*/


require 'header.php';

$reader = match (true) {
    str_contains($_REQUEST['query'], 'pages/') => 'core/pages/data-reader.php',
    str_contains($_REQUEST['query'], 'snippets/') => 'core/snippets/data-reader.php',
    str_contains($_REQUEST['query'], 'blog/') => 'core/blog/data-reader.php',
    str_contains($_REQUEST['query'], 'shop/') => 'core/shop/data-reader.php',
    str_contains($_REQUEST['query'], 'events/') => 'core/events/data-reader.php',
    str_contains($_REQUEST['query'], 'inbox/') => 'core/inbox/data-reader.php',
    str_contains($_REQUEST['query'], 'user/') => 'core/user/data-reader.php',
    str_contains($_REQUEST['query'], 'settings/labels') => 'core/settings/data-reader.php',
    str_contains($_REQUEST['query'], 'categories/') => 'core/categories/data-reader.php',
    str_contains($_REQUEST['query'], 'dashboard/') => 'core/dashboard/data-reader.php',
    str_contains($_REQUEST['query'], 'uploads/') => 'core/uploads/data-reader.php',
    str_contains($_REQUEST['query'], 'counter/') => 'core/xhr/counters.php',
    str_contains($_REQUEST['query'], 'support/') => 'core/support/data-reader.php',
    default => ''
};


if($reader != '') {
    include $reader;
    exit;
}