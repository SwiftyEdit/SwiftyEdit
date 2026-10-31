<?php

/**
 * SwiftyEdit /admin-xhr/pages/read/
 * the page list (acp/templates/pages/list-items.twig), the active search keywords,
 * keywords and page types of the sidebar - and the parts of the edit form
 * (content field, sibling picker, snapshots) and the info modal
 *
 * global variables
 * @var object $db_content
 * @var array $icon
 * @var array $lang
 * @var array $se_page_types
 * @var array $se_settings
 * @var object $twig
 */

$list_writer_uri = '/admin-xhr/pages/write/';

// text fields are stored html-encoded (se_sanitize_page_inputs), Twig escapes on output
$decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

$action = $_REQUEST['action'] ?? '';

// list pages
if($action == 'list_pages') {

    $type = (int) ($_REQUEST['type'] ?? 1); // 1 = sorted 2 = single

    $active_page = (int) ($_SESSION['pagination_get_pages'] ?? 0);
    $nbr_show_items = (int) ($_SESSION['items_per_page'] ?? 25);
    if($nbr_show_items < 1) {
        $nbr_show_items = 25;
    }
    $limit_start = $active_page * $nbr_show_items;

    // sorting of the single pages, only these columns
    $order_key = $_SESSION['sorting_single_pages'] ?? 'page_lastedit';
    if(!in_array($order_key, ['page_lastedit', 'page_linkname', 'page_priority'])) {
        $order_key = 'page_lastedit';
    }
    $order_direction = (($_SESSION['sorting_single_pages_dir'] ?? '') == 'ASC') ? 'ASC' : 'DESC';

    // "sorted" (type 1) = part of the page tree: has a parent, or is the
    // portal itself (the portal's own page_parent_id is NULL, like an
    // unsorted page's - page_sort = 'portal' is what tells them apart).
    // "single" (type 2) = not in the tree at all.
    if ($type == 1) {
        $db_where = [
            'OR #tree' => [
                'page_parent_id[!]' => null,
                'page_sort' => 'portal'
            ]
        ];
    } else {
        $db_where = [
            'page_parent_id' => null,
            'page_sort[!]' => 'portal'
        ];
    }

    // search: every keyword has to be part of one of the texts
    $search_columns = ['page_title', 'page_meta_description', 'page_meta_keywords', 'page_content', 'page_content_values', 'page_permalink'];
    $search = array_values(array_filter(explode(" ", $_SESSION['pages_text_filter'] ?? '')));
    foreach($search as $i => $keyword) {
        $db_where["OR #search$i"] = array_fill_keys(array_map(static fn($column) => $column.'[~]', $search_columns), "%$keyword%");
    }

    // keywords of the sidebar: every selected keyword has to be part of the meta keywords
    $tags = array_values(array_filter(explode(",", $_SESSION['pages_keyword_filter'] ?? '')));
    foreach($tags as $i => $tag) {
        $db_where["AND #tag$i"] = ["page_meta_keywords[~]" => "%$tag%"];
    }

    // page types of the sidebar: pages of one of the selected types
    $page_types = array_values(array_filter(explode(" ", $_SESSION['checked_page_type_string'] ?? '')));
    if($page_types) {
        $db_where['page_type_of_use'] = $page_types;
    }

    // global filter (top navigation), pages store the status as a word
    $db_where += se_get_global_filter_where('page_language', 'page_status', 'page_labels', [1 => 'public', 2 => 'draft', 3 => 'private', 4 => 'ghost']);

    // the global status 5 means pages with a redirect - selected, they are listed
    // whatever their status is, not selected (but another status), they are hidden
    $global_status = (array) (json_decode($_SESSION['global_filter_status'] ?? '', true) ?? []);
    $global_status = array_filter($global_status, static fn($value) => $value !== '' && $value !== null);
    if($global_status) {
        if(in_array('5', array_map('strval', $global_status))) {
            $db_where['OR #status'] = [
                'page_status' => $db_where['page_status'],
                'page_redirect[!]' => ''
            ];
            unset($db_where['page_status']);
        } else {
            $db_where['OR #no_redirect'] = [
                'page_redirect' => '',
                'AND #redirect_null' => ['page_redirect' => null]
            ];
        }
    }

    $db_where = ["AND" => $db_where];

    $pages_data_cnt = $db_content->count("se_pages", $db_where);

    if ($type == 1) {

        // "sorted" pages are a tree (page_parent_id/position), not a flat
        // SQL order - fetch everything matching the filter (typical page
        // counts make this cheap), flatten depth-first per language in PHP,
        // then paginate the flattened list. See se_index_pages_by_parent()/
        // se_flatten_page_tree() in app/functions/functions.pages.php.
        $all_matching_pages = $db_content->select("se_pages", "*", $db_where);

        $pages_by_language = [];
        foreach ($all_matching_pages as $page) {
            $pages_by_language[$page['page_language']][] = $page;
        }
        ksort($pages_by_language); // page_language ASC, same primary order as before

        $flattened = [];
        foreach ($pages_by_language as $language_pages) {

            $tree_index = se_index_pages_by_parent($language_pages);
            $flat = se_flatten_page_tree($tree_index, null);

            // a page whose page_parent_id doesn't resolve within this same
            // filtered set (e.g. its parent got unsorted/deleted, or a data
            // inconsistency) would otherwise silently vanish from the list -
            // append it unindented instead of losing it
            $included = [];
            foreach ($flat as $row) {
                $included[(int) $row['page_id']] = true;
            }
            foreach ($language_pages as $page) {
                if (!isset($included[(int) $page['page_id']])) {
                    $page['tree_depth'] = 0;
                    $flat[] = $page;
                }
            }

            foreach ($flat as $row) {
                $flattened[] = $row;
            }
        }

        $pages_data = array_slice($flattened, $limit_start, $nbr_show_items);

    } else {
        $pages_data = $db_content->select("se_pages", "*", $db_where + [
            "ORDER" => [$order_key => $order_direction],
            "LIMIT" => [$limit_start, $nbr_show_items]
        ]);
    }

    echo se_list_pages($pages_data, [
        'pagination' => se_print_pagination($list_writer_uri, (int) ceil($pages_data_cnt/$nbr_show_items), $active_page),
        'items_per_page' => $nbr_show_items,
        'total' => $pages_data_cnt,
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

/**
 * list active keywords from search input
 * used in sidebar
 */
if($action == 'list_active_searches') {
    $keywords = array_filter(explode(" ", $_SESSION['pages_text_filter'] ?? ''));
    echo $twig->render('components/list/active-searches.twig', [
        'keywords' => array_map($decode, array_values($keywords)),
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

/**
 * list all keywords
 * used in sidebar
 */
if($action == 'list_keyword_btn') {
    $active_keywords = explode(",", $_SESSION['pages_keyword_filter'] ?? '');
    $get_keywords = se_get_pages_keywords();
    arsort($get_keywords);
    $keywords = [];
    foreach($get_keywords as $keyword => $count) {
        $keyword = trim((string) $keyword);
        $keywords[] = [
            // the value is posted back and compared with the stored (encoded) keywords
            'name' => $decode($keyword),
            'count' => $count,
            'active' => in_array($keyword, $active_keywords)
        ];
    }
    echo $twig->render('components/list/keyword-buttons.twig', [
        'keywords' => $keywords,
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

/**
 * list all page types
 * used in the sidebar
 */
if($action == 'list_page_types') {

    $find_target_page = $db_content->select("se_pages", "page_type_of_use", [
        "page_type_of_use" => $se_page_types
    ]);
    $cnt_page_types = [];
    foreach($find_target_page as $page_type) {
        $cnt_page_types[$page_type] = ($cnt_page_types[$page_type] ?? 0) + 1;
    }
    $active_types = explode(" ", $_SESSION['checked_page_type_string'] ?? '');

    $types = [];
    foreach($se_page_types as $page_type) {
        $types[] = [
            'value' => $page_type,
            'label' => $lang['type_of_use_'.$page_type] ?? $page_type,
            'count' => $cnt_page_types[$page_type] ?? 0,
            'active' => in_array($page_type, $active_types)
        ];
    }
    echo $twig->render('pages/page-types.twig', [
        'types' => $types,
        'writer_uri' => $list_writer_uri
    ]);
    exit;
}

// rebuild just the page_content field when the format-switch dropdown changes
// (see tpl_content_format_switch() in acp/core/templates.php) - an HTMX
// partial swap targeting #pageContentField instead of a full page reload, so
// unsaved edits in the rest of the pages-edit form survive.
if(isset($_REQUEST['content_field'])) {
    require_once __DIR__ . '/functions.php';

    $requested_page_id = $_REQUEST['content_field'];
    if ($requested_page_id !== 'new' && is_numeric($requested_page_id)) {
        $get_page = $db_content->get("se_pages", "*", ["page_id" => (int) $requested_page_id]);
    }
    if (!is_array($get_page ?? null)) {
        $get_page = [];
    }

    $format_override = $_REQUEST['set_content_format'] ?? null;
    $input_text_page_content = se_build_page_content_field($get_page, $format_override);

    echo '<div id="pageContentField">';
    echo se_print_form_input($input_text_page_content);
    echo '</div>';
    exit;
}

// rebuild the "insert after which sibling" radio list on the Position tab
// when the parent dropdown changes (see se_build_parent_options() /
// se_build_sibling_picker() in functions.php) - an HTMX partial swap
// targeting #siblingPicker.
if ($action == 'page_siblings') {
    require_once __DIR__ . '/functions.php';

    $language = $_REQUEST['language'] ?? '';
    $exclude_page_id = (int) ($_REQUEST['exclude_page_id'] ?? 0);
    $parent_value = $_REQUEST['new_parent_id'] ?? '';

    $parent_id = $parent_value !== '' ? (int) $parent_value : se_get_portal_page_id($db_content, $language);

    $siblings = $db_content->select('se_pages', ['page_id', 'page_linkname', 'page_title', 'page_permalink', 'position'], [
        'page_parent_id' => $parent_id,
        'page_id[!]' => $exclude_page_id ?: -1,
        'ORDER' => ['position' => 'ASC'],
    ]);

    // a freshly chosen parent has no "current position" to preselect -
    // default to appending at the end (or "at the start" if it has no
    // children yet)
    $last_sibling = end($siblings);
    $selected_after_page_id = $last_sibling !== false ? (int) $last_sibling['page_id'] : null;

    echo se_build_sibling_picker($siblings, $selected_after_page_id);
    exit;
}

// show snapshots of pages
if(isset($_REQUEST['snapshots']) && is_numeric($_REQUEST['snapshots'])) {
    $snapshot_id = (int) $_REQUEST['snapshots'];

    $get_snapshots = $db_content->select("se_pages_cache",["page_id", "page_linkname", "page_title", "page_lastedit", "page_lastedit_from"],[
        "AND" => [
            "page_id_original" => $snapshot_id,
            "page_cache_type" => "history"
            ],
        "ORDER" => [
            "page_id" => "DESC"
        ]
    ]);

    echo '<div class="card my-3">';
    echo '<div class="accordion accordion-flush" id="accordionVersions">';
    echo '<div class="accordion-item">';
    echo '<div class="accordion-header" id="headingOne">';
    echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVersions" aria-expanded="false" aria-controls="collapseOne">Versions ('.count($get_snapshots).')</button>';
    echo '</div>';
    echo '<div id="collapseVersions" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionVersions">';
    echo '<div class="accordion-body">';
    echo '<div class="scroll-container">';
    echo '<table class="table table-hover">';
    foreach($get_snapshots as $snapshot) {

        $page_id = $snapshot['page_id'];

        echo '<tr>';
        echo '<td>'.se_format_datetime($snapshot['page_lastedit']).'</td>';
        echo '<td>'.htmlentities($snapshot['page_title']).'</td>';
        echo '<td>'.htmlentities($snapshot['page_lastedit_from']).'</td>';
        echo '<td>';
        echo '<form action="/admin/pages/edit/" method="POST">';
        echo '<button class="btn btn-sm btn-default w-100" name="restore_id" value="'.$page_id.'" title="'.$lang['edit'].'">'.$icon['edit'].' '.$lang['edit'].'</button>';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>'; // scroll-container
    echo '</div>'; // accordion-body
    echo '</div>'; // collapse
    echo '</div>'; // accordion-item
    echo '</div>'; // accordion
    echo '</div>';

}

if(isset($_GET['page_info'])) {
    include 'pages-info.php';
}

/**
 * render the page list, the redirects are already filtered by the query (global status 5)
 *
 * @param array|null $data page rows (null if the query failed)
 * @param array $list pagination, items_per_page, total, writer_uri for the template
 * @return string
 */
function se_list_pages(?array $data, array $list = []): string {

    global $twig, $lang;

    $data = $data ?? [];
    $se_labels = se_get_labels();

    $status_labels = [
        'public' => $lang['status_public'],
        'ghost' => $lang['status_ghost'],
        'private' => $lang['status_private'],
        'draft' => $lang['status_draft']
    ];

    // text fields are stored html-encoded (se_sanitize_page_inputs),
    // decode them here because Twig escapes on output
    $decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

    $pages = [];
    foreach($data as $page) {

        $page_thumb_src = '/assets/themes/administration/images/swiftyedit-page-icon.png';
        if($page['page_thumbnail'] != '') {
            $page_thumbs = explode('<->', html_entity_decode($page['page_thumbnail']));
            if($page_thumbs[0] != '') {
                $page_thumb_src = str_replace('../content/images/', '/images/', $page_thumbs[0]);
            }
        }

        $description = $decode($page['page_meta_description']);
        $description_truncated = false;
        if(mb_strlen($description) > 100) {
            $description = mb_substr($description, 0, 100);
            $description_truncated = true;
        }

        $labels = [];
        if($page['page_labels'] != '') {
            foreach(explode(',', $page['page_labels']) as $page_label) {
                foreach($se_labels as $l) {
                    if($page_label == $l['label_id']) {
                        $labels[] = [
                            'color' => $l['label_color'],
                            'title' => $decode($l['label_title'].' '.$l['label_description'])
                        ];
                    }
                }
            }
        }

        // edit + duplicate: editors with the right or explicitly authorized users
        $authorized_users = explode(",", (string) $page['page_authorized_users']);
        $can_edit = se_hasPermission('drm_acp_editpages') || in_array($_SESSION['user_nick'], $authorized_users);

        $pages[] = [
            'id' => (int) $page['page_id'],
            'status' => $page['page_status'],
            'status_label' => $status_labels[$page['page_status']] ?? '',
            // tree_depth is set by the type==1 (sorted) tree flatten above;
            // single/unsorted pages have no tree position, so no indent
            'indent' => (int) ($page['tree_depth'] ?? 0) * 10,
            'thumb_src' => $page_thumb_src,
            'is_portal' => $page['page_sort'] == 'portal',
            'linkname' => $decode($page['page_linkname']),
            'title' => $decode($page['page_title']),
            'has_password' => $page['page_psw'] != '',
            'description' => $description,
            'description_truncated' => $description_truncated,
            'permalink' => $decode($page['page_permalink']),
            'redirect' => $decode($page['page_redirect']),
            'lang' => $page['page_language'],
            'lang_flag_src' => return_language_flag_src($page['page_language']),
            'template' => $page['page_template'],
            'labels' => $labels,
            'modul' => $decode($page['page_modul']),
            'lastedit' => se_format_datetime($page['page_lastedit']),
            'lastedit_from' => $page['page_lastedit_from'],
            'can_edit' => $can_edit
        ];
    }

    return $twig->render('pages/list-items.twig', ['pages' => $pages] + $list);
}
