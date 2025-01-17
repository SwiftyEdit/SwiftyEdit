<?php

// search snippet
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

// remove search string from filter list
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



// pagination
if(isset($_POST['pagination'])) {
    $_SESSION['pagination_snippets_page'] = (int) $_POST['pagination'];
    header( "HX-Trigger: update_snippet_list");
}

// snippets per page
if(isset($_POST['items_per_page'])) {
    $_SESSION['snippets_per_page'] = (int) $_POST['items_per_page'];
    $_SESSION['pagination_snippets_page'] = 0;
    header( "HX-Trigger: update_snippet_list");
}

// delete snippet
if(isset($_POST['delete_snippet'])) {
    $delete_id = (int) $_POST['delete_snippet'];
    $cnt_changes=$db_content->delete("se_snippets",[
        "snippet_id" => $delete_id
    ]);

    if(($cnt_changes->rowCount()) > 0) {
        show_toast($lang['msg_info_data_deleted'],'success');
        record_log($_SESSION['user_nick'],"deleted snippet id: $delete_snip_id","10");
        header( "HX-Redirect: /admin/snippets/");
        $modus = 'new';
    }
}

// save or update snippets
if(isset($_POST['save_snippet'])) {

    $snippet_name = clean_filename($_POST['snippet_name']);
    $snippet_title = sanitizeUserInputs($_POST['snippet_title']);
    $snippet_keywords = sanitizeUserInputs($_POST['snippet_keywords']);
    $snippet_lang = sanitizeUserInputs($_POST['snippet_lang']);
    $snippet_label = sanitizeUserInputs($_POST['snippet_label']);
    $snippet_classes = sanitizeUserInputs($_POST['snippet_classes']);
    $snippet_permalink = sanitizeUserInputs($_POST['snippet_permalink']);
    $snippet_permalink_title = sanitizeUserInputs($_POST['snippet_permalink_title']);
    $snippet_permalink_name = sanitizeUserInputs($_POST['snippet_permalink_name']);
    $snippet_permalink_classes = sanitizeUserInputs($_POST['snippet_permalink_classes']);


    $snippet_priority = (int) $_POST['snippet_priority'];

    $snippet_themes = explode('<|-|>', $_POST['select_template']);
    $snippet_theme = $snippet_themes[0];
    $snippet_template = $snippet_themes[1];


    if($snippet_name == '') {
        $snippet_name = date("Y_m_d_h_i",time());
    }

    $snippet_thumbnail = '';
    if(is_array($_POST['picker_0'])) {
        if(count($_POST['picker_0']) > 1) {
            $snippet_thumbnail = implode("<->", array_unique($_POST['picker_0']));
        } else {
            $st = $_POST['picker_0'];
            $snippet_thumbnail = $st[0].'<->';
        }
    }

    if (is_array($_POST['snippet_labels'])) {
        sort($_POST['snippet_labels']);
        $string_labels = implode(",", $_POST['snippet_labels']);
    } else {
        $string_labels = "";
    }


    $insert_data = [
        "snippet_lastedit" =>  time(),
        "snippet_lastedit_from" =>  $_SESSION['user_nick'],
        "snippet_type" =>  'snippet',
        "snippet_name" =>  $snippet_name,
        "snippet_title" =>  $snippet_title,
        "snippet_content" =>  $_POST['snippet_content'],
        "snippet_keywords" =>  $snippet_keywords,
        "snippet_priority" => $snippet_priority,
        "snippet_lang" =>  $snippet_lang,
        "snippet_template" => $snippet_template,
        "snippet_theme" => $snippet_theme,
        "snippet_images" => $snippet_thumbnail,
        "snippet_labels" => $string_labels,
        "snippet_label" => $snippet_label,
        "snippet_classes" => $snippet_classes,
        "snippet_permalink" => $snippet_permalink,
        "snippet_permalink_title" => $snippet_permalink_title,
        "snippet_permalink_name" => $snippet_permalink_name,
        "snippet_permalink_classes" => $snippet_permalink_classes
    ];

    // create new snippet
    if($_POST['save_snippet'] == 'new') {
        $data = $db_content->insert("se_snippets", $insert_data);
        $new_id = $db_content->id();
        show_toast($lang['msg_success_new_record'],'success');

        header( 'HX-REDIRECT: /admin/snippets/edit/'.$new_id.'/');
    }

    // or update a snippet
    if(is_numeric($_POST['save_snippet'])) {
        $snippet_id = (int) $_POST['save_snippet'];
        $data = $db_content->update("se_snippets", $insert_data,[
            "snippet_id" => $snippet_id
        ]);
        show_toast($lang['msg_success_db_changed'],'success');
    }
}