<?php

$subinc = match (true) {
    str_starts_with($query, 'users/new/') => 'users-edit',
    str_starts_with($query, 'users/edit/') => 'users-edit',
    str_starts_with($query, 'users/groups/new/') => 'users-groups',
    str_starts_with($query, 'users/groups/') => 'users-groups',
    str_starts_with($query, 'users/settings/') => 'users-settings',
    str_starts_with($query, 'users') => 'users-list',
    default => 'users-list'
};

include $subinc.'.php';
