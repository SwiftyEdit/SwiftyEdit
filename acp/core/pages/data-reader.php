<?php

/**
 * global variables
 * @var object $db_content
 * @var object $db_settings
 * @var array $icon
 * @var array $lang
 * @var array $se_page_types
 * @var array $se_settings
 *
 */

use Medoo\Medoo;

$writer_uri = '/admin/pages/edit/';
$duplicate_uri = '/admin/pages/duplicate/';

$se_labels = se_get_labels();

$global_filter_languages = json_decode($_SESSION['global_filter_languages'],true);
$global_filter_status = json_decode($_SESSION['global_filter_status'],true);
$global_filter_label = json_decode($_SESSION['global_filter_label'],true);

$sort_single_pages = 'page_lastedit';
$sort_single_pages_direction = 'DESC';


if(!isset($_SESSION['sorting_single_pages'])) {
    $_SESSION['sorting_single_pages'] = $sort_single_pages;
}
if(!isset($_SESSION['sorting_single_pages_dir'])) {
    $_SESSION['sorting_single_pages_dir'] = $sort_single_pages_direction;
}

// list pages
if($_REQUEST['action'] == 'list_pages') {

    $type = (int) $_REQUEST['type']; // 1 = sorted 2 = single

    // defaults
    $order_by = 'page_lastedit';
    $order_direction = 'DESC';
    $limit_start = $_SESSION['pagination_get_pages'] ?? 0;
    $nbr_show_items = (int) ($_SESSION['items_per_page'] ?? 25);

    $match_str = $_SESSION['pages_text_filter'] ?? '';
    $keyword_str = $_SESSION['pages_keyword_filter'] ?? '';
    $page_type_str = $_SESSION['checked_page_type_string'] ?? '';
    $order_key = $_SESSION['sorting_single_pages'] ?? $order_by;
    $order_direction = $_SESSION['sorting_single_pages_dir'] ?? $order_direction;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    // "sorted" (type 1) = part of the page tree: has a parent, or is the
    // portal itself (the portal's own page_parent_id is NULL, like an
    // unsorted page's - page_sort = 'portal' is what tells them apart).
    // "single" (type 2) = not in the tree at all.
    if ($type == 1) {
        $filter_base = [
            'OR' => [
                'page_parent_id[!]' => null,
                'page_sort' => 'portal'
            ]
        ];
    } else {
        $filter_base = [
            'AND' => [
                'page_parent_id' => null,
                'page_sort[!]' => 'portal'
            ]
        ];
    }

    // text search
    $filter_by_str = [];
    if($match_str != '') {
        $this_filter = explode(" ",$match_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_str = [
                "OR" => [
                    "page_title[~]" => "%$f%",
                    "page_meta_description[~]" => "%$f%",
                    "page_meta_keywords[~]" => "%$f%",
                    "page_content[~]" => "%$f%",
                    "page_content_values[~]" => "%$f%",
                    "page_permalink[~]" => "%$f%"
                ]
            ];
        }
    }

    // filter by keywords
    $filter_by_keyword = [];
    if($keyword_str != '') {
        $this_filter = explode(",",$keyword_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_keyword = [
                "page_meta_keywords[~]" => "$f"
            ];
        }
    }

    // filter by page types
    $filter_by_page_types = [];
    if($page_type_str != '') {
        $this_filter = explode(" ",$page_type_str);
        foreach($this_filter as $f) {
            if($f == "") { continue; }
            $filter_by_page_types = [
                "page_type_of_use[~]" => "$f"
            ];
        }
    }

    // global language filter
    $filter_by_language = [];
    if(is_array($global_filter_languages)) {
        $lang_filter = array_filter($global_filter_languages);
        $filter_by_language = [
            "page_language[~]" => $lang_filter
        ];
    }

    // global status filter and redirects
    $filter_by_status = [];
    $filter_by_redirect = [];
    if(is_array($global_filter_status)) {
        $status_filter = array_filter($global_filter_status);

        $index = array_search(5,$status_filter);
        if ($index !== false) {
            $filter_by_redirect = [
                "page_redirect[!]" => ' '
            ];
        }

        $index = array_search(1,$status_filter);
        if ($index !== false) { $status_filter[$index] = 'public'; }
        $index = array_search(2,$status_filter);
        if ($index !== false) { $status_filter[$index] = 'draft'; }
        $index = array_search(3,$status_filter);
        if ($index !== false) { $status_filter[$index] = 'private'; }
        $index = array_search(4,$status_filter);
        if ($index !== false) { $status_filter[$index] = 'ghost'; }
        $index = array_search(5,$status_filter);
        if ($index !== false) { $status_filter[$index] = 'redirect'; }
        $filter_by_status = [
            "page_status[~]" => $status_filter
        ];
    }

    // global label filter
    $filter_by_label = [];
    if(is_array($global_filter_label)) {
        $label_filter = array_filter($global_filter_label);
        $filter_by_label = [
            "page_labels[~]" => $label_filter
        ];
    }

    $db_where = [
        "AND" => $filter_base+$filter_by_str+$filter_by_keyword+$filter_by_page_types+$filter_by_language+$filter_by_status+$filter_by_redirect+$filter_by_label
    ];

    // order single pages
    $db_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];

    $db_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

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
        $pages_data = $db_content->select("se_pages","*",
            $db_where+$db_order+$db_limit
        );
    }

    $nbr_pag_pages = ceil($pages_data_cnt/$nbr_show_items);
    
    echo '<div class="card p-3">';
    echo '<div class="d-flex justify-content-end">';
    echo '<div>';
    echo se_print_pagination('/admin-xhr/pages/write/',$nbr_pag_pages,$_SESSION['pagination_get_pages']);
    echo '</div>';
    echo '<div class="ps-3">';
    echo '<div class="input-group mb-3">';
    echo '<input type="number" class="form-control" hx-post="/admin-xhr/pages/write/" hx-swap="none" hx-trigger="keyup delay:500ms changed" hx-include="[name=\'csrf_token\']" name="items_per_page" min="5" max="99" value="'.$nbr_show_items.'">';
    echo '<span class="input-group-text" id="basic-addon2"> / '.$pages_data_cnt.'</span>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo se_list_pages($pages_data);
    echo '</div>';


}

/**
 * list active keywords from search input
 * used in sidebar
 */
if($_REQUEST['action'] == 'list_active_searches') {

    if(isset($_SESSION['pages_text_filter']) AND $_SESSION['pages_text_filter'] != "") {
        unset($all_filter);
        $all_filter = explode(" ", $_SESSION['pages_text_filter']);

        foreach($all_filter as $f) {
            if($_REQUEST['rm_keyword'] == "$f") { continue; }
            if($f == "") { continue; }
            $btn_remove_keyword .= '<button class="btn btn-sm btn-default" name="rmkey" value="'.$f.'" hx-post="/admin-xhr/pages/write/" hx-trigger="click" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button> ';
        }
    }

    if(isset($btn_remove_keyword)) {
        echo '<div class="d-inline">';
        echo '<p style="padding-top:5px;">' . $btn_remove_keyword . '</p>';
        echo '</div><hr>';
    }
    exit;
}

/**
 * list all keywords
 * used in sidebar
 */
if($_REQUEST['action'] == 'list_keyword_btn') {
    $get_keywords = se_get_pages_keywords();
    arsort($get_keywords);
    $vals = ['csrf_token' => $_SESSION['token']];
    echo '<div class="scroll-container">';
    foreach($get_keywords as $k => $v) {
        $k = trim($k);
        if(str_contains($_SESSION['pages_keyword_filter'],$k)) {
            echo '<button name="remove_keyword" value="'.$k.'" hx-post="/admin-xhr/pages/write/" hx-trigger="click" hx-swap="none" hx-vals=\''.json_encode($vals).'\' class="btn btn-default active btn-xs mb-1">'.$k.' <span class="badge bg-secondary">'.$v.'</span></button> ';
        } else {
            echo '<button name="add_keyword" value="'.$k.'" hx-post="/admin-xhr/pages/write/" hx-trigger="click" hx-swap="none" hx-vals=\''.json_encode($vals).'\' class="btn btn-default btn-xs mb-1">'.$k.' <span class="badge bg-secondary">'.$v.'</span></button> ';
        }
    }
    echo '</div>';
    exit;
}

/**
 * list all page types
 * used in the sidebar
 */

if($_REQUEST['action'] == 'list_page_types') {

    $find_target_page = $db_content->select("se_pages", "page_type_of_use", [
        "page_type_of_use" => $se_page_types
    ]);

    $cnt_page_types = array_count_values($find_target_page);
    echo '<div class="scroll-container">';
    foreach($se_page_types as $types) {
        $str = 'type_of_use_'.$types;
        $name = $lang[$str];
        $classes = 'list-group-item list-group-item-action d-flex justify-content-between align-items-start';
        if(str_contains($_SESSION['checked_page_type_string'],"$types")) {
            $classes .= ' active';
        }
        $vals = ['csrf_token' => $_SESSION['token']];
        echo '<button class=" '.$classes.'" name="filter_type" value="'.$types.'" hx-post="/admin-xhr/pages/write/" hx-trigger="click" hx-swap="none" hx-vals=\''.json_encode($vals).'\'>';
        echo '<div class="me-auto">'.$name.'</div>';
        if($cnt_page_types[$types] < 1) {
            echo '<span class="badge text-bg-danger">0</span>';
        } else {
            echo '<span class="badge text-bg-secondary">' . $cnt_page_types[$types] . '</span>';
        }
        echo '</button>';
    }
    echo '</div>';
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
if ($_REQUEST['action'] == 'page_siblings') {
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
 * @param $data array page contents
 * @return string
 */

function se_list_pages($data) {

    global $twig, $global_filter_status, $lang, $se_labels;

    if(!is_array($data)) {
        return '';
    }

    $status_labels = [
        'public' => $lang['status_puplic'],
        'ghost' => $lang['status_ghost'],
        'private' => $lang['status_private'],
        'draft' => $lang['status_draft']
    ];

    // text fields are stored html-encoded (se_sanitize_page_inputs),
    // decode them here because Twig escapes on output
    $decode = static fn($value) => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');

    $pages = [];
    foreach($data as $page) {

        // redirects are only listed if the global status filter includes them
        if($page['page_redirect'] != '' && is_array($global_filter_status) && !in_array("5", $global_filter_status)) {
            continue;
        }

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

    return $twig->render('pages/list-items.twig', ['pages' => $pages]);
}
