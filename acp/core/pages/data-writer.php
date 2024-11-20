<?php

//print_r($_POST);

if(isset($_POST['pages_text_filter'])) {

    $_SESSION['pages_text_filter'] = $_SESSION['pages_text_filter'] . ' ' . clean_filename($_POST['pages_text_filter']);

    header( "HX-Trigger: update_pages_list");
}

/* remove keyword from filter list */
if(isset($_POST['rmkey'])) {
    print_r($_POST);
    $all_filter = explode(" ", $_SESSION['pages_text_filter']);
    $_SESSION['pages_text_filter'] = '';
    foreach($all_filter as $f) {
        if($_POST['rmkey'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['pages_text_filter'] .= "$f ";
    }
    header( "HX-Trigger: update_pages_list");
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
    header( "HX-Trigger: update_pages_list");
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
    header( "HX-Trigger: update_pages_list");
}

//print_r($_POST);

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

    $page_sort = sanitizeUserInputs($_POST['page_sort']);
    $page_title = sanitizeUserInputs($_POST['page_title']);
    $page_meta_description = sanitizeUserInputs($_POST['page_meta_description']);
    $page_meta_keywords = sanitizeUserInputs($_POST['page_meta_keywords']);
    $page_status = sanitizeUserInputs($_POST['page_status']);
    $page_lastedit = time();
    $page_lastedit_from = $_SESSION['user_nick'];
    $page_language = sanitizeUserInputs($_POST['page_language']);
    $page_content = $_POST['page_content'];
    $page_linkname = sanitizeUserInputs($_POST['page_linkname']);



    $insert_data = [
        "page_sort" =>  $page_sort,
        "page_language" =>  $page_language,
        "page_linkname" =>  $page_linkname,
        "page_meta_keywords" =>  $page_meta_keywords,
        "page_meta_description" =>  $page_meta_description,
        "page_title" =>  $page_title,
        "page_content" =>  $page_content,
        "page_status" =>  $page_status,
        "page_lastedit" =>  $page_lastedit,
        "page_lastedit_from" =>  $page_lastedit_from
    ];

    // create new page
    if($_POST['save_page'] == 'new') {
        $data = $db_content->insert("se_pages", $insert_data);

        $new_id = $db_content->id();
    }

    // updated category
    if(is_numeric($_POST['save_page'])) {
        $page_id = (int) $_POST['save_page'];
        $data = $db_content->update("se_pages", $insert_data,[
            "page_id" => $page_id
        ]);
    }

    show_toast($lang['msg_success_db_changed'],'success');
    header( "HX-Trigger: updated_pages");

}