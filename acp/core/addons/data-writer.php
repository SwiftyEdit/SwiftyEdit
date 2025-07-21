<?php

// give the plugins the possibility to write via xhr
$path = explode('/', $_REQUEST['query']);
$plugin = basename($path[2]);
$plugin_base = '/admin/addons/plugin/' . $plugin . '/';
$plugin_root = SE_ROOT.'plugins/'.$plugin.'/';
$plugin_writer_file = SE_ROOT.'plugins/'.$plugin.'/backend/writer.php';
if(is_file("$plugin_writer_file")) {
    include_once "$plugin_writer_file";
    exit;
}

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
    show_toast($lang['msg_success_db_changed'],'success');
    header( "HX-Trigger: update_themes_list");
}

// activate plugin
if(isset($_POST['activate_addon'])) {
    $plugin_base = basename($_POST['activate_addon']);
    $info_file = SE_ROOT.'plugins/'.$plugin_base.'/info.json';
    if(is_file($info_file)) {
        $info = json_decode(file_get_contents($info_file), true);
        $db_content->insert("se_addons", [
            "addon_type" => "plugin",
            "addon_dir" => $plugin_base,
            "addon_name" => $info['addon']['name'],
            "addon_version" => $info['addon']['version']
        ]);
        mods_check_in();
    }
    header( "HX-Trigger: update_plugins_list");
}

// deactivate plugin
if(isset($_POST['deactivate_addon'])) {
    $plugin_base = basename($_POST['deactivate_addon']);
    $db_content->delete("se_addons", [
        "AND" => [
            "addon_dir" => $plugin_base
        ]
    ]);
    mods_check_in();
    header( "HX-Trigger: update_plugins_list");
}

if(isset($_POST['save_theme_options'])) {
    se_write_theme_options($_POST);
    show_toast($lang['msg_success_db_changed'],'success');
}