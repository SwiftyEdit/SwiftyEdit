<?php

$subinc = match (true) {
    str_starts_with($query, 'update/helpers/') => 'helpers',
    default => 'index'
};

if (!se_hasPermission('drm_acp_sensitive_files')) {
    echo '<div class="alert alert-info">';
    echo $lang['rm_no_access'];
    echo '</div>';
} else {
    include __DIR__ . '/' . $subinc . '.php';
}