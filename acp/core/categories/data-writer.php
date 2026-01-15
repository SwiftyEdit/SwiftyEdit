<?php

/**
 * global variables
 * @var array $lang
 * @var object $db_content
 * @var array $se_settings
 * @var array $lang_codes
 * @var string $hidden_csrf_token
 */


if(isset($_POST['delete'])) {
    $delete_id = (int) $_POST['delete'];
    $data = $db_content->delete("se_categories", [
        "cat_id" => $delete_id
    ]);
    if($data->rowCount() > 0) {
        record_log($_SESSION['user_nick'],"delete category id: $delete_id","8");
        header( "HX-Trigger: updated_categories");
        exit;
    }
}

if (isset($_POST['save_category'])) {

    if($_POST['cat_name_clean'] == '') {
        $cat_name_clean = clean_filename($_POST['cat_name']);
    } else {
        $cat_name_clean = clean_filename($_POST['cat_name_clean']);
    }
    $cat_name = sanitizeUserInputs($_POST['cat_name']);
    $cat_lang = sanitizeUserInputs($_POST['cat_lang']);
    $cat_thumbnail = sanitizeUserInputs($_POST['cat_thumbnail']);
    $cat_description = sanitizeUserInputs($_POST['cat_description']);
    $cat_sort = (int) $_POST['cat_sort'];

    if($_POST['cat_hash'] != '') {
        $cat_hash = $_POST['cat_hash'];
    } else {
        $cat_hash = md5(time());
    }


    $insert_data = [
        "cat_name" =>  $cat_name,
        "cat_name_clean" =>  $cat_name_clean,
        "cat_hash" =>  $cat_hash,
        "cat_lang" =>  $cat_lang,
        "cat_sort" => $cat_sort,
        "cat_description" => $cat_description,
        "cat_thumbnail" => $cat_thumbnail
    ];

    // create ne category
    if($_POST['save_category'] == 'new') {
        $data = $db_content->insert("se_categories", $insert_data);
        $new_id = $db_content->id();
    }

    // updated category
    if(is_numeric($_POST['save_category'])) {
        $cat_id = (int) $_POST['save_category'];
        $data = $db_content->update("se_categories", $insert_data,[
            "cat_id" => $cat_id
        ]);
    }

    show_toast($lang['msg_success_db_changed'],'success');
    header( "HX-Trigger: updated_categories");
}