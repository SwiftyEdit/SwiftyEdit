<?php


if(isset($_POST['delete'])) {
    echo '<hr>DELETE<hr>';
    print_r($_POST);
    exit;
}

if (isset($_POST['save_category'])) {

    $cat_name_clean = clean_filename($_POST['cat_name']);
    $cat_name = sanitizeUserInputs($_POST['cat_name']);
    $cat_lang = sanitizeUserInputs($_POST['cat_lang']);
    $cat_thumbnail = sanitizeUserInputs($_POST['cat_thumbnail']);
    $cat_description = sanitizeUserInputs($_POST['cat_description']);
    $cat_sort = (int) $_POST['cat_sort'];
    $cat_hash = md5(time());

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