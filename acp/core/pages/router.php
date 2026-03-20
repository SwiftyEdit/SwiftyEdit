<?php

/**
 * SwiftyEdit backend
 * switch file for section pages
 *
 * @var string $query - the current url
 */


$subinc = match (true) {
    str_starts_with($query, 'pages/new/') => 'pages-edit',
    str_starts_with($query, 'pages/edit/') => 'pages-edit',
    str_starts_with($query, 'pages/duplicate/') => 'pages-edit',
    str_starts_with($query, 'pages') => 'pages-list',
    default => ''
};

if($subinc != '') {
    include $subinc.'.php';
}