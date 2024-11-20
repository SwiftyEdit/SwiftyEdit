<?php


if(isset($_GET['query'])) {
    $query = se_clean_query($_GET['query']);
}

if(!isset($query)) {
    $query = '/admin/';
}

$path = explode("/", $query);

$maininc = match (true) {
    str_starts_with($query, 'pages') => 'inc.pages',
    str_starts_with($query, 'snippets') => 'inc.pages',
    str_starts_with($query, 'shortcodes') => 'inc.pages',
    str_starts_with($query, 'addons') => 'inc.addons',
    str_starts_with($query, 'users') => 'inc.users',
    str_starts_with($query, 'categories') => 'inc.categories',
    str_starts_with($query, 'settings') => 'inc.settings',
    str_starts_with($query, 'shop') => 'inc.shop',
    str_starts_with($query, 'events') => 'inc.events',
    str_starts_with($query, 'blog') => 'inc.blog',
    str_starts_with($query, 'inbox') => 'inc.inbox',
    str_starts_with($query, 'uploads') => 'inc.uploads',
    default => 'inc.dashboard'
};

if($maininc == '') {
    $maininc = "inc.dashboard";
}