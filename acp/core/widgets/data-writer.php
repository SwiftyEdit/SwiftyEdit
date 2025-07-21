<?php

if(isset($_POST['set_global_filter'])) {

    if(isset($_POST['set_label'])) {
        $set_labels = [];
        foreach($_POST['set_label'] as $set_label) {
            $set_labels[] = (int) $set_label;
        }
        $_SESSION['global_filter_label'] = json_encode($set_labels);
    } else {
        $_SESSION['global_filter_label'] = '';
    }

    if(isset($_POST['set_lang'])) {
        $set_langs = [];
        foreach($_POST['set_lang'] as $set_lang) {
            $set_langs[] = se_sanitize_lang_input($set_lang);
        }
        $_SESSION['global_filter_languages'] = json_encode($set_langs);
    } else {
        $_SESSION['global_filter_languages'] = '';
    }

    if(isset($_POST['set_status'])) {
        $set_stati = [];
        foreach($_POST['set_status'] as $set_status) {
            $set_stati[] = (int) $set_status;
        }
        $_SESSION['global_filter_status'] = json_encode($set_stati);
    } else {
        $_SESSION['global_filter_status'] = '';
    }

    //echo $lang['msg_info_filter_updated'];
    header( "HX-Trigger: updated_global_filter");
    exit;
}

/**
 * delete smarty cache
 */

if(isset($_POST['delete_smarty_cache'])) {
    se_delete_smarty_cache('all');
    header( "HX-Trigger: deleted_cache");
    exit;
}

