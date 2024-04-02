<?php

require '_include.php';

foreach($_POST as $key => $val) {
    if(is_string($val)) {
        $$key = @htmlspecialchars($val, ENT_QUOTES);
    }
}

if((isset($_POST['mode'])) && $_POST['mode'] == 'new') {

    $cat_name_clean = clean_filename($cat_name);
    $cat_hash = md5(time());

    $data = $db_content->insert("se_categories", [
        "cat_name" =>  $cat_name,
        "cat_lang" =>  $cat_lang,
        "cat_name_clean" =>  $cat_name_clean,
        "cat_sort" =>  $cat_sort,
        "cat_hash" =>  $cat_hash,
        "cat_description" =>  $cat_description,
        "cat_thumbnail" =>  $cat_thumbnail
    ]);

    $new_id = $db_content->id();

    if($new_id > 0) {
        echo '<div class="alert alert-success">'.$lang['msg_success_db_changed'].'</div>';
    }
}

if((isset($_POST['mode'])) && $_POST['mode'] == 'update') {

    $cat_id = (int) $_POST['cat_id'];

    $cat_name_clean = clean_filename($cat_name);

    $data = $db_content->update("se_categories", [
        "cat_name" => $cat_name,
        "cat_lang" => $cat_lang,
        "cat_name_clean" => $cat_name_clean,
        "cat_sort" => $cat_sort,
        "cat_description" => $cat_description,
        "cat_thumbnail" => $cat_thumbnail
    ], [
        "cat_id" => $cat_id
    ]);

    $show_form = true;

    if($data->rowCount() > 0) {
        echo '<div class="alert alert-success">'.$lang['msg_success_db_changed'].'</div>';
    }
}

