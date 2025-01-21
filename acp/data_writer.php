<?php

/**
 * routing - write data
 */


require 'header.php';


$writer = match (true) {
    str_starts_with($_REQUEST['query'], 'addons/') => 'core/addons/data-writer.php',
    str_starts_with($_REQUEST['query'], 'settings/') => 'core/settings/data-writer.php',
    str_starts_with($_REQUEST['query'], 'categories/') => 'core/categories/data-writer.php',
    str_starts_with($_REQUEST['query'], 'pages/') => 'core/pages/data-writer.php',
    str_starts_with($_REQUEST['query'], 'snippets/') => 'core/snippets/data-writer.php',
    str_starts_with($_REQUEST['query'], 'uploads/') => 'core/uploads/data-writer.php',
    str_starts_with($_REQUEST['query'], 'update/') => 'core/update/data-writer.php',
    str_starts_with($_REQUEST['query'], 'shop/') => 'core/shop/data-writer.php',
    str_starts_with($_REQUEST['query'], 'blog/') => 'core/blog/data-writer.php',
    str_starts_with($_REQUEST['query'], 'events/') => 'core/events/data-writer.php',
    str_starts_with($_REQUEST['query'], 'users/') => 'core/users/data-writer.php',
    str_starts_with($_REQUEST['query'], 'xhr/') => 'core/xhr/data-writer.php',
    default => ''
};

if($writer != '') {
    include $writer;
    exit;
}

