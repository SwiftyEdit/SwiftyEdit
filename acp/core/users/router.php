<?php
error_reporting(E_ALL ^E_WARNING ^E_NOTICE ^E_DEPRECATED);

$subinc = match (true) {
    str_starts_with($query, 'users/new/') => 'users-edit',
    str_starts_with($query, 'users/edit/') => 'users-edit',
    str_starts_with($query, 'users/groups/new/') => 'users-groups',
    str_starts_with($query, 'users/groups/') => 'users-groups',
    str_starts_with($query, 'users/settings/') => 'users-settings',
    str_starts_with($query, 'users') => 'users-list',
    default => 'users-list'
};

if (!se_hasPermission('drm_acp_user')) {
    echo '<div class="alert alert-info">';
    echo $lang['rm_no_access'];
    echo '</div>';
} else {
    include $subinc.'.php';
}