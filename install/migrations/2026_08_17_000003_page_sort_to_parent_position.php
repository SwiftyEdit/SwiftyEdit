<?php

/**
 * Backfill page_parent_id + position from the legacy page_sort dot-path
 * ("100.200.100" style). page_sort itself is left untouched for now (kept
 * as a fallback until the parent_id/position-based tree code has proven
 * itself for a release or two) - see install/contents/se_pages.php.
 *
 * Resolution happens per page_language, since every language has its own
 * "portal" (root) page and its own path hierarchy:
 *   - page_sort === 'portal'      -> page_parent_id = NULL, position = 0 (root)
 *   - page_sort === '' / NULL     -> page_parent_id = NULL, position = 0 (unsorted, not in nav)
 *   - "100"                       -> parent is the portal page of the same language, position 100
 *   - "100.200.100"               -> parent is the page whose page_sort is "100.200", position 100
 *
 * A path segment that doesn't resolve to an existing page (or a language
 * with no portal page at all) is logged to $_SESSION['protocol'] - the
 * update's usual log channel, see install/inc.update.php and
 * acp/core/update/data-writer.php - and skipped rather than aborting the
 * whole migration over a handful of inconsistent rows.
 */

return function ($db_content, $db_user, $db_posts) {

    $pages = $db_content->select('se_pages', ['page_id', 'page_language', 'page_sort']);

    $by_language = [];
    foreach ($pages as $page) {
        $by_language[$page['page_language']][] = $page;
    }

    foreach ($by_language as $language => $language_pages) {

        $portal_id = null;
        foreach ($language_pages as $page) {
            if ($page['page_sort'] === 'portal') {
                $portal_id = $page['page_id'];
                $db_content->update('se_pages', [
                    'page_parent_id' => null,
                    'position' => 0,
                ], [
                    'page_id' => $page['page_id'],
                ]);
                break;
            }
        }

        // page_sort -> page_id, for resolving parent paths within this language
        $path_map = [];
        foreach ($language_pages as $page) {
            if (preg_match('/^\d+(\.\d+)*$/', $page['page_sort'])) {
                $path_map[$page['page_sort']] = $page['page_id'];
            }
        }

        foreach ($language_pages as $page) {
            $sort = $page['page_sort'];

            if ($sort === 'portal') {
                continue; // handled above
            }

            if ($sort === '' || $sort === null) {
                $db_content->update('se_pages', [
                    'page_parent_id' => null,
                    'position' => 0,
                ], [
                    'page_id' => $page['page_id'],
                ]);
                continue;
            }

            if (!preg_match('/^\d+(\.\d+)*$/', $sort)) {
                $_SESSION['protocol'] .= '<b class="text-warning">migration: skipped page ' . (int) $page['page_id']
                    . ' (' . htmlspecialchars($language, ENT_QUOTES) . ') - unrecognized page_sort "'
                    . htmlspecialchars((string) $sort, ENT_QUOTES) . '"</b><|>';
                continue;
            }

            $segments = explode('.', $sort);
            $position = (int) end($segments);

            if (count($segments) === 1) {
                $parent_id = $portal_id;
            } else {
                $parent_path = implode('.', array_slice($segments, 0, -1));
                $parent_id = $path_map[$parent_path] ?? null;
            }

            if ($parent_id === null) {
                $_SESSION['protocol'] .= '<b class="text-warning">migration: skipped page ' . (int) $page['page_id']
                    . ' (' . htmlspecialchars($language, ENT_QUOTES) . ') - could not resolve parent for page_sort "'
                    . htmlspecialchars($sort, ENT_QUOTES) . '"</b><|>';
                continue;
            }

            $db_content->update('se_pages', [
                'page_parent_id' => $parent_id,
                'position' => $position,
            ], [
                'page_id' => $page['page_id'],
            ]);
        }
    }
};
