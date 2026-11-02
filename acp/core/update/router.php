<?php

/**
 * SwiftyEdit backend
 * switch file for section update - core updates need the "can upload sensitive files" right
 *
 * global variables
 * @var string $query - the current url
 * @var array $lang
 */

$subinc = match (true) {
    str_starts_with($query, 'update/helpers/') => 'helpers',
    default => 'index'
};

if (!se_hasPermission('drm_acp_sensitive_files')) {
    echo '<div class="alert alert-info">'.$lang['rm_no_access'].'</div>';
} else {
    include __DIR__ . '/' . $subinc . '.php';
}
