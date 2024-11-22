<?php

$subinc = match (true) {
    str_starts_with($query, 'addons/plugins/') => 'list-plugins',
    str_starts_with($query, 'addons/themes/') => 'list-themes',
    str_starts_with($query, 'addons') => 'list',
    default => ''
};

if($subinc != '') {
    include $subinc.'.php';
}