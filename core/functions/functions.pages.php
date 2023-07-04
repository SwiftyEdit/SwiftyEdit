<?php

/**
 * write and update pages
 * do snapshots from pages
 */

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
        show_toast($lang['msg_page_saved'],'success');
    } else {
        show_toast($lang['msg_page_saved_error'],'danger');
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
        show_toast($lang['msg_page_updated'],'success');
    } else {
        show_toast($lang['msg_page_saved_error'],'danger');
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