<?php

$subinc = match (true) {
    str_starts_with($query, 'addons/plugin/') => 'edit-plugin',
    str_starts_with($query, 'addons/theme/') => 'edit-theme',
    str_starts_with($query, 'addons') => 'list',
    default => ''
};

if($subinc != '') {
    include $subinc.'.php';
}