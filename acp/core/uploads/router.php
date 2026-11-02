<?php

/**
 * SwiftyEdit backend
 * switch file for section uploads
 *
 * global variables
 * @var string $query - the current url
 */

require __DIR__.'/functions.php';

$subinc = match (true) {
    str_starts_with($query, 'uploads/edit/') => 'edit',
    default => 'index'
};

include __DIR__.'/'.$subinc.'.php';
