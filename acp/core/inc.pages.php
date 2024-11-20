<?php

/**
 * SwiftyEdit backend
 * switch file for section pages
 */


$subinc = match (true) {
    str_starts_with($query, 'pages/new/') => 'pages/pages-edit',
    str_starts_with($query, 'pages/edit/') => 'pages/pages-edit',
    str_starts_with($query, 'pages/duplicate/') => 'pages/pages-edit',
    str_starts_with($query, 'pages') => 'pages/pages-list',
    str_starts_with($query, 'snippets/new/') => 'snippets/snippets-edit',
    str_starts_with($query, 'snippets/edit/') => 'snippets/snippets-edit',
    str_starts_with($query, 'snippets/duplicate/') => 'snippets/snippets-edit',
    str_starts_with($query, 'snippets') => 'snippets/snippets-list',
    str_starts_with($query, 'shortcodes/edit/') => 'shortcodes/shortcodes-edit',
    str_starts_with($query, 'shortcodes') => 'shortcodes/shortcodes-list',
    default => ''
};

if($subinc != '') {
    include $subinc.'.php';
}
