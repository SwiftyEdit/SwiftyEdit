<?php

$subinc = match (true) {
    str_starts_with($query, 'update/helpers/') => 'helpers',
    default => 'index'
};

include __DIR__.'/'.$subinc.'.php';