<?php

// save the default template
if(isset($_POST['save_default_layout'])) {

    // template
    $select_template = explode("<|-|>", $_POST['select_template']);
    $prefs_template = $select_template[0];
    $prefs_template_layout = $select_template[1];

    $select_template_sytlesheet = basename($_POST['select_template_sytlesheet']);

    $data = [
        "prefs_template" =>  "$prefs_template",
        "prefs_template_layout" =>  "$prefs_template_layout",
        "prefs_template_stylesheet" =>  "$select_template_sytlesheet"
    ];

    se_write_option($data,'se');
    record_log($_SESSION['user_nick'],"edit system design <b>$prefs_template</b>","6");
    se_delete_smarty_cache('all');
    show_toast($lang['msg_data_updated'],'success');
    header( "HX-Trigger: update_themes_list");

}