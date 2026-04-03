<?php

$subinc = match (true) {
    str_starts_with($query, 'addons/plugin/') => 'edit-plugin',
    str_starts_with($query, 'addons/theme/') => 'edit-theme',
    str_starts_with($query, 'addons/install/') => 'install',
    str_starts_with($query, 'addons') => 'list',
    default => ''
};

if($subinc != '') {
    include __DIR__.'/'.$subinc.'.php';
}