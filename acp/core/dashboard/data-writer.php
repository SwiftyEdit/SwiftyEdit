<?php

/**
 * dashboard data writer - central cache management (Dashboard "Cache" tab)
 *
 * @var object $db_content
 * @var array $icon
 * @var array $lang
 */

if (isset($_POST['cache_target'])) {

    $target = $_POST['cache_target'];

    switch ($target) {

        case 'smarty':
            se_delete_smarty_cache('all');
            break;

        case 'navigation':
            se_build_navigation_cache();
            break;

        case 'urls':
            cache_url_paths();
            break;

        case 'categories':
            se_updateCategoriesCache();
            break;

        case 'tags':
            se_updateTagsCache();
            break;

        case 'preferences':
            se_build_preferences_cache();
            break;

        case 'snippets':
            se_rebuild_all_snippets_cache();
            break;

        case 'products_clear':
            se_clearProductCache();
            break;

        case 'products_rebuild':
            se_rebuild_all_product_cache();
            break;

        case 'all':
            se_delete_smarty_cache('all');
            se_build_navigation_cache();
            cache_url_paths();
            se_updateCategoriesCache();
            se_updateTagsCache();
            se_build_preferences_cache();
            se_rebuild_all_snippets_cache();
            se_rebuild_all_product_cache();
            break;

        default:
            http_response_code(400);
            exit;
    }

    header("HX-Trigger: cache_rebuilt");
    echo '<span class="badge rounded-pill text-bg-success alert-auto-close">'.$icon['check'].'</span>';
    exit;
}

// manually flush OPcache ("OPcache leeren" card). Resetting the server's
// bytecode cache is a sensitive, server-wide operation, so it's gated the
// same way as the update/addon installers instead of just the general
// dashboard access every backend user already has (see
// acp/core/update/data-writer.php, acp/core/addons/data-writer.php).
if (isset($_POST['opcache_reset'])) {

    if (!se_hasPermission('drm_acp_sensitive_files')) {
        http_response_code(403);
        echo '<span class="badge rounded-pill text-bg-danger alert-auto-close">'.$lang['rm_no_access'].'</span>';
        exit;
    }

    $status = se_get_opcache_status();

    if (empty($status['available']) || empty($status['enabled'])) {
        echo '<span class="badge rounded-pill text-bg-warning alert-auto-close">'.$lang['opcache_msg_reset_unavailable'].'</span>';
        exit;
    }

    if (opcache_reset()) {
        record_log($_SESSION['user_nick'], 'cleared OPcache', '6');
        header("HX-Trigger: opcache_reset");
        echo '<span class="badge rounded-pill text-bg-success alert-auto-close">'.$icon['check'].' '.$lang['opcache_msg_reset_success'].'</span>';
    } else {
        // e.g. blocked via opcache.restrict_api
        echo '<span class="badge rounded-pill text-bg-danger alert-auto-close">'.$lang['opcache_msg_reset_failed'].'</span>';
    }

    exit;
}
