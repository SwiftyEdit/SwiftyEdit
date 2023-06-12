<?php

require '_include.php';

foreach($_POST as $key => $val) {
    if(is_string($val)) {
        $$key = @htmlspecialchars($val, ENT_QUOTES);
    }
}

if((isset($_POST['mode'])) && $_POST['mode'] == 'new_group') {

    $filter_type = 1;
    $filter_priority = (int) $_POST['filter_group_priority'];
    $filter_input_type = (int) $_POST['filter_input_type'];

    $data = $db_content->insert("se_filter", [
        "filter_title" =>  $filter_group_name,
        "filter_lang" =>  $filter_group_lang,
        "filter_priority" =>  $filter_priority,
        "filter_type" =>  $filter_type,
        "filter_input_type" =>  $filter_input_type
    ]);

    $new_id = $db_content->id();

    if($new_id > 0) {
        echo '<div class="alert alert-success">'.$lang['db_changed'].'</div>';
    }

}

if((isset($_POST['mode'])) && $_POST['mode'] == 'edit_group') {

    $filter_parent_id = (int) $_POST['filter_parent_id'];
    $filter_priority = (int) $_POST['filter_group_priority'];

    $data = $db_content->update("se_filter", [
        "filter_title" =>  $filter_group_name,
        "filter_lang" =>  $filter_group_lang,
        "filter_priority" =>  $filter_priority,
        "filter_input_type" =>  $filter_input_type
        ],[
            "filter_id" => (int) $_POST['group_id']
    ]);

    if($data->rowCount() > 0) {
        echo '<div class="alert alert-success">'.$lang['db_changed'].'</div>';
    }

}

if((isset($_POST['mode'])) && $_POST['mode'] == 'new_value') {

    $filter_priority = (int) $_POST['filter_priority'];
    $filter_parent_id = (int) $_POST['filter_parent_id'];

    $data = $db_content->insert("se_filter", [
        "filter_title" =>  $filter_name,
        "filter_priority" =>  $filter_priority,
        "filter_parent_id" =>  $filter_parent_id,
        "filter_type" =>  2
    ]);

    $new_id = $db_content->id();
    if($new_id > 0) {
        echo '<div class="alert alert-success">'.$lang['db_changed'].'</div>';
    }

}

if((isset($_POST['mode'])) && $_POST['mode'] == 'edit_value') {

    $filter_priority = (int) $_POST['filter_priority'];
    $filter_parent_id = (int) $_POST['filter_parent_id'];

    $data = $db_content->update("se_filter", [
        "filter_title" =>  $filter_name,
        "filter_priority" =>  $filter_priority,
        "filter_parent_id" =>  $filter_parent_id
    ],[
        "filter_id" => (int) $_POST['value_id']
    ]);

    if($data->rowCount() > 0) {
        echo '<div class="alert alert-success">'.$lang['db_changed'].'</div>';
    }

}