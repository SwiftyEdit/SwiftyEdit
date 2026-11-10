<?php

/**
 * SwiftyEdit /admin-xhr/addons/write/
 * layout of the frontend, theme options, activation of plugins,
 * installation, update and deletion of plugins and themes -
 * requests of activated plugins are handed over to their backend/writer.php
 *
 * global variables
 * @var array $lang
 * @var object $db_content
 * @var array $se_settings
 * @var object $twig
 * @var bool $se_upload_addons from config.php
 */

$writer_uri = '/admin-xhr/addons/write/';

// give the plugins the possibility to write via xhr
$path = explode('/', $_REQUEST['query'] ?? '');
// strict whitelist instead of basename() - basename() alone still lets a bare
// ".." through (no "/" for it to strip), which would point $plugin_root one
// level above plugins/; this also rules out reaching any plugin that hasn't
// been activated, see se_is_plugin_activated()
$plugin = preg_replace('/[^a-zA-Z0-9_-]/', '', $path[2] ?? '');

if ($plugin !== '' && se_is_plugin_activated($plugin)) {
    $plugin_base = '/admin/addons/plugin/' . $plugin . '/';
    $plugin_root = SE_ROOT.'plugins/'.$plugin.'/';
    $plugin_writer_file = SE_ROOT.'plugins/'.$plugin.'/backend/writer.php';
    if(is_file($plugin_writer_file)) {
        include_once $plugin_writer_file;
        exit;
    }
}

/**
 * directory name of an installed theme or plugin
 *
 * @param mixed $name posted name
 * @param string $root SE_THEMES or SE_PLUGINS
 * @return string the name, empty if there is no such directory
 */
$installed_addon = static function(mixed $name, string $root): string {
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $name);
    return ($name !== '' && is_dir($root.'/'.$name)) ? $name : '';
};

/**
 * response of the installation steps, see acp/templates/addons/install-response.twig
 */
$install_response = static function(array $data) use ($twig, $writer_uri): void {
    $target_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['target_id'] ?? '') ?: 'get-addon-response';
    echo $twig->render('addons/install-response.twig', $data + [
        // the catalog targets its shared install modal, the install page its response div
        'in_modal' => ($target_id === 'catalogInstallModalBody'),
        'target_id' => $target_id,
        'writer_uri' => $writer_uri
    ]);
};

// save the theme, its layout and stylesheet for the frontend
if(isset($_POST['save_default_layout'])) {

    $theme = $installed_addon($_POST['theme'] ?? '', SE_THEMES);
    $layout = basename((string) ($_POST['template_layout'] ?? ''));
    $stylesheet = basename((string) ($_POST['template_stylesheet'] ?? ''));

    if($theme === '' || $theme === 'administration' || !is_file(SE_THEMES.'/'.$theme.'/templates/'.$layout)) {
        show_toast($lang['msg_error_db_changed'], 'danger');
        exit;
    }
    // the stylesheet is optional, but has to be a color variant of the theme
    if($stylesheet !== '' && !is_file(SE_THEMES.'/'.$theme.'/dist/skins/'.$stylesheet)) {
        $stylesheet = '';
    }

    se_write_option([
        "prefs_template" => $theme,
        "prefs_template_layout" => $layout,
        "prefs_template_stylesheet" => $stylesheet
    ], 'se');
    record_log($_SESSION['user_nick'], "edit system design $theme", 6);
    se_delete_smarty_cache();
    show_toast($lang['msg_success_db_changed'], 'success');
    header("HX-Trigger: update_themes_list");
    exit;
}

// activate plugin
if(isset($_POST['activate_addon'])) {
    $plugin_dir = $installed_addon($_POST['activate_addon'], SE_PLUGINS);
    $info = ($plugin_dir !== '') ? json_decode((string) file_get_contents(SE_PLUGINS.'/'.$plugin_dir.'/info.json'), true) : null;
    // a double click must not activate the plugin twice
    if(is_array($info) && !se_is_plugin_activated($plugin_dir)) {
        $db_content->insert("se_addons", [
            "addon_type" => "plugin",
            "addon_dir" => $plugin_dir,
            "addon_name" => $info['addon']['name'] ?? $plugin_dir,
            "addon_version" => $info['addon']['version'] ?? ''
        ]);
        // editor plugins: publish their browser assets to /assets/editors
        se_publish_editor_assets($plugin_dir);
        mods_check_in();
    }
    header("HX-Trigger: update_plugins_list");
    exit;
}

// deactivate plugin
if(isset($_POST['deactivate_addon'])) {
    $plugin_dir = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $_POST['deactivate_addon']);
    $db_content->delete("se_addons", [
        "addon_type" => "plugin",
        "addon_dir" => $plugin_dir
    ]);
    // editor plugins: remove their published browser assets again
    se_unpublish_editor_assets($plugin_dir);
    mods_check_in();
    header("HX-Trigger: update_plugins_list");
    exit;
}

// options of a theme, only the options the theme defines in php/options.php
if(isset($_POST['save_theme_options'])) {
    $theme = $installed_addon($_POST['theme'] ?? '', SE_THEMES);
    $theme_options = se_get_theme_option_fields($theme);
    if($theme === '' || $theme_options === []) {
        exit;
    }

    $data = ['theme' => $theme];
    foreach($theme_options as $key => $option) {
        $value = $_POST['theme_'.$key] ?? '';
        // a select only accepts its own options
        if(($option['type'] ?? '') === 'select' && !array_key_exists($value, $option['options'] ?? [])) {
            $value = '';
        }
        $data['theme_'.$key] = sanitizeUserInputs((string) $value);
    }
    se_write_theme_options($data);
    se_delete_smarty_cache();
    show_toast($lang['msg_success_db_changed'], 'success');
    exit;
}

// the steps below install addon code - reachable directly regardless of whether the
// install and catalog buttons are shown, so they check the "can upload sensitive files"
// right and the switch for the addon upload themselves
if(isset($_POST['get_addon_info_from_url']) || isset($_POST['install_addon_from_url']) || isset($_POST['update_addon_from_url'])) {

    if(!se_hasPermission('drm_acp_sensitive_files')) {
        $install_response(['step' => 'error', 'message' => $lang['rm_no_access']]);
        exit;
    }
    if(!$se_upload_addons) {
        $install_response(['step' => 'error', 'message' => $lang['msg_info_upload_addons_deactivated']]);
        exit;
    }
}

// Step 1 – load and display the info of a plugin or theme
if(isset($_POST['get_addon_info_from_url'])) {

    $url = trim((string) $_POST['get_addon_info_from_url']);
    if(!str_starts_with($url, 'https://')) {
        $install_response(['step' => 'error', 'message' => $lang['addons_msg_https_only']]);
        exit;
    }

    // automatically append info.json if not present
    if(!str_ends_with($url, '.json')) {
        $url = rtrim($url, '/').'/info.json';
    }

    $info = se_load_addon_info($url);
    if(!$info['success']) {
        $install_response(['step' => 'error', 'message' => $info['message']]);
        exit;
    }

    // editor plugins install exactly like a regular plugin, the editor specific wiring
    // happens on activation - themes share the same info.json shape, only the ZIP target differs
    if(!in_array($info['addon_type'], ['plugin', 'editor', 'theme'], true)) {
        $install_response(['step' => 'error', 'message' => $lang['addons_msg_unknown_type']]);
        exit;
    }

    $install_response([
        'step' => 'confirm',
        'addon' => [
            'name' => $info['addon']['name'] ?? $info['plugin_id'],
            'author' => $info['addon']['author'] ?? '',
            'description' => $info['addon']['description'] ?? '',
            'version' => $info['compatible_version']['version'] ?? '',
            'build' => $info['compatible_version']['build'] ?? '',
            'requires_build' => $info['compatible_version']['requires_build'] ?? ''
        ],
        'addon_url' => $url
    ]);
    exit;
}

// Step 2 – install the plugin or theme
if(isset($_POST['install_addon_from_url'])) {

    $url = trim((string) ($_POST['addon_url'] ?? ''));
    if(!str_starts_with($url, 'https://')) {
        $install_response(['step' => 'error', 'message' => $lang['addons_msg_https_only']]);
        exit;
    }

    $info = se_load_addon_info($url);
    if(!$info['success']) {
        $install_response(['step' => 'error', 'message' => $info['message']]);
        exit;
    }

    if(in_array($info['addon_type'], ['plugin', 'editor'], true)) {
        $result = se_install_plugin($info['plugin_id'], $info['compatible_version']['download_url']);
    } elseif($info['addon_type'] === 'theme') {
        $result = se_install_theme($info['plugin_id'], $info['compatible_version']['download_url']);
    } else {
        $install_response(['step' => 'error', 'message' => $lang['addons_msg_unknown_type']]);
        exit;
    }

    $install_response(['step' => 'result', 'success' => $result['success'], 'message' => $result['message']]);
    exit;
}

// update a plugin or theme, the button of the update status (see update-status.twig)
if(isset($_POST['update_addon_from_url'])) {

    // strict whitelist - the id becomes the target directory of the ZIP
    $plugin_id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($_POST['plugin_id'] ?? ''));
    $download_url = trim((string) ($_POST['download_url'] ?? ''));

    if($plugin_id === '' || $download_url === '') {
        $install_response(['step' => 'error', 'message' => $lang['addons_msg_missing_update_data']]);
        exit;
    }
    if(!str_starts_with($download_url, 'https://')) {
        $install_response(['step' => 'error', 'message' => $lang['addons_msg_https_only']]);
        exit;
    }

    if(($_POST['addon_type'] ?? 'plugin') === 'theme') {
        $result = se_install_theme($plugin_id, $download_url);
        se_delete_smarty_cache();
    } else {
        $result = se_install_plugin($plugin_id, $download_url);
    }
    $install_response(['step' => 'result', 'success' => $result['success'], 'message' => $result['message']]);
    exit;
}

// delete a plugin - an activated one is deactivated first
if(isset($_POST['delete_plugin'])) {

    if(!se_hasPermission('drm_acp_sensitive_files')) {
        show_toast($lang['rm_no_access'], 'danger');
        exit;
    }

    $plugin_dir = $installed_addon($_POST['delete_plugin'], SE_PLUGINS);
    if($plugin_dir === '') {
        exit;
    }

    if(se_is_plugin_activated($plugin_dir)) {
        $db_content->delete("se_addons", [
            "addon_type" => "plugin",
            "addon_dir" => $plugin_dir
        ]);
        se_unpublish_editor_assets($plugin_dir);
    }
    se_delete_addon($plugin_dir, 'plugin');
    mods_check_in();
    show_toast($lang['msg_success_entry_delete'], 'success');
    header("HX-Trigger: update_plugins_list");
    exit;
}

// delete a theme
if(isset($_POST['delete_theme'])) {

    if(!se_hasPermission('drm_acp_sensitive_files')) {
        show_toast($lang['rm_no_access'], 'danger');
        exit;
    }

    $theme = $installed_addon($_POST['delete_theme'], SE_THEMES);

    // the default and the backend theme are always required, and the
    // currently active theme would leave the frontend without a template
    if($theme === '' || in_array($theme, ['default', 'administration', $se_settings['template']], true)) {
        show_toast($lang['msg_error_theme_in_use'], 'danger');
        exit;
    }

    // pages can override the default theme (se_pages.page_template)
    $cnt_pages = $db_content->count("se_pages", [
        "page_template" => $theme
    ]);
    if($cnt_pages > 0) {
        show_toast($lang['msg_error_theme_in_use'], 'danger');
        exit;
    }

    se_delete_addon($theme, 'theme');
    se_delete_smarty_cache();
    show_toast($lang['msg_success_entry_delete'], 'success');
    header("HX-Trigger: update_themes_list");
    exit;
}
