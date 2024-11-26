<?php

if(isset($_POST['snippets_text_filter'])) {

    $add_text_filter = sanitizeUserInputs($_POST['snippets_text_filter']);

    if($_SESSION['snippets_text_filter'] == '') {
        $_SESSION['snippets_text_filter'] = $add_text_filter;
    } else {
        if(!str_contains($_SESSION['snippets_text_filter'], $add_text_filter)) {
            $_SESSION['snippets_text_filter'] .= ' '. $add_text_filter;
        }

    }

    header( "HX-Trigger: update_snippet_list");
}

/* remove search string from filter list */
if(isset($_POST['rmkey'])) {

    $all_filter = explode(" ", $_SESSION['snippets_text_filter']);
    $_SESSION['snippets_text_filter'] = '';
    foreach($all_filter as $f) {
        if($_POST['rmkey'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['snippets_text_filter'] .= "$f ";
    }
    header( "HX-Trigger: update_snippet_list");
}

// add keyword
if(isset($_POST['add_keyword'])) {
    $_SESSION['snippets_keyword_filter'] = $_SESSION['snippets_keyword_filter'] . ' ' . sanitizeUserInputs($_POST['add_keyword']);
    header( "HX-Trigger: update_snippet_list");
}

// remove keyword
if(isset($_POST['remove_keyword'])) {

    $all_keywords_filter = explode(" ", $_SESSION['snippets_keyword_filter']);
    $_SESSION['snippets_keyword_filter'] = '';
    foreach($all_keywords_filter as $f) {
        if($_POST['remove_keyword'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['snippets_keyword_filter'] .= "$f ";
    }
    header( "HX-Trigger: update_snippet_list");
}


/**
 * pagination
 */

if(isset($_POST['pagination'])) {
    $_SESSION['pagination_snippets_page'] = (int) $_POST['pagination'];
    header( "HX-Trigger: update_snippet_list");
}


// save or update snippets
if(isset($_POST['save_snippet'])) {

    $snippet_name = clean_filename($_POST['snippet_name']);
    $snippet_title = sanitizeUserInputs($_POST['snippet_title']);
    $snippet_content = sanitizeUserInputs($_POST['snippet_content']);
    $snippet_keywords = sanitizeUserInputs($_POST['snippet_keywords']);
    $snippet_lang = sanitizeUserInputs($_POST['snippet_lang']);

    $insert_data = [
        "snippet_lastedit" =>  time(),
        "snippet_lastedit_from" =>  $_SESSION['user_nick'],
        "snippet_name" =>  $snippet_name,
        "snippet_title" =>  $snippet_title,
        "snippet_content" =>  $snippet_content,
        "snippet_keywords" =>  $snippet_keywords,
        "snippet_lang" =>  $snippet_lang
    ];

    // create new snippet
    if($_POST['save_snippet'] == 'new') {
        $data = $db_content->insert("se_snippets", $insert_data);
        $new_id = $db_content->id();
    }

    // or update a snippet
    if(is_numeric($_POST['save_snippet'])) {
        $snippet_id = (int) $_POST['save_snippet'];
        $data = $db_content->update("se_snippets", $insert_data,[
            "snippet_id" => $snippet_id
        ]);
    }

    show_toast($lang['msg_success_db_changed'],'success');

}