<?php

$subinc = match (true) {
    str_starts_with($query, 'snippets/new/') => 'snippets-edit',
    str_starts_with($query, 'snippets/edit/') => 'snippets-edit',
    str_starts_with($query, 'snippets/duplicate/') => 'snippets-edit',
    str_starts_with($query, 'snippets') => 'snippets-list',
    default => 'snippets-list'
};

include $subinc.'.php';