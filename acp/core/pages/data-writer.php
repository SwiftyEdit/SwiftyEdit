<?php

/**
 * SwiftyEdit /admin-xhr/pages/write/
 * filters, sorting and pagination of the page list - and save/delete of the edit form
 *
 * global variables
 * @var array $lang
 * @var object $db_content
 */

// pagination
if(isset($_POST['pagination'])) {
    $_SESSION['pagination_get_pages'] = (int) $_POST['pagination'];
    header( "HX-Trigger: update_pages_list");
    exit;
}

// items per page
if(isset($_POST['items_per_page'])) {
    $_SESSION['items_per_page'] = (int) $_POST['items_per_page'];
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    header( "HX-Trigger: update_pages_list");
    exit;
}

// text search
if(isset($_POST['pages_text_filter'])) {
    $add_text_filter = sanitizeUserInputs($_POST['pages_text_filter']);
    $text_filter = $_SESSION['pages_text_filter'] ?? '';
    if($add_text_filter != '' && !in_array($add_text_filter, explode(' ', $text_filter))) {
        $_SESSION['pages_text_filter'] = trim($text_filter.' '.$add_text_filter);
    }
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    header( "HX-Trigger: update_pages_list");
    exit;
}

// remove keyword from filter list
if(isset($_POST['rmkey'])) {
    // the keywords are stored sanitized (see above), the button sends the plain keyword
    $remove_keyword = sanitizeUserInputs($_POST['rmkey']);
    $keywords = explode(' ', $_SESSION['pages_text_filter'] ?? '');
    $keywords = array_filter($keywords, static fn($keyword) => $keyword != '' && $keyword != $remove_keyword);
    $_SESSION['pages_text_filter'] = implode(' ', $keywords);
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    header( "HX-Trigger: update_pages_list");
    exit;
}

// keywords of the sidebar, add or remove one
if(isset($_POST['add_keyword']) || isset($_POST['remove_keyword'])) {
    // the buttons send the plain keyword, the meta keywords are stored sanitized
    $keyword = trim(sanitizeUserInputs($_POST['add_keyword'] ?? $_POST['remove_keyword']));
    $keywords = array_filter(explode(",", $_SESSION['pages_keyword_filter'] ?? ''), static fn($k) => $k != '' && $k != $keyword);
    if(isset($_POST['add_keyword']) && $keyword != '') {
        $keywords[] = $keyword;
    }
    $_SESSION['pages_keyword_filter'] = implode(",", $keywords);
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    header( "HX-Trigger: update_pages_list");
    exit;
}

// page types of the sidebar, add or remove one
if(isset($_POST['filter_type'])) {
    $type_filter = clean_filename($_POST['filter_type']);
    $types = array_filter(explode(" ", $_SESSION['checked_page_type_string'] ?? ''), static fn($t) => $t != '' && $t != $type_filter);
    if(!in_array($type_filter, explode(" ", $_SESSION['checked_page_type_string'] ?? ''))) {
        $types[] = $type_filter;
    }
    $_SESSION['checked_page_type_string'] = implode(" ", $types);
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    header( "HX-Trigger: update_pages_list");
    exit;
}

// sorting of the single pages, the buttons send the direction with the column
if(isset($_POST['sorting_single_pages_asc']) || isset($_POST['sorting_single_pages_desc'])) {
    $sorting_columns = [
        'lastedit' => 'page_lastedit',
        'linkname' => 'page_linkname',
        'priority' => 'page_priority'
    ];
    $_SESSION['sorting_single_pages'] = $sorting_columns[$_POST['sorting_single_pages'] ?? ''] ?? 'page_lastedit';
    $_SESSION['sorting_single_pages_dir'] = isset($_POST['sorting_single_pages_asc']) ? 'ASC' : 'DESC';
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    header( "HX-Trigger: update_pages_list");
    exit;
}


// delete pages by id
// delete from se_pages se_pages_cache and the assigned comments
if(isset($_POST['delete_page'])) {

    $delete_id = (int) $_POST['delete_page'];
    $comment_id = 'p'.$delete_id;

    $del_page = $db_content->delete("se_pages", [
        "page_id" => $delete_id
    ]);
    $db_content->delete("se_pages_cache", [
        "page_id_original" => $delete_id
    ]);
    $db_content->delete("se_pages_cache", [
        "page_id_original" => NULL
    ]);
    $db_content->delete("se_comments", [
        "comment_parent" => $comment_id
    ]);
    $db_content->delete("se_tags_relations", [
        "content_type" => "page",
        "content_id" => $delete_id
    ]);

    if($del_page->rowCount() > 0) {
        record_log($_SESSION['user_nick'],"deleted page id: $delete_id",10);
        se_generate_xml_sitemap('pages');
        se_build_navigation_cache();
        show_toast($lang['msg_success_page_deleted'],'success');
    }

}

// save or update pages
if(isset($_POST['save_page'])) {

    $page_id = null;
    $new_page_id = null;

    // update mode
    if(is_numeric($_POST['save_page'])) {
        $page_id = (int) $_POST['save_page'];
        se_update_page($_POST,$page_id);
        se_snapshot_page($page_id);
    }

    // new page
    if($_POST['save_page'] == 'new') {
        $new_page_id = se_save_page($_POST);
        se_snapshot_page($new_page_id);
        $page_id = $new_page_id;
    }

    // cache files
    mods_check_in();
    cache_url_paths();
    se_build_navigation_cache();

// build hook context
    $context = [
        'page_id' => $page_id,
        'data'    => $_POST,
        'user_id' => $_SESSION['user_id'],
    ];

// selected hooks from POST (or empty array)
    $selectedHooks = $_POST['hooks']['page.updated'] ?? [];

// run only selected callbacks for this hook
    if (!empty($selectedHooks)) {
        se_do_backend_hook_selected('page.updated', $selectedHooks, $context);
    }

    if(is_numeric($new_page_id)) {
        header( "HX-Redirect: /admin/pages/edit/$new_page_id/");
    }

    header( "HX-Trigger: updated_pages");
}