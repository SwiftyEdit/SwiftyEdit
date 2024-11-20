<?php

$subinc = match (true) {
    str_starts_with($query, 'settings/labels/') => 'settings.labels',
    str_starts_with($query, 'settings/posts/') => 'settings.posts',
    str_starts_with($query, 'settings/events/') => 'settings.events',
    str_starts_with($query, 'settings/shop/') => 'settings.shop',
    str_starts_with($query, 'pages') => 'pages.list',
    str_starts_with($query, 'snippets') => 'pages.snippets',
    str_starts_with($query, 'shortcodes') => 'pages.shortcodes',
    str_starts_with($query, 'index') => 'pages.index',
    str_starts_with($query, 'rss') => 'pages.rss',
    default => 'settings.general'
};


if($_SESSION['acp_system'] != "allowed"){
	$subinc = "no_access";
}

include 'settings/'.$subinc.'.php';