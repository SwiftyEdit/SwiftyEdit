<?php

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

if(isset($_POST['pages_text_filter'])) {
    $_SESSION['pages_text_filter'] = $_SESSION['pages_text_filter'] . ' ' . sanitizeUserInputs($_POST['pages_text_filter']);
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    header( "HX-Trigger: update_pages_list");
    exit;
}

/* remove keyword from filter list */
if(isset($_POST['rmkey'])) {
    $all_filter = explode(" ", $_SESSION['pages_text_filter']);
    $_SESSION['pages_text_filter'] = '';
    foreach($all_filter as $f) {
        if($_POST['rmkey'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['pages_text_filter'] .= "$f ";
    }
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    header( "HX-Trigger: update_pages_list");
    exit;
}

if(isset($_POST['add_keyword'])) {
    $_SESSION['pages_keyword_filter'] = $_SESSION['pages_keyword_filter'] . ',' . sanitizeUserInputs($_POST['add_keyword']);
    header( "HX-Trigger: update_pages_list");
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    exit;
}

if(isset($_POST['remove_keyword'])) {
    $all_keywords_filter = explode(",", $_SESSION['pages_keyword_filter']);
    $_SESSION['pages_keyword_filter'] = '';
    foreach($all_keywords_filter as $f) {
        if($_POST['remove_keyword'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['pages_keyword_filter'] .= $f.',';
    }
    header( "HX-Trigger: update_pages_list");
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    exit;
}

if(isset($_POST['filter_type'])) {

    $sent_type_filter = clean_filename($_POST['filter_type']);

    if(str_contains($_SESSION['checked_page_type_string'],"$sent_type_filter")) {
        $type_filter = explode(" ", $_SESSION['checked_page_type_string']);
        if(($key = array_search($sent_type_filter, $type_filter)) !== false) {
            unset($type_filter[$key]);
        }
        $_SESSION['checked_page_type_string'] = implode(" ", $type_filter);
    } else {
        $_SESSION['checked_page_type_string'] = $_SESSION['checked_page_type_string'] . ' ' . $sent_type_filter;
    }

    header( "HX-Trigger: update_pages_list");
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    exit;
}


if(isset($_POST['sorting_single_pages_asc'])) {

    if($_POST['sorting_single_pages'] == 'linkname') {
        $_SESSION['sorting_single_pages'] = 'page_linkname';
    } else if($_POST['sorting_single_pages'] == 'priority') {
        $_SESSION['sorting_single_pages'] = 'page_priority';
    } else {
        $_SESSION['sorting_single_pages'] = 'page_lastedit';
    }

    $_SESSION['sorting_single_pages_dir'] = 'ASC';
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    header( "HX-Trigger: update_pages_list");
    exit;
}

if(isset($_POST['sorting_single_pages_desc'])) {
    if($_POST['sorting_single_pages'] == 'linkname') {
        $_SESSION['sorting_single_pages'] = 'page_linkname';
    } else if($_POST['sorting_single_pages'] == 'priority') {
        $_SESSION['sorting_single_pages'] = 'page_priority';
    } else {
        $_SESSION['sorting_single_pages'] = 'page_lastedit';
    }
    $_SESSION['sorting_single_pages_dir'] = 'DESC';
    $_SESSION['pagination_get_pages'] = 0; // reset pagination
    header( "HX-Trigger: update_pages_list");
    exit;
}


// delete pages by id
// delete from se_pages se_pages_cache and the assigned comments
if(isset($_POST['delete_page'])) {
    echo 'we delete ...';
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

    if($del_page->rowCount() > 0) {
        $success_message = '{OKAY} '. $lang['msg_success_page_deleted'];
        record_log($_SESSION['user_nick'],"deleted page id: $delete_id","10");
        generate_xml_sitemap();
        show_toast($success_message,'success');
    }

}

// save or update pages
if(isset($_POST['save_page'])) {


    // update mode
    if(is_numeric($_POST['save_page'])) {
        $page_id = (int) $_POST['save_page'];
        se_update_page($_POST,$page_id);
    }

    // new page
    if($_POST['save_page'] == 'new') {
        $new_page_id = se_save_page($_POST);
        se_snapshot_page($new_page_id);
    }

    // cache files
    mods_check_in();
    cache_url_paths();

    // run hooks
    if (isset($_POST['send_hook'])) {
        if (is_array($_POST['send_hook'])) {
            se_run_hooks($_POST['send_hook'],$_POST);
        }
    }

    // delete the smarty cache for this page
    se_delete_smarty_cache(md5($_POST['page_permalink']));

    show_toast($lang['msg_success_db_changed'],'success');
    header( "HX-Trigger: updated_pages");
}