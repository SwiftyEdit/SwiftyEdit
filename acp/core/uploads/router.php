<?php

require __DIR__.'/functions.php';

$subinc = match (true) {
    str_starts_with($query, 'uploads/edit/') => 'edit',
    default => 'index'
};

if($subinc != '') {
    include __DIR__.'/'.$subinc.'.php';
}