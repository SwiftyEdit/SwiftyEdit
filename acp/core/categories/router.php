<?php

/**
 * SwiftyEdit backend
 * switch file for section categories
 *
 * @var string $query - the current url
 */

$subinc = match (true) {
    str_starts_with($query, 'categories/new/') => 'categories-edit',
    str_starts_with($query, 'categories/edit/') => 'categories-edit',
    str_starts_with($query, 'categories') => 'categories-list',
    default => 'snippets-list'
};

include $subinc.'.php';
