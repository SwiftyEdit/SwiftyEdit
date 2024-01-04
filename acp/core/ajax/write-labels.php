<?php

require '_include.php';

foreach($_POST as $key => $val) {
    if(is_string($val)) {
        $$key = @htmlspecialchars($val, ENT_QUOTES);
    }
}

if (isset($_POST['update_label'])) {

    if($label_custom_id == '') {
        $label_custom_id = clean_filename($label_title);
    }

    $data = $db_content->update("se_labels", [
        "label_custom_id" => $label_custom_id,
        "label_color" => $label_color,
        "label_title" => $label_title,
        "label_description" => $label_description
    ], [
        "label_id" => $label_id
    ]);

    show_toast($lang['db_changed'],'success');
    record_log($_SESSION['user_nick'],"update label","1");
}


if(isset($_POST['delete_label'])) {

    $label_id = (int) $_POST['label_id'];

    $data = $db_content->delete("se_labels", [
        "label_id" => $label_id
    ]);
    show_toast($lang['db_changed'],'success');
    record_log($_SESSION['user_nick'],"deleted label","5");
}