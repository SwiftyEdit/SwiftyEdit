<?php

/**
 * SwiftyEdit backend
 * switch file for section tags
 *
 * @var string $query - the current url
 */

$subinc = match (true) {
    str_starts_with($query, 'tags') => 'tags-list',
    default => 'tags-list'
};

include $subinc.'.php';
