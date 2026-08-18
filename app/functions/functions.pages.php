<?php

/**
 * get, write and update pages
 * do snapshots from pages
 */

/**
 * @param array $filter
 * @return mixed
 */
function se_get_pages($filter) {

    global $db_content;
    $se_labels = se_get_labels();

    $order = "ORDER BY page_language ASC, page_sort *1 ASC, LENGTH(page_sort), page_sort ASC";

    /* add sorting for single pages */
    $order .= ' ,'.$filter['sort_by'].' '.$filter['sort_direction'];

    if(!isset($filter['labels'])) {
        $filter['labels'] = '';
    }


    /* text search */

    if($filter['text'] != '') {
        $sql_text_filter = '';
        $all_filter = explode(" ",$filter['text']);
        // loop through keywords
        foreach($all_filter as $f) {
            if($f == "") { continue; }
            $sql_text_filter .= "(page_meta_keywords like '%$f%' OR page_meta_description like '%$f%' OR page_title like '%$f%' OR page_linkname like '%$f%' OR page_content like '%$f%') AND";
        }
        $sql_text_filter = substr("$sql_text_filter", 0, -4); // cut the last ' AND'

    } else {
        $sql_text_filter = '';
    }

    // keyword filter
    if($filter['keywords'] != '') {
        $sql_keywords_filter = '';
        $all_filter = explode(" ",$filter['keywords']);
        // loop through keywords
        foreach($all_filter as $f) {
            if($f == "") { continue; }
            $sql_keywords_filter .= "(page_meta_keywords like '%$f%') AND";
        }
        $sql_keywords_filter = substr("$sql_keywords_filter", 0, -4); // cut the last ' AND'
    } else {
        $sql_keywords_filter = '';
    }



    $filter_string = "WHERE page_status IS NOT NULL "; // -> result = match all pages

    /* language filter */

    if($filter['languages'] != '') {
        $sql_lang_filter = "page_language IS NULL OR ";
        $lang = explode('-', $filter['languages']);
        foreach ($lang as $l) {
            if ($l != '') {
                $sql_lang_filter .= "(page_language LIKE '%$l%') OR ";
            }
        }
        $sql_lang_filter = substr("$sql_lang_filter", 0, -3); // cut the last ' OR'
    } else {
        $sql_lang_filter = '';
    }

    /* status filter */
    if($filter['status'] != '') {

        $filter['status'] = str_replace("1","public",$filter['status']);
        $filter['status'] = str_replace("2","draft",$filter['status']);
        $filter['status'] = str_replace("3","private",$filter['status']);
        $filter['status'] = str_replace("4","ghost",$filter['status']);

        $sql_status_filter = "page_status IS NULL OR ";
        $status = explode('-', $filter['status']);
        foreach ($status as $s) {
            if ($s != '') {
                $sql_status_filter .= "(page_status LIKE '%$s%') OR ";
            }
        }
        $sql_status_filter = substr("$sql_status_filter", 0, -3); // cut the last ' OR'
    } else {
        $sql_status_filter = '';
    }

    /* label filter */
    if($filter['labels'] == 'all' OR $filter['labels'] == '') {
        $sql_label_filter = '';
    } else {

        $checked_labels_array = explode('-', $filter['labels']);

        for($i=0;$i<count($se_labels);$i++) {
            $label = $se_labels[$i]['label_id'];
            if(in_array($label, $checked_labels_array)) {
                $sql_label_filter .= "page_labels LIKE '%,$label,%' OR page_labels LIKE '%,$label' OR page_labels LIKE '$label,%' OR page_labels = '$label' OR ";
            }
        }
        $sql_label_filter = substr("$sql_label_filter", 0, -3); // cut the last ' OR'
    }

    /* type filter - column page_type_of_use */
    if($filter['types'] == 'all' OR $filter['types'] == '') {
        $sql_types_filter = '';
    } else {
        $checked_types_array = explode(' ', $filter['types']);
        foreach($checked_types_array as $t) {
            if($t == '') { continue; }
            $sql_types_filter .= "(page_type_of_use LIKE '%$t%') OR ";
        }
        $sql_types_filter = substr("$sql_types_filter", 0, -3); // cut the last ' OR'
    }

    // filter by page_sort - all | sorted | single
    if($filter['sort_type'] == 'all' OR $filter['sort_type'] == '') {
        $sql_sort_type_filter = '';
    } else if($filter['sort_type'] == 'sorted') {
        $sql_sort_type_filter = "(page_sort IS NOT NULL AND page_sort != '') ";
    } else if($filter['sort_type'] == 'single') {
        $sql_sort_type_filter = "(page_sort IS NULL OR page_sort = '' AND page_sort != 'portal') ";
    }


    $sql_filter = $filter_string;

    if($sql_lang_filter != "") {
        $sql_filter .= " AND ($sql_lang_filter) ";
    }
    if($sql_status_filter != "") {
        $sql_filter .= " AND ($sql_status_filter) ";
    }
    if($sql_label_filter != "") {
        $sql_filter .= " AND ($sql_label_filter) ";
    }

    if($sql_text_filter != "") {
        $sql_filter .= " AND ($sql_text_filter) ";
    }

    if($sql_keywords_filter != "") {
        $sql_filter .= " AND ($sql_keywords_filter) ";
    }

    if($sql_types_filter != "") {
        $sql_filter .= " AND ($sql_types_filter) ";
    }

    if($sql_sort_type_filter != "") {
        $sql_filter .= " AND ($sql_sort_type_filter) ";
    }

    $cols = 'page_id, page_status, page_sort, page_thumbnail, page_language, page_title, page_linkname, page_permalink, 
    page_meta_description, page_lastedit, page_lastedit_from, page_labels, page_template, page_redirect, page_modul, page_hits';

    $sql = "SELECT $cols FROM se_pages $sql_filter $order";
    $pages = $db_content->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    return $pages;
}


/**
 * @param array $data $_POST data
 * @return mixed
 */
function se_save_page($data) {

    global $db_content, $custom_fields, $lang;

    $sanitized_data = se_sanitize_page_inputs($data);
    $sanitized_data = se_freeze_editor_content($sanitized_data);

    // get all cols from the installer
    require '../install/contents/se_pages.php';

    // add custom cols
    foreach($custom_fields as $f) {
        $cols[$f] = "";
    }

    // loop through sanitized data
    // if key exists in $cols -> insert
    foreach($sanitized_data as $k => $v) {
        if(array_key_exists($k,$cols)) {
            $insert[$k] = $v;
        }
    }

    $cnt_changes = $db_content->insert("se_pages",$insert);
    $new_page_id = $db_content->id();

    // position needs this page's own page_id to exclude it from its own
    // sibling query, so it's only known now - see se_compute_child_position()
    if (($data['page_role'] ?? '') === 'tree') {
        $after_page_id = ($data['insert_after_page_id'] ?? '') !== '' ? (int) $data['insert_after_page_id'] : null;
        $position = se_compute_child_position($db_content, $sanitized_data['page_parent_id'] ?? null, $new_page_id, $after_page_id);
        $db_content->update('se_pages', ['position' => $position], ['page_id' => $new_page_id]);
    }

    if($cnt_changes->rowCount() > 0) {
        $page_title = $sanitized_data['page_title'];
        record_log("$_SESSION[user_nick]","new Page <i>$page_title</i>","5");
        se_generate_xml_sitemap('pages');
        show_toast($lang['msg_success_page_saved'],'success');
    } else {
        show_toast($lang['msg_error_page_saved'],'danger');
    }

    se_set_content_tags('page', $new_page_id, explode(',', $data['content_tags'] ?? ''));

    return $new_page_id;
}

/**
 * @param array $data $_POST data
 * @param integer $id page_id
 * @return void
 */
function se_update_page($data,$id) {

    global $db_content, $custom_fields, $lang;
    $id = (int) $id;


    $sanitized_data = se_sanitize_page_inputs($data);
    $sanitized_data = se_freeze_editor_content($sanitized_data);

    // get all cols from the installer
    require '../install/contents/se_pages.php';

    // add custom cols
    foreach($custom_fields as $f) {
        $cols[$f] = "";
    }

    // loop through sanitized data
    // if key exists in $cols -> update
    foreach($sanitized_data as $k => $v) {
        if(array_key_exists($k,$cols)) {
            $updates[$k] = $v;
        }
    }

    $cnt_changes = $db_content->update("se_pages", $updates, [
        "page_id" => $id
    ]);

    // position needs this page's own page_id to exclude it from its own
    // sibling query - see se_compute_child_position()
    if (($data['page_role'] ?? '') === 'tree') {
        $after_page_id = ($data['insert_after_page_id'] ?? '') !== '' ? (int) $data['insert_after_page_id'] : null;
        $position = se_compute_child_position($db_content, $sanitized_data['page_parent_id'] ?? null, $id, $after_page_id);
        $db_content->update('se_pages', ['position' => $position], ['page_id' => $id]);
    }

    if($cnt_changes->rowCount() > 0) {
        $page_title = $sanitized_data['page_title'];
        record_log("$_SESSION[user_nick]","page update &raquo;$page_title&laquo;","5");
        se_generate_xml_sitemap('pages');
        show_toast($lang['msg_success_page_saved'],'success');
    } else {
        show_toast($lang['msg_error_page_saved'],'danger');
    }

    se_set_content_tags('page', $id, explode(',', $data['content_tags'] ?? ''));

}

/**
 * @param array $data $_POST data
 * @return void
 */
function se_save_preview_page($data) {
    global $db_content, $custom_fields, $lang;

    $sanitized_data = se_sanitize_page_inputs($data);
    $sanitized_data = se_freeze_editor_content($sanitized_data);
    $page_id_original = $sanitized_data['editpage'];
    // get all cols from the installer
    require '../install/contents/se_pages.php';

    // add custom cols
    foreach($custom_fields as $f) {
        $cols[$f] = "";
    }

    // loop through sanitized data
    // if key exists in $cols -> insert
    foreach($sanitized_data as $k => $v) {
        if(array_key_exists($k,$cols)) {
            $insert[$k] = $v;
        }
    }

    $insert += [
        "page_id_original" => $page_id_original,
        "page_cache_type" => "preview"
    ];

    $db_content->insert("se_pages_cache",$insert);
}



/**
 * Take a snapshot of a page
 * get all data from se_pages by id
 * @param $id
 * @return void
 */
function se_snapshot_page($id) {

    global $db_content, $custom_fields;
    $id = (int) $id;

    $get_data = $db_content->get("se_pages", "*", [
        "page_id" => $id
    ]);

    foreach($get_data as $k => $v) {
        $columns_cache[$k] = $v;
    }

    $columns_cache += [
        "page_id_original" => "$id",
        "page_cache_type" => "history"
    ];

    /* add the custom fields */
    foreach($custom_fields as $f) {
        $columns_cache[$f] = "{${$f}}";
    }

    /* reset id */
    unset($columns_cache['page_id']);

    $db_content->insert("se_pages_cache", $columns_cache);
}

/**
 * @return array
 * get all keywords
 * key is the keyword, value the counter
 */
function se_get_pages_keywords() {

    global $db_content;

    $get_keywords = $db_content->select("se_pages", "page_meta_keywords",[
        "page_meta_keywords[!]" => ""
    ]);

    $get_keywords = array_filter( $get_keywords );
    foreach($get_keywords as $keys) {
        $keys_string .= trim($keys).',';
    }
    $keys_string = str_replace(', ', ',', $keys_string);
    $keys_string = str_replace(' ,', ',', $keys_string);
    $keys_array = explode(",",$keys_string);
    $keys_array = array_filter( $keys_array );
    $count_keywords = array_count_values($keys_array);

    return $count_keywords;
}

/**
 * path to the navigation cache file for a language
 * cache directory is created on first use
 */
function se_getNavigationCachePath($lang) {

    $dir = SE_CONTENT . '/cache/navigation';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    return $dir . '/nav_' . $lang . '.json';
}

/**
 * read the cached navigation entries for a language
 * @return array|null null if no valid cache file exists
 */
function se_get_navigation_from_cache($lang) {

    $cache_file = se_getNavigationCachePath($lang);

    if (!file_exists($cache_file) || !is_readable($cache_file)) {
        return null;
    }

    $content = file_get_contents($cache_file);
    if ($content === false) {
        return null;
    }

    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }

    return $data;
}

/**
 * rebuild the navigation cache file(s) from the database
 * only the public navigation (no draft/ghost pages) is cached -
 * administrators always get a live query so they can preview unpublished pages
 * called after page create/update/delete in the ACP
 *
 * @param string|null $lang rebuild a single language, or all languages if null
 */
function se_build_navigation_cache($lang = null) {

    global $db_content;

    $languages = $lang !== null ? [$lang] : array_unique($db_content->select("se_pages", "page_language", []));

    foreach ($languages as $language) {

        $se_nav = $db_content->select("se_pages", ['page_id', 'page_parent_id', 'position', 'page_classes', 'page_hash', 'page_language', 'page_linkname', 'page_permalink', 'page_target', 'page_title', 'page_sort', 'page_status'], [
                "AND" => [
                    "OR" => [
                        "page_status[!]" => ["draft","ghost"]
                ],
                "page_language" => $language
            ],
                "ORDER" => ["page_sort" => "DESC"]
            ]);

        $se_nav = se_array_multisort($se_nav, 'page_language', SORT_ASC, 'page_sort', SORT_ASC, SORT_NATURAL);

        $cache_file = se_getNavigationCachePath($language);
        file_put_contents($cache_file, json_encode($se_nav, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

/**
 * Page tree helpers, shared by frontend navigation (func_navigation.php) and
 * the backend pages list / position picker (acp/core/pages/). Building
 * blocks around page_parent_id + position instead of parsing the legacy
 * page_sort dot-path - see the 2026_08_17_000003_page_sort_to_parent_position
 * migration and install/contents/se_pages.php.
 */

/**
 * Index a flat page list by page_id, for O(1) lookups (e.g. resolving a
 * page's parent row while walking up the tree).
 * @return array<int, array>
 */
function se_index_pages_by_id(array $pages): array {
    $by_id = [];
    foreach ($pages as $page) {
        $by_id[(int) $page['page_id']] = $page;
    }
    return $by_id;
}

/**
 * Index a flat page list by page_parent_id, each bucket already sorted by
 * position, for O(1) "children of X" lookups. Top-level pages (NULL parent)
 * are filed under the 'root' key.
 * @return array<int|string, array>
 */
function se_index_pages_by_parent(array $pages): array {
    $by_parent = [];
    foreach ($pages as $page) {
        $parent_key = $page['page_parent_id'] ?? 'root';
        $by_parent[$parent_key][] = $page;
    }
    foreach ($by_parent as &$children) {
        usort($children, function ($a, $b) {
            return ($a['position'] <=> $b['position']) ?: ($a['page_id'] <=> $b['page_id']);
        });
    }
    unset($children);
    return $by_parent;
}

/**
 * @param array $index result of se_index_pages_by_parent()
 * @param int|null $parent_id null for top-level pages
 * @return array children of $parent_id, sorted by position
 */
function se_get_page_children(array $index, $parent_id): array {
    return $index[$parent_id ?? 'root'] ?? [];
}

/**
 * Depth-first flatten of the tree rooted at $parent_id, each row tagged with
 * a 'tree_depth' key (0 for the direct children of $parent_id). Used for
 * indentation in flat list views (backend pages list, position picker).
 *
 * @param array $index result of se_index_pages_by_parent()
 * @param int|null $parent_id where to start ("root" tree, or a subtree)
 * @param int|null $exclude_page_id skip this page - and, since we never
 *   recurse into a skipped page, its whole subtree. Used to keep a page
 *   (and its descendants) out of its own "choose new parent" picker.
 */
function se_flatten_page_tree(array $index, $parent_id = null, int $depth = 0, ?int $exclude_page_id = null): array {
    $flat = [];
    foreach (se_get_page_children($index, $parent_id) as $page) {
        if ($exclude_page_id !== null && (int) $page['page_id'] === $exclude_page_id) {
            continue;
        }
        $page['tree_depth'] = $depth;
        $flat[] = $page;
        foreach (se_flatten_page_tree($index, (int) $page['page_id'], $depth + 1, $exclude_page_id) as $child) {
            $flat[] = $child;
        }
    }
    return $flat;
}

/**
 * Chain of ancestor page_ids for $page_id, from the top-level page (a direct
 * child of the portal page) down to $page_id itself - the portal page is
 * deliberately excluded, so count($chain) is the page's nav depth (1 for a
 * top-level page, matching the old page_sort segment-count convention).
 * Empty for the portal page itself, or a page with no resolvable path to the
 * portal (unsorted page, or $portal_id not given/found).
 *
 * @param array $pages_by_id result of se_index_pages_by_id()
 * @param int|null $portal_id the language's portal page_id
 */
function se_get_page_ancestor_chain(array $pages_by_id, $page_id, $portal_id = null): array {
    $chain = [];
    $seen = [];

    while ($page_id !== null && isset($pages_by_id[$page_id]) && !isset($seen[$page_id])) {
        if ($portal_id !== null && (int) $page_id === (int) $portal_id) {
            break; // stop at (and exclude) the portal page
        }
        $seen[$page_id] = true;
        array_unshift($chain, (int) $page_id);
        $page_id = $pages_by_id[$page_id]['page_parent_id'];
    }

    return $chain;
}

/**
 * @return int|null the page_id of $language's portal page, or null if it
 *   has none (shouldn't normally happen, but a page_role='tree' page with
 *   "top level" chosen needs somewhere to point page_parent_id at)
 */
function se_get_portal_page_id($db_content, string $language): ?int {
    $portal = $db_content->get('se_pages', ['page_id'], [
        'page_sort' => 'portal',
        'page_language' => $language,
    ]);
    return is_array($portal) ? (int) $portal['page_id'] : null;
}

/**
 * Compute the position for placing $moving_page_id as a child of $parent_id,
 * immediately after $after_page_id (first child if null). Uses the midpoint
 * of the surrounding siblings' positions (matching the 10/20/30-with-gaps
 * scheme the page_sort->parent_id/position migration seeded); if there's no
 * room left (adjacent siblings with no gap between them), renumbers all of
 * $parent_id's children first and retries once - see se_renumber_children().
 *
 * Called after the page itself has already been inserted/updated (see
 * se_save_page()/se_update_page() in this file), since it needs the page's
 * own page_id to exclude it from its own sibling query.
 *
 * @param int|null $parent_id
 * @param int $moving_page_id
 * @param int|null $after_page_id null = place as the first child
 */
function se_compute_child_position($db_content, ?int $parent_id, int $moving_page_id, ?int $after_page_id): int {

    $position = se_find_gap_position($db_content, $parent_id, $moving_page_id, $after_page_id);

    if ($position === null) {
        se_renumber_children($db_content, $parent_id, $moving_page_id);
        $position = se_find_gap_position($db_content, $parent_id, $moving_page_id, $after_page_id) ?? 10;
    }

    return $position;
}

/**
 * @return int|null null means "no room for a clean midpoint" - caller should
 *   renumber the siblings and retry
 */
function se_find_gap_position($db_content, ?int $parent_id, int $moving_page_id, ?int $after_page_id): ?int {

    $siblings = $db_content->select('se_pages', ['page_id', 'position'], [
        'page_parent_id' => $parent_id,
        'page_id[!]' => $moving_page_id,
        'ORDER' => ['position' => 'ASC'],
    ]);

    if (empty($siblings)) {
        return 10;
    }

    if ($after_page_id === null) {
        $first_position = (int) $siblings[0]['position'];
        return $first_position > 1 ? intdiv($first_position, 2) : null;
    }

    foreach ($siblings as $i => $sibling) {
        if ((int) $sibling['page_id'] !== $after_page_id) {
            continue;
        }

        $prev_position = (int) $sibling['position'];
        $next_position = isset($siblings[$i + 1]) ? (int) $siblings[$i + 1]['position'] : null;

        if ($next_position === null) {
            return $prev_position + 10;
        }

        $mid = intdiv($prev_position + $next_position, 2);
        return $mid > $prev_position ? $mid : null;
    }

    // $after_page_id is stale (no longer a child of $parent_id) - append at the end
    $last_sibling = end($siblings);
    return ((int) $last_sibling['position']) + 10;
}

/**
 * Reassign fresh 10/20/30... positions to all children of $parent_id (except
 * $exclude_page_id), in their current order. Only called by
 * se_compute_child_position() when the existing gaps have run out.
 */
function se_renumber_children($db_content, ?int $parent_id, int $exclude_page_id): void {

    $siblings = $db_content->select('se_pages', ['page_id'], [
        'page_parent_id' => $parent_id,
        'page_id[!]' => $exclude_page_id,
        'ORDER' => ['position' => 'ASC'],
    ]);

    $position = 10;
    foreach ($siblings as $sibling) {
        $db_content->update('se_pages', ['position' => $position], ['page_id' => $sibling['page_id']]);
        $position += 10;
    }
}
