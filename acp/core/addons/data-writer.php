<?php

/**
 * global variables
 * @var array $lang
 * @var object $db_content
 * @var bool $se_upload_addons from config.php
 */

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
        // editor plugins: publish their browser assets to /assets/editors
        se_publish_editor_assets($plugin_base);
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
    // editor plugins: remove their published browser assets again
    se_unpublish_editor_assets($plugin_base);
    mods_check_in();
    header( "HX-Trigger: update_plugins_list");
}

if(isset($_POST['save_theme_options'])) {
    se_write_theme_options($_POST);
    show_toast($lang['msg_success_db_changed'],'success');
}


// Step 1 – Load and display plugin info
if(isset($_POST['get_addon_info_from_url'])) {

    // Reachable directly via /admin-xhr/addons/write/ regardless of whether
    // the Install/Catalog buttons are shown - the "can upload sensitive
    // files" right gates install/uninstall of plugins and themes.
    if(!se_hasPermission('drm_acp_sensitive_files')) {
        echo 'Error: '.$lang['rm_no_access'];
        return;
    }

    if(!$se_upload_addons) {
        echo 'Error: Plugin installation via URL is disabled.';
        return;
    }

    $url = trim($_POST['get_addon_info_from_url']);

    // Response target for the confirm/install step below. Defaults to
    // install.php's own single #get-addon-response div (unchanged, existing
    // behaviour) - the catalog browser instead targets its shared install
    // modal (#catalogInstallModalBody, see catalog.php), so swapping the
    // confirm step in doesn't grow a card and desync its grid row's height
    // against its siblings.
    $target_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['target_id'] ?? '') ?: 'get-addon-response';
    $is_modal  = $target_id === 'catalogInstallModalBody';

    // Only allow HTTPS
    if(!str_starts_with($url, 'https://')) {
        echo 'Error: Only HTTPS URLs are allowed.';
        return;
    }

    // Automatically append info.json if not present
    if(!str_ends_with($url, '.json')) {
        $url = rtrim($url, '/').'/info.json';
    }

    $info = se_load_addon_info($url);

    if(!$info['success']) {
        echo 'Error: '.$info['message'];
        return;
    }

    // Editor plugins (docs/v2/en/09-02-plugins.md: "addon.type must be editor")
    // install exactly like a regular plugin - same zip, same SE_PLUGINS
    // extraction. The editor-specific wiring (se_publish_editor_assets())
    // only happens later, on activation, so no special-casing is needed here.
    // Themes share the same info.json shape (docs/v2/en/09-01-00-themes.md)
    // and confirm table - only the actual ZIP target differs, which Step 2
    // below branches on.
    if(in_array($info['addon_type'], ['plugin', 'editor', 'theme'], true)) {

        $compatible_version = $info['compatible_version'];

        // Same confirm table for plugins, editors and themes
        $confirm_table = '<table class="table">'
            .'<tr><th>Name</th><td>'.$info['addon']['name'].'</td></tr>'
            .'<tr><th>Author</th><td>'.$info['addon']['author'].'</td></tr>'
            .'<tr><th>Description</th><td>'.$info['addon']['description'].'</td></tr>'
            .'<tr><th>Version</th><td>'.$compatible_version['version'].' (Build '.$compatible_version['build'].')</td></tr>'
            .'<tr><th>Requires SwiftyEdit</th><td>>= '.$compatible_version['requires_build'].'</td></tr>'
            .'</table>';

        $vals = json_encode([
            'csrf_token' => $_SESSION['token'],
            'install_addon_from_url' => 1,
            'addon_url' => $url,
            'target_id' => $target_id
        ]);

        $install_btn = '<button class="btn btn-primary"
                hx-post="/admin-xhr/addons/write/"
                hx-vals=\''.$vals.'\'
                hx-target="#'.$target_id.'">
                Install</button>';

        if($is_modal) {
            // Swapped into the modal-content div directly (see catalog.php's
            // #catalogInstallModal shell) - the standard modal-header/-body/
            // -footer children reproduce the same chrome that shell would
            // otherwise ship empty.
            echo '<div class="modal-header">';
            echo '<h5 class="modal-title">'.$info['addon']['name'].'</h5>';
            echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
            echo '</div>';
            echo '<div class="modal-body">'.$confirm_table.'</div>';
            echo '<div class="modal-footer">';
            echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'.$lang['close'].'</button>';
            echo $install_btn;
            echo '</div>';
        } else {
            echo '<div class="card p-3 mt-3">'.$confirm_table.$install_btn.'</div>';
        }

    } else {
        echo 'Error: Unknown addon type.';
        return;
    }
}

// Step 2 – Install plugin
if(isset($_POST['install_addon_from_url'])) {

    if(!se_hasPermission('drm_acp_sensitive_files')) {
        echo 'Error: '.$lang['rm_no_access'];
        return;
    }

    if(!$se_upload_addons) {
        echo 'Error: Plugin installation via URL is disabled.';
        return;
    }

    $url = trim($_POST['addon_url']);
    $target_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['target_id'] ?? '') ?: 'get-addon-response';
    $is_modal  = $target_id === 'catalogInstallModalBody';

    // Only allow HTTPS
    if(!str_starts_with($url, 'https://')) {
        echo 'Error: Only HTTPS URLs are allowed.';
        return;
    }

    $info = se_load_addon_info($url);

    if(!$info['success']) {
        echo 'Error: '.$info['message'];
        return;
    }

    if(in_array($info['addon_type'], ['plugin', 'editor'], true)) {
        $result = se_install_plugin($info['plugin_id'], $info['compatible_version']['download_url']);
    } elseif($info['addon_type'] === 'theme') {
        $result = se_install_theme($info['plugin_id'], $info['compatible_version']['download_url']);
    } else {
        echo 'Error: Unknown addon type.';
        return;
    }

    if($is_modal) {
        // Refresh the catalog grid on close so the just-installed entry
        // switches from its Install button to the "Installed" badge
        // (list_catalog in data-reader.php listens for this same event).
        echo '<div class="modal-body">'.$result['message'].'</div>';
        echo '<div class="modal-footer">';
        echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="htmx.trigger(\'body\', \'refresh_catalog\')">'.$lang['close'].'</button>';
        echo '</div>';
    } else {
        echo $result['message'];
    }
}

// Handle update request
if(isset($_POST['update_addon_from_url'])) {

    if(!se_hasPermission('drm_acp_sensitive_files')) {
        echo 'Error: '.$lang['rm_no_access'];
        return;
    }

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

    if(!se_hasPermission('drm_acp_sensitive_files')) {
        return;
    }

    se_delete_addon($_POST['delete_plugin'],'plugin');
    header( "HX-Trigger: update_plugins_list");
}