<?php

/**
 * dashboard data writer - central cache management (Dashboard "Cache" tab)
 *
 * @var object $db_content
 * @var array $icon
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
