<?php

if(isset($_POST['set_global_filter'])) {

    if(isset($_POST['set_label'])) {
        $_SESSION['global_filter_label'] = json_encode($_POST['set_label']);
    } else {
        $_SESSION['global_filter_label'] = '';
    }

    if(isset($_POST['set_lang'])) {
        $_SESSION['global_filter_languages'] = json_encode($_POST['set_lang']);
    } else {
        $_SESSION['global_filter_languages'] = '';
    }

    if(isset($_POST['set_status'])) {
        $_SESSION['global_filter_status'] = json_encode($_POST['set_status']);
    } else {
        $_SESSION['global_filter_status'] = '';
    }

    echo $lang['msg_info_filter_updated'];
    header( "HX-Trigger: updated_global_filter");
}

/**
 * delete smarty cache
 */

if(isset($_POST['delete_smarty_cache'])) {
    se_delete_smarty_cache('all');
    show_toast("Deleted Smarty Cache",'success');
    header( "HX-Trigger: deleted_cache");
}