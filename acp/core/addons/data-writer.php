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


if(isset($_POST['get_addon_info_from_url'])) {

    $url = trim($_POST['get_addon_info_from_url']);

    // Only allow HTTPS
    if(!str_starts_with($url, 'https://')) {
        echo 'Error: Only HTTPS URLs are allowed.';
        return;
    }

    // Automatically append info.json if not present
    if(!str_ends_with($url, '.json')) {
        $url = rtrim($url, '/').'/info.json';
    }

    // Load info.json
    $json = @file_get_contents($url);

    if($json === false) {
        echo 'Error: Could not load URL.';
        return;
    }

    // Parse JSON
    $data = json_decode($json, true);

    if(!$data || !isset($data['addon']) || !isset($data['versions'])) {
        echo 'Error: Invalid plugin info.json';
        return;
    }

    // Determine addon type
    $addon_type = $data['addon']['type'] ?? null;

    if($addon_type === 'plugin') {

        // Load SwiftyEdit build number
        $se_version = json_decode(file_get_contents(SE_ROOT.'version.json'), true);
        $se_build = $se_version['build'];

        // Find the most recent compatible version
        $compatible_version = null;

        foreach($data['versions'] as $v) {
            if($se_build >= $v['requires_build']) {
                $compatible_version = $v;
                break;
            }
        }

        if($compatible_version === null) {
            echo 'Error: No compatible version found for your SwiftyEdit build ('.$se_build.').';
            return;
        }

        // Determine plugin ID – from info.json or derive from URL
        $plugin_id = $data['addon']['id'] ?? basename(dirname($url));

        if(empty($plugin_id)) {
            echo 'Error: Could not determine plugin ID.';
            return;
        }

        // Install plugin
        $result = se_install_plugin($plugin_id, $compatible_version['download_url']);
        echo $result['message'];

    } elseif($addon_type === 'theme') {

        // Theme logic follows here

    } else {
        echo 'Error: Unknown addon type.';
        return;
    }
}

// Handle update request
if(isset($_POST['update_addon_from_url'])) {

    $plugin_id = trim($_POST['plugin_id']);
    $download_url = trim($_POST['download_url']);

    if(empty($plugin_id) || empty($download_url)) {
        echo 'Error: Missing plugin ID or download URL.';
        return;
    }

    // Only allow HTTPS
    if(!str_starts_with($download_url, 'https://')) {
        echo 'Error: Only HTTPS URLs are allowed.';
        return;
    }

    // Update plugin
    $result = se_install_plugin($plugin_id, $download_url);
    echo $result['message'];
}

if(isset($_POST['delete_plugin'])) {

    se_delete_addon($_POST['delete_plugin'],'plugin');
    header( "HX-Trigger: update_plugins_list");
}