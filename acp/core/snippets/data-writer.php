<?php

/**
 * SwiftyEdit /admin-xhr/snippets/write/
 * list filters (search, keywords, pagination), saving and deleting snippets
 *
 * global variables
 * @var array $lang
 * @var array $lang_codes
 * @var string $languagePack
 * @var object $db_content
 */

// search snippet
if(isset($_POST['snippets_text_filter'])) {

    $add_text_filter = sanitizeUserInputs($_POST['snippets_text_filter']);

    if(($_SESSION['snippets_text_filter'] ?? '') == '') {
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

    // the search strings are stored sanitized (see above), the button sends the plain string
    $remove_keyword = sanitizeUserInputs($_POST['rmkey']);
    $all_filter = explode(" ", $_SESSION['snippets_text_filter'] ?? '');
    $_SESSION['snippets_text_filter'] = '';
    foreach($all_filter as $f) {
        if($remove_keyword == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['snippets_text_filter'] .= "$f ";
    }
    header( "HX-Trigger: update_snippet_list");
}

// add keyword
if(isset($_POST['add_keyword'])) {
    $_SESSION['snippets_keyword_filter'] = ($_SESSION['snippets_keyword_filter'] ?? '') . ' ' . sanitizeUserInputs($_POST['add_keyword']);
    header( "HX-Trigger: update_snippet_list");
}

// remove keyword
if(isset($_POST['remove_keyword'])) {

    // the keywords are stored sanitized (see above), the button sends the plain keyword
    $remove_keyword = sanitizeUserInputs($_POST['remove_keyword']);
    $all_keywords_filter = explode(" ", $_SESSION['snippets_keyword_filter'] ?? '');
    $_SESSION['snippets_keyword_filter'] = '';
    foreach($all_keywords_filter as $f) {
        if($remove_keyword == "$f") { continue; }
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
    $deleted_snippet = $db_content->get("se_snippets", ["snippet_name", "snippet_title"], ["snippet_id" => $delete_id]);
    $cnt_changes=$db_content->delete("se_snippets",[
        "snippet_id" => $delete_id
    ]);

    if(($cnt_changes->rowCount()) > 0) {
        show_toast($lang['msg_info_data_deleted'],'success');
        se_log_content('delete', 'snippet', $delete_id, (string) ($deleted_snippet['snippet_title'] ?? ''), (string) ($deleted_snippet['snippet_name'] ?? ''));
        header( "HX-Redirect: /admin/snippets/");
    }
}

// save or update snippets
if(isset($_POST['save_snippet'])) {

    $snippet_name = clean_filename($_POST['snippet_name']);
    $snippet_title = sanitizeUserInputs($_POST['snippet_title']);
    $snippet_keywords = sanitizeUserInputs($_POST['snippet_keywords']);
    // whitelist - the language becomes part of the cache file name below
    $snippet_lang = (string) ($_POST['snippet_lang'] ?? '');
    if(!in_array($snippet_lang, $lang_codes, true)) {
        $snippet_lang = $languagePack;
    }
    $snippet_label = sanitizeUserInputs($_POST['snippet_label']);
    $snippet_classes = sanitizeUserInputs($_POST['snippet_classes']);
    $snippet_permalink = sanitizeUserInputs($_POST['snippet_permalink']);
    $snippet_permalink_title = sanitizeUserInputs($_POST['snippet_permalink_title']);
    $snippet_permalink_name = sanitizeUserInputs($_POST['snippet_permalink_name']);
    $snippet_permalink_classes = sanitizeUserInputs($_POST['snippet_permalink_classes']);


    $snippet_priority = (int) $_POST['snippet_priority'];

    // value of the select: theme<|-|>template file
    $snippet_themes = explode('<|-|>', (string) ($_POST['select_template'] ?? ''));
    $snippet_theme = $snippet_themes[0];
    $snippet_template = $snippet_themes[1] ?? '';


    if($snippet_name == '') {
        $snippet_name = date("Y_m_d_h_i",time());
    }

    $snippet_thumbnail = '';
    if(is_array($_POST['picker_0'] ?? null)) {
        if(count($_POST['picker_0']) > 1) {
            $snippet_thumbnail = implode("<->", array_unique($_POST['picker_0']));
        } else {
            $st = $_POST['picker_0'];
            $snippet_thumbnail = $st[0].'<->';
        }
    }

    if (is_array($_POST['snippet_labels'] ?? null)) {
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

    // check for cache directory
    if(!is_dir(SE_CONTENT . '/cache/snippets')) {
        mkdir(SE_CONTENT . '/cache/snippets', SE_DIR_PERMISSIONS, true);
    }

    // cache snippet as JSON in /cache/snippets/$snippet_name+$lang
    $file = SE_CONTENT . '/cache/snippets/'.$snippet_name.'_'.$snippet_lang.'.json';
    file_put_contents($file, json_encode($insert_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // create new snippet
    if($_POST['save_snippet'] == 'new') {
        $data = $db_content->insert("se_snippets", $insert_data);
        $new_id = $db_content->id();
        se_log_content('create', 'snippet', (int) $new_id, $snippet_title, $snippet_name);
        show_toast($lang['msg_success_new_record'],'success');
        header( 'HX-REDIRECT: /admin/snippets/edit/'.$new_id.'/');
    }

    // or update a snippet
    if(is_numeric($_POST['save_snippet'])) {
        $snippet_id = (int) $_POST['save_snippet'];
        $data = $db_content->update("se_snippets", $insert_data,[
            "snippet_id" => $snippet_id
        ]);
        se_log_content('update', 'snippet', $snippet_id, $snippet_title, $snippet_name);
        show_toast($lang['msg_success_db_changed'],'success');
    }
}