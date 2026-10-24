<?php

/**
 * Delete page versions beyond the "number of page versions" setting.
 *
 * Since 2.0 the setting was read but never applied, so se_pages_cache kept
 * every version of every page and could grow to hundreds of MB. New
 * snapshots are pruned when a page is saved (se_snapshot_page()); this
 * removes what piled up before. The newest versions of each page are kept.
 *
 * The database file does not shrink by this alone - SQLite reuses the freed
 * space for new data. VACUUM can't run inside the migration's transaction.
 */

return function ($db_content, $db_user, $db_posts) {

    $max_versions = (int) $db_content->get('se_options', 'option_value', [
        'option_module' => 'se',
        'option_key' => 'prefs_nbr_page_versions'
    ]) ?: 25;
    $max_versions = max(1, $max_versions);

    $page_ids = $db_content->select('se_pages_cache', 'page_id_original', [
        'page_cache_type' => 'history',
        'GROUP' => 'page_id_original'
    ]);

    foreach ($page_ids as $page_id) {
        if ((int) $page_id < 1) {
            continue;
        }

        $snapshot_ids = $db_content->select('se_pages_cache', 'page_id', [
            'page_id_original' => (int) $page_id,
            'page_cache_type' => 'history',
            'ORDER' => ['page_id' => 'DESC']
        ]);

        $delete_ids = array_slice($snapshot_ids, $max_versions);

        if (!empty($delete_ids)) {
            $db_content->delete('se_pages_cache', [
                'page_id' => $delete_ids
            ]);
        }
    }
};
