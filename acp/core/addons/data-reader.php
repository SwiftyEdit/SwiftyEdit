<?php

/**
 * SwiftyEdit /admin-xhr/addons/read/
 * installed plugins and themes, their update status and the catalog -
 * requests of activated plugins are handed over to their backend/reader.php
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var array $se_settings
 * @var object $twig
 * @var bool $se_upload_addons from config.php
 */

$reader_uri = '/admin-xhr/addons/read/';
$writer_uri = '/admin-xhr/addons/write/';

// the readers of the plugins rely on it
if(!isset($languagePack)) {
    $languagePack = $_SESSION['lang'] ?? 'en';
}

// give the plugins the possibility to read via xhr
$path = explode('/', $_REQUEST['query'] ?? '');
// strict whitelist instead of basename() - basename() alone still lets a bare
// ".." through (no "/" for it to strip), which would point $plugin_root one
// level above plugins/; this also rules out reaching any plugin that hasn't
// been activated, see se_is_plugin_activated()
$plugin = preg_replace('/[^a-zA-Z0-9_-]/', '', $path[2] ?? '');

if ($plugin !== '' && se_is_plugin_activated($plugin)) {
    $plugin_base = '/admin/addons/plugin/' . $plugin . '/';
    $plugin_root = SE_ROOT.'plugins/'.$plugin.'/';
    $plugin_reader_file = $plugin_root.'backend/reader.php';
    if(is_file($plugin_reader_file)) {
        include_once $plugin_reader_file;
        exit;
    }
}

$action = $_REQUEST['action'] ?? '';
$can_manage = se_hasPermission('drm_acp_sensitive_files');

// list all plugins
if($action == 'list_plugins') {

    // se_docs_context_files() for the help buttons
    require_once __DIR__.'/../docs/functions.php';

    $activated = array_column(se_get_addons('plugin'), 'addon_dir');
    $default_image = 'data:image/png;base64,'.base64_encode((string) file_get_contents(SE_PUBLIC.'/assets/themes/administration/images/poster-addons.png'));

    $plugins = [];
    foreach(se_get_all_addons() as $dir => $info) {

        $image = $default_image;
        $poster = SE_ROOT.'plugins/'.$dir.'/poster.png';
        if(is_file($poster)) {
            $image = 'data:image/png;base64,'.base64_encode((string) file_get_contents($poster));
        }

        // plugin navigation, the texts may be translated by the plugin
        $addon_lang = se_return_addon_translations($dir);
        $navigation = [];
        foreach($info['navigation'] ?? [] as $nav) {
            $navigation[] = [
                'url' => '/admin/addons/plugin/'.$dir.'/'.$nav['file'].'/',
                'text' => $addon_lang[$nav['text']] ?? $nav['text']
            ];
        }

        // open the first doc (docs/{lang}/*.md by priority, else readme.md) in the help window
        $help_link = '';
        $help_files = se_docs_context_files('plugin', $dir);
        if(!empty($help_files)) {
            $help_link = se_print_plugin_docs_link($dir, basename($help_files[0]['path']), $icon['question'], null, 'btn btn-sm btn-outline-default me-1');
        }

        // category for the filter buttons in list.twig - editors by their info.json type,
        // payment addons by the "-pay" suffix the shop relies on (see se_get_payment_addons())
        $category = 'plugin';
        if(($info['addon']['type'] ?? '') === 'editor') {
            $category = 'editor';
        } else if(str_ends_with($dir, '-pay')) {
            $category = 'payment';
        }

        $plugins[] = [
            'id' => $dir,
            'name' => $info['addon']['name'] ?? $dir,
            'version' => $info['addon']['version'] ?? '',
            'description' => $info['addon']['description'] ?? '',
            'image' => $image,
            'category' => $category,
            'activated' => in_array($dir, $activated),
            // only offer activation if the plugin ships something that depends on it
            'needs_activation' => se_plugin_needs_activation($dir, $info),
            'navigation' => $navigation,
            'help_link' => $help_link
        ];
    }

    echo $twig->render('addons/plugins-list.twig', [
        'plugins' => $plugins,
        'can_check_updates' => $can_manage,
        'can_delete' => $can_manage,
        'reader_uri' => $reader_uri,
        'writer_uri' => $writer_uri
    ]);
    exit;
}

// list catalog entries from the SwiftyEdit registry
if($action == 'list_catalog') {

    $render_notice = static fn(string $notice) => $twig->render('addons/catalog-list.twig', ['notice' => $notice]);

    // same "can upload sensitive files" right as the install page (see router.php)
    if(!$can_manage) {
        echo $render_notice($lang['rm_no_access']);
        exit;
    }
    if(!$se_upload_addons) {
        echo $render_notice($lang['catalog_disabled']);
        exit;
    }

    $catalog = se_get_catalog_entries();
    if(!$catalog['success']) {
        echo $render_notice($lang['catalog_unavailable'].' ('.$catalog['message'].')');
        exit;
    }
    if(empty($catalog['entries'])) {
        echo $render_notice($lang['catalog_empty']);
        exit;
    }

    // links and images of the registry are third-party urls, only https is used
    $https = static fn($url): string => (is_string($url) && str_starts_with($url, 'https://')) ? $url : '';

    // plugins live under SE_PLUGINS, themes under SE_THEMES - an entry of either type can be installed
    $installed = array_merge(array_keys(se_get_all_addons()), array_keys(se_get_all_themes()));

    $entries = [];
    foreach($catalog['entries'] as $slug => $entry) {

        $type = (strtolower($entry['type'] ?? 'plugin') === 'theme') ? 'theme' : 'plugin';
        $tags = array_map('strval', (array) ($entry['tags'] ?? []));
        $screenshots = array_values(array_filter(array_map($https, (array) ($entry['screenshots'] ?? []))));
        $repo_url = $https($entry['repo_url'] ?? '');

        // the addon's own icon (poster.png for plugins, images/icon.png for themes) is the thumbnail,
        // else the first screenshot - not every addon has one, the template falls back client-side
        $poster = $https(se_registry_repo_to_raw_url($repo_url, ($type === 'theme') ? 'images/icon.png' : 'poster.png'))
            ?: ($screenshots[0] ?? '/assets/themes/administration/images/poster-addons.png');

        $meta = [];
        if(($entry['version'] ?? '') !== '') {
            $meta[] = 'v'.$entry['version'];
        }
        if(($entry['requires_build'] ?? '') !== '') {
            $meta[] = $lang['catalog_requires'].' '.$entry['requires_build'];
        }

        $entries[] = [
            'name' => $entry['name'] ?? $slug,
            'description' => $entry['description'] ?? '',
            'author' => $entry['author'] ?? '',
            'repo_url' => $repo_url,
            'type' => $type,
            'tags' => $tags,
            'tags_attr' => implode(',', array_map('strtolower', $tags)),
            'poster' => $poster,
            'screenshots' => $screenshots,
            'modal_id' => 'catalog-screenshots-'.preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $slug),
            'meta' => $meta,
            'installed' => in_array($slug, $installed, true),
            'info_json_url' => $https(se_registry_repo_to_raw_url($repo_url, 'info.json'))
        ];
    }

    echo $twig->render('addons/catalog-list.twig', [
        'entries' => $entries,
        'stale' => ($catalog['source'] === 'stale_cache'),
        'writer_uri' => $writer_uri
    ]);
    exit;
}

// check if a plugin or theme is up to date
if(isset($_REQUEST['check_plugin']) || isset($_REQUEST['check_theme'])) {

    // this triggers an outbound request to the addon's own self-declared update_url
    // (see se_check_addon_update()), so it needs the same right as the install and update actions
    if(!$can_manage) {
        exit;
    }

    // release the session lock - the list fires one check per addon, and the
    // outbound request below would otherwise serialize them and block every
    // other admin request; $_SESSION stays readable after this
    session_write_close();

    if(isset($_REQUEST['check_theme'])) {
        $addon_type = 'theme';
        $addon_root = SE_THEMES;
        $addon_dir = basename((string) $_REQUEST['check_theme']);
        // themes use a prefixed id, a plugin may share the same directory name
        $response_id = 'update-response-theme-'.$addon_dir;
    } else {
        $addon_type = 'plugin';
        $addon_root = SE_PLUGINS;
        $addon_dir = basename((string) $_REQUEST['check_plugin']);
        $response_id = 'update-response-'.$addon_dir;
    }

    // confine the info file to the addon directory (traversal safeguard, see #338)
    $addon_info_file = se_resolve_within($addon_root, $addon_dir.'/info.json');
    if($addon_info_file === false || !is_file($addon_info_file)) {
        exit;
    }

    $addon_info = json_decode((string) file_get_contents($addon_info_file), true);
    $update_info = se_check_addon_update(is_array($addon_info) ? $addon_info : []);

    $status = $update_info['status'];
    if($status == 'update_available' && !$se_upload_addons) {
        $status = 'update_disabled';
    }

    echo $twig->render('addons/update-status.twig', [
        'status' => $status,
        'update' => [
            'plugin_id' => $addon_dir,
            'addon_type' => $addon_type,
            'download_url' => $update_info['download_url'] ?? '',
            'target' => $response_id
        ],
        'writer_uri' => $writer_uri
    ]);
    exit;
}

// list all themes
if($action == 'list_themes') {

    // se_docs_context_files() for the help buttons
    require_once __DIR__.'/../docs/functions.php';

    $all_theme_info = se_get_all_themes();

    $themes = [];
    foreach(get_all_templates() as $template) {

        if($template == 'administration') {
            continue;
        }

        $is_active = ($template == $se_settings['template']);

        // layout templates of the theme, the selected one only counts for the active theme
        $layouts = [];
        foreach(glob(SE_THEMES.'/'.$template.'/templates/layout*.tpl') ?: [] as $layout_tpl) {
            $layouts[] = [
                'value' => basename($layout_tpl),
                'label' => basename($layout_tpl, '.tpl'),
                'selected' => ($is_active && $se_settings['template_layout'] == basename($layout_tpl))
            ];
        }

        // color variants, e.g. dist/skins/ocean.css of the default theme
        $stylesheets = [];
        foreach(glob(SE_THEMES.'/'.$template.'/dist/skins/*.css') ?: [] as $css) {
            $stylesheets[] = [
                'value' => basename($css),
                'label' => ucfirst(basename($css, '.css')),
                'selected' => ($is_active && $se_settings['template_stylesheet'] == basename($css))
            ];
        }

        $poster = '/themes/'.$template.'/images/icon.png';
        if(!is_file(SE_PUBLIC.'/assets'.$poster)) {
            $poster = '/themes/administration/images/poster-addons.png';
        }

        // open the first doc (docs/{lang}/*.md by priority, else readme.md) in the help window
        $help_link = '';
        $help_files = se_docs_context_files('theme', $template);
        if(!empty($help_files)) {
            $help_link = se_print_theme_docs_link($template, basename($help_files[0]['path']), $icon['question'].' '.$lang['btn_help'], null, 'btn btn-default btn-sm mt-1 w-100');
        }

        // themes without an info.json (older themes) fall back to their directory name
        $theme_addon = $all_theme_info[$template]['addon'] ?? [];

        $themes[] = [
            'id' => $template,
            'name' => $theme_addon['name'] ?? $template,
            'version' => $theme_addon['version'] ?? '',
            'active' => $is_active,
            'poster' => $poster,
            'layouts' => $layouts,
            'stylesheets' => $stylesheets,
            'help_link' => $help_link,
            // the default theme ships with the core and is updated along with it
            'check_update' => ($template != 'default' && $can_manage),
            'deletable' => ($template != 'default' && !$is_active && $can_manage)
        ];
    }

    echo $twig->render('addons/themes-list.twig', [
        'themes' => $themes,
        'reader_uri' => $reader_uri,
        'writer_uri' => $writer_uri
    ]);
    exit;
}
