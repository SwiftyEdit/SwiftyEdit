<?php

require '_include.php';

foreach($_POST as $key => $val) {
    if(is_string($val)) {
        $$key = @htmlspecialchars($val, ENT_QUOTES);
    }
}


/**
 * delete group from se_filter
 * delete all entries where parent_id ist group's id
 * or delete value
 */

if((isset($_POST['action'])) && $_POST['action'] == 'delete') {

    if(isset($_POST['group_id'])) {
        $delete_id = (int) $_POST['group_id'];

        $delete_entries = $db_content->delete("se_filter", [
            "filter_parent_id" => $delete_id
        ]);
        $delete_entries_cnt = $delete_entries->rowCount();

        $delete_group = $db_content->delete("se_filter", [
            "filter_id" => $delete_id
        ]);

        $delete_group_cnt = $delete_group->rowCount();

        echo '<div class="alert alert-info">' . $lang['msg_entry_delete'] . ' (Group: ' . $delete_group_cnt . ' Entries: ' . $delete_entries_cnt . ')</div>';
        exit;
    }

    if(isset($_POST['value_id'])) {
        $delete_id = (int) $_POST['value_id'];
        $delete_value = $db_content->delete("se_filter", [
            "filter_id" => $delete_id
        ]);
        $delete_cnt = $delete_value->rowCount();
        if($delete_cnt > 0) {
            echo '<div class="alert alert-info">' . $lang['msg_entry_delete'] . '</div>';
        }
    }


}

if((isset($_POST['mode'])) && $_POST['mode'] == 'new_group') {

    $filter_type = 1;
    $filter_priority = (int) $_POST['filter_group_priority'];
    $filter_input_type = (int) $_POST['filter_input_type'];

    $data = $db_content->insert("se_filter", [
        "filter_title" =>  $filter_group_name,
        "filter_description" =>  $filter_group_description,
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

    $filter_cats = implode(",",$_POST['filter_cats']);

    $data = $db_content->update("se_filter", [
        "filter_title" =>  $filter_group_name,
        "filter_description" =>  $filter_group_description,
        "filter_lang" =>  $filter_group_lang,
        "filter_priority" =>  $filter_priority,
        "filter_input_type" =>  $filter_input_type,
        "filter_categories" =>  $filter_cats
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
        "filter_description" =>  $filter_description,
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
        "filter_description" =>  $filter_description,
        "filter_priority" =>  $filter_priority,
        "filter_parent_id" =>  $filter_parent_id
    ],[
        "filter_id" => (int) $_POST['value_id']
    ]);

    if($data->rowCount() > 0) {
        echo '<div class="alert alert-success">'.$lang['db_changed'].'</div>';
    }

}