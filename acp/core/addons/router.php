<?php

/**
 * @var string $query
 * @var array $lang
 */

$subinc = match (true) {
    str_starts_with($query, 'addons/plugin/') => 'edit-plugin',
    str_starts_with($query, 'addons/theme/') => 'edit-theme',
    str_starts_with($query, 'addons/install/') => 'install',
    str_starts_with($query, 'addons/catalog/') => 'catalog',
    str_starts_with($query, 'addons') => 'list',
    default => ''
};

// Installing/uninstalling plugins and themes (from URL or the catalog) means
// uploading and extracting arbitrary ZIP files - gated behind the same
// "can upload sensitive files" right as the core update module (see
// acp/core/update/router.php). Viewing the installed-addons list itself
// (and activating/deactivating what's already there) stays open to every
// admin, same as before.
if(in_array($subinc, ['install', 'catalog'], true) && !se_hasPermission('drm_acp_sensitive_files')) {
    echo '<div class="alert alert-info">';
    echo $lang['rm_no_access'];
    echo '</div>';
} elseif($subinc != '') {
    include __DIR__.'/'.$subinc.'.php';
}