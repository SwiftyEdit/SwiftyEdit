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

    $sql = "SELECT * FROM se_pages $sql_filter $order";
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

    if($cnt_changes->rowCount() > 0) {
        $page_title = $sanitized_data['page_title'];
        record_log("$_SESSION[user_nick]","new Page <i>$page_title</i>","5");
        generate_xml_sitemap();
        show_toast($lang['msg_success_page_saved'],'success');
    } else {
        show_toast($lang['msg_error_page_saved'],'danger');
    }

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

    if($cnt_changes->rowCount() > 0) {
        $page_title = $sanitized_data['page_title'];
        record_log("$_SESSION[user_nick]","page update &raquo;$page_title&laquo;","5");
        generate_xml_sitemap();
        show_toast($lang['msg_success_page_saved'],'success');
    } else {
        show_toast($lang['msg_error_page_saved'],'danger');
    }

}

/**
 * @param array $data $_POST data
 * @return void
 */
function se_save_preview_page($data) {
    global $db_content, $custom_fields, $lang;

    $sanitized_data = se_sanitize_page_inputs($data);
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
        $keys_string .= $keys.',';
    }
    $keys_array = explode(",",$keys_string);
    $keys_array = array_filter( $keys_array );
    $count_keywords = array_count_values($keys_array);

    return $count_keywords;
}