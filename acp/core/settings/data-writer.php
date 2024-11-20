<?php

/**
 * global variables
 * @var array $lang
 */

//error_reporting(E_ALL);

if (isset($_POST['update_events'])) {
    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');

    show_toast($lang['msg_success_db_changed'],'success');
}

if (isset($_POST['update_posts'])) {

    foreach($_POST as $key => $val) {
        $data[htmlentities($key)] = htmlentities($val);
    }
    se_write_option($data,'se');

    show_toast($lang['msg_success_db_changed'],'success');
}

if (isset($_POST['update_email'])) {
    //echo 'writing ....';
    //print_r($_POST);
    show_toast($lang['msg_success_db_changed'],'success');
}

if (isset($_POST['update_general'])) {
    //echo 'writing ....';
    //print_r($_POST);
    show_toast($lang['msg_success_db_changed'],'success');
}

if (isset($_POST['update_general_system'])) {
    //echo 'writing ....';
    //print_r($_POST);
    show_toast($lang['msg_success_db_changed'],'success');
}

if (isset($_POST['post_label'])) {

    $label_color = sanitizeUserInputs($_POST['label_color']);
    $label_title = sanitizeUserInputs($_POST['label_title']);
    $label_description = sanitizeUserInputs($_POST['label_description']);

    $label_custom_id = clean_filename($label_title);

    $data = $db_content->insert("se_labels", [
        "label_custom_id" => $label_custom_id,
        "label_color" => $label_color,
        "label_title" => $label_title,
        "label_description" => $label_description
    ]);

    show_toast($lang['msg_success_db_changed'],'success');
    record_log($_SESSION['user_nick'],"create new label","1");
    header( "HX-Trigger: updated_labels");
}

if(isset($_POST['update_label'])) {

    $label_color = sanitizeUserInputs($_POST['label_color']);
    $label_title = sanitizeUserInputs($_POST['label_title']);
    $label_description = sanitizeUserInputs($_POST['label_description']);

    $data = $db_content->update("se_labels", [
        "label_custom_id" => $label_custom_id,
        "label_color" => $label_color,
        "label_title" => $label_title,
        "label_description" => $label_description
    ],[
        "label_id" => (int) $_POST['label_id']
    ]);

    show_toast("Label updated successfully","success");
    header( "HX-Trigger: updated_labels");
}

if(isset($_POST['delete_label'])) {

    $label_id = (int) $_POST['label_id'];

    $data = $db_content->delete("se_labels", [
        "label_id" => $label_id
    ]);
    show_toast($lang['msg_success_db_changed'],'success');
    record_log($_SESSION['user_nick'],"deleted label","5");
}

