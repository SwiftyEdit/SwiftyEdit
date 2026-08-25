<?php
//error_reporting(E_ALL);

/**
 * global variables
 * @var array $icon
 * @var array $lang
 * @var array $se_settings
 * @var string $se_upload_addons from config.php
 */

if(!isset($languagePack)) {
    $languagePack = $_SESSION['lang'] ?? 'en';
}

// give the plugins the possibility to read via xhr
$path = explode('/', $_REQUEST['query']);
$plugin = basename($path[2]);
$plugin_base = '/admin/addons/plugin/' . $plugin . '/';
$plugin_root = SE_ROOT.'plugins/'.$plugin.'/';
$plugin_reader_file = $plugin_root.'backend/reader.php';
if(is_file("$plugin_reader_file")) {
    include_once "$plugin_reader_file";
    exit;
}



// list all plugins
if($_REQUEST['action'] == 'list_plugins') {

    $get_all_addons = se_get_all_addons();
    $se_addons = se_get_addons($t='plugin');

    // for showing help text
    $modal_template_file = file_get_contents("../acp/templates/bs-modal.tpl");

    foreach($get_all_addons as $k => $v) {

        $get_image = base64_encode(file_get_contents(SE_PUBLIC.'/assets/themes/administration/images/poster-addons.png'));

        $update_btn = '<div hx-get="/admin-xhr/addons/read/?check_plugin='.$k.'" hx-trigger="load"></div>';

        $addon_name = $v['addon']['name'];
        $addon_version = $v['addon']['version'];
        $addon_description = $v['addon']['description'];
        $addon_author = $v['addon']['author'];
        $addon_image_src = SE_ROOT.'plugins/'.$k.'/poster.png';
        if(is_file($addon_image_src)) {
            $get_image = base64_encode(file_get_contents($addon_image_src));
        }

        $addon_lang = se_return_addon_translations($k);

        $btn_help_text = '';
        $modal = '';
        $help_file_md = SE_ROOT.'/plugins/'.$k.'/readme.md';
        if(is_file($help_file_md)) {
            $addon_id = 'addonID'.$k;
            $btn_help_text = '<button type="button" class="btn btn-sm btn-default" data-bs-toggle="modal" data-bs-target="#'.$addon_id.'">'.$icon['question'].'</button>';

            $modal_body_text = file_get_contents($help_file_md);
            $Parsedown = new Parsedown();
            $modal_body = $Parsedown->text($modal_body_text);

            $modal = $modal_template_file;
            $modal = str_replace('{modalID}', $addon_id, $modal);
            $modal = str_replace('{modalTitle}', $addon_name, $modal);
            $modal = str_replace('{modalBody}', $modal_body, $modal);
            echo $modal;
        }

        $vals = ['csrf_token' => $_SESSION['token']];

        $delete_btn = '<button name="delete_plugin" value="'.$k.'" class="btn btn-sm btn-default text-danger" 
                            hx-post="/admin-xhr/addons/write/"
                            hx-trigger="click"
                            hx-confirm="'.$lang['msg_confirm_delete'].'"
                            hx-vals=\''.json_encode($vals).'\'
                            hx-swap="none"
                            >'.$icon['trash_alt'].'</button>';

        $activate_btn = '<button name="activate_addon" value="'.$k.'" class="btn btn-sm btn-default text-success"
                                hx-post="/admin-xhr/addons/write/"
                                hx-trigger="click"
                                hx-vals=\''.json_encode($vals).'\'
                                hx-swap="none"
                                >'.$lang['btn_addon_enable'].'</button>';

        foreach($se_addons as $a) {
            if($k == $a['addon_dir']) {
                $activate_btn = '<button name="deactivate_addon" value="'.$k.'" class="btn btn-sm btn-default text-danger"
                                hx-post="/admin-xhr/addons/write/"
                                hx-trigger="click"
                                hx-vals=\''.json_encode($vals).'\'
                                hx-swap="none"
                                >'.$lang['btn_addon_disable'].'</button>';
            }
        }

        echo '<div class="card mb-1 border-bottom">';
        echo '<div class="card-header d-flex justify-content-between align-items-center">';
        echo '<div>'.$addon_name.' <span class="badge badge-se">'.$addon_version.'</span></div>';
        echo $update_btn;
        echo '</div>';
        echo '<div class="card-body">';
        echo '<div class="row">';
        echo '<div class="col-md-2">';
        echo '<img src="data:image/png;base64,'.$get_image.'" class="img-fluid rounded-circle">';
        echo '</div>';
        echo '<div class="col-md-10">';
        echo '<div id="update-response-'.$k.'"></div>';
        echo '<div class="card-text">'.$addon_description.'</div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="btn-toolbar mt-1">';
        echo '<div class="btn-group">';
        if(array_key_exists('navigation',$v)) {
            foreach ($v['navigation'] as $nav) {

                $nav_text = $nav['text'];
                if (array_key_exists($nav['text'], $addon_lang)) {
                    $nav_text = $addon_lang[$nav['text']];
                }

                echo '<a href="/admin/addons/plugin/' . $k . '/' . $nav['file'] . '/" class="btn btn-sm btn-default">' . $nav_text . '</a>';
            }
        }
        echo '</div>';
        echo '<div class="btn-group ms-auto">';
        echo $btn_help_text;
        echo $delete_btn;
        echo $activate_btn;
        echo '</div>';
        echo '</div>';



        echo '</div>';
        echo '</div>';
    }

    exit;
}

// list catalog entries from the SwiftyEdit registry
if($_REQUEST['action'] == 'list_catalog') {

    // Reachable directly regardless of whether catalog.php's own link is
    // shown - same "can upload sensitive files" right as install/router.php.
    if(!se_hasPermission('drm_acp_sensitive_files')) {
        echo '<div class="card p-3">'.$lang['rm_no_access'].'</div>';
        exit;
    }

    if(!$se_upload_addons) {
        echo '<div class="card p-3">'.$lang['catalog_disabled'].'</div>';
        exit;
    }

    $catalog = se_get_catalog_entries();

    if(!$catalog['success']) {
        echo '<div class="card p-3 text-muted">';
        echo $lang['catalog_unavailable'].' ('.htmlspecialchars($catalog['message']).')';
        echo '</div>';
        exit;
    }

    if($catalog['source'] === 'stale_cache') {
        echo '<div class="alert alert-warning">'.$lang['catalog_stale'].'</div>';
    }

    if(empty($catalog['entries'])) {
        echo '<div class="card p-3 text-muted">'.$lang['catalog_empty'].'</div>';
        exit;
    }

    // Plugins live under SE_PLUGINS, themes under SE_THEMES - merge both so a
    // registry entry of either type correctly shows as already installed.
    $installed = array_merge(array_keys(se_get_all_addons()), array_keys(se_get_all_themes()));

    // Reused for the per-card screenshots modal, same template/pattern as
    // list_plugins' help-text modal above.
    $modal_template_file = file_get_contents("../acp/templates/bs-modal.tpl");

    echo '<div class="row">';
    foreach($catalog['entries'] as $slug => $entry) {

        $name          = htmlspecialchars($entry['name'] ?? $slug, ENT_QUOTES);
        $description   = htmlspecialchars($entry['description'] ?? '', ENT_QUOTES);
        $author        = htmlspecialchars($entry['author'] ?? '', ENT_QUOTES);
        $requires_build = htmlspecialchars($entry['requires_build'] ?? '', ENT_QUOTES);
        $latest        = htmlspecialchars($entry['version'] ?? '', ENT_QUOTES);
        $tags          = $entry['tags'] ?? [];
        $screenshots   = $entry['screenshots'] ?? [];
        $fallback_poster = '/assets/themes/administration/images/poster-addons.png';
        // poster.png (the plugin's own icon, optional per its own repo - see
        // docs/v2/en/09-02-plugins.md) takes priority as the card thumbnail
        // over a screenshot, since it's the plugin's actual branding. Not
        // every plugin has one, so the <img onerror> below falls back
        // client-side instead of doing an extra existence-check request
        // per card on every cache refresh.
        $poster = se_registry_repo_to_raw_url($entry['repo_url'] ?? '', 'poster.png') ?: (($screenshots[0] ?? null) ?: $fallback_poster);

        $info_json_url = se_registry_repo_to_raw_url($entry['repo_url'] ?? '', 'info.json');
        $modal_id      = 'catalog-screenshots-'.preg_replace('/[^a-zA-Z0-9_-]/', '', $slug);

        // Small circular thumbnail, matching the installed-plugins list's
        // own poster styling (acp/core/addons/data-reader.php's
        // list_plugins block) rather than a large banner image. The
        // screenshots gallery gets its own explicit link below (see
        // $screenshots_link) instead of being a silent click-target on the
        // image itself, which nobody would discover on their own.
        $poster_attrs = 'src="'.htmlspecialchars($poster, ENT_QUOTES).'" class="img-fluid rounded-circle" onerror="this.onerror=null;this.src=\''.$fallback_poster.'\';"';

        $screenshots_link = '';
        if(!empty($screenshots)) {
            $screenshots_link = '<button type="button" class="btn btn-sm btn-link p-0"
                    data-bs-toggle="modal" data-bs-target="#'.$modal_id.'">'
                    .$icon['images'].' '.$lang['catalog_screenshots'].' ('.count($screenshots).')</button>';
        }

        // Emitted as a sibling before the card (not nested inside it) -
        // same placement list_plugins already uses for its help-text modal,
        // since Bootstrap modals are position:fixed overlays and shouldn't
        // sit inside a .card's own stacking/scroll context.
        if(!empty($screenshots)) {
            $modal_body = '';
            foreach($screenshots as $shot) {
                $modal_body .= '<img src="'.htmlspecialchars($shot, ENT_QUOTES).'" class="img-fluid mb-2 rounded">';
            }
            $modal = str_replace(
                ['{modalID}', '{modalTitle}', '{modalBody}'],
                [$modal_id, $name.' - '.$lang['catalog_screenshots'], $modal_body],
                $modal_template_file
            );
            echo $modal;
        }

        $tags_attr = htmlspecialchars(implode(',', array_map('strtolower', $tags)), ENT_QUOTES);
        echo '<div class="col-md-4 mb-3" data-catalog-tags="'.$tags_attr.'">';
        echo '<div class="card h-100">';
        echo '<div class="card-body d-flex flex-column">';
        echo '<div class="row mb-2">';
        echo '<div class="col-3 text-center"><img '.$poster_attrs.'></div>';
        echo '<div class="col-9">';
        echo '<div class="card-title d-flex justify-content-between">';
        echo '<span>'.$name.'</span><span class="badge badge-se">'.$latest.'</span>';
        echo '</div>';
        $repo_url_attr = htmlspecialchars($entry['repo_url'] ?? '', ENT_QUOTES);
        echo '<div class="card-text small text-muted">'.$lang['catalog_by'].' <a href="'.$repo_url_attr.'" target="_blank" rel="noopener">'.$author.'</a></div>';
        if($screenshots_link !== '') {
            echo '<div class="small mt-1">'.$screenshots_link.'</div>';
        }
        echo '</div>'; // col-9
        echo '</div>'; // row

        echo '<div class="card-text flex-grow-1">'.$description.'</div>';

        if(!empty($tags)) {
            echo '<div class="mb-1">';
            foreach($tags as $tag) {
                echo '<span class="badge badge-se me-1">'.htmlspecialchars($tag, ENT_QUOTES).'</span>';
            }
            echo '</div>';
        }

        if($requires_build !== '') {
            echo '<div class="small text-muted mt-2">'.$lang['catalog_requires'].' '.$requires_build.'</div>';
        }

        echo '<div class="btn-toolbar mt-2">';

        if(in_array($slug, $installed, true)) {
            echo '<span class="badge text-bg-success">'.$lang['catalog_installed'].'</span>';
        } elseif($info_json_url) {
            // Targets the shared #catalogInstallModalBody (see catalog.php)
            // instead of an inline per-card div - swapping the confirm/
            // install response in inline would grow that card and desync
            // the grid row's height against its siblings.
            $vals = json_encode([
                'csrf_token' => $_SESSION['token'],
                'get_addon_info_from_url' => $info_json_url,
                'target_id' => 'catalogInstallModalBody'
            ]);
            echo '<button class="btn btn-sm btn-primary"
                    hx-post="/admin-xhr/addons/write/"
                    hx-vals=\''.$vals.'\'
                    hx-target="#catalogInstallModalBody"
                    data-bs-toggle="modal"
                    data-bs-target="#catalogInstallModal">'
                    .$lang['btn_install'].'</button>';
        } else {
            echo '<span class="badge text-bg-danger">'.$lang['catalog_invalid_repo'].'</span>';
        }

        echo '</div>';

        echo '</div>'; // card-body
        echo '</div>'; // card
        echo '</div>'; // col
    }
    echo '</div>'; // row

    exit;
}

// check if a plugin is up to date
if(isset($_REQUEST['check_plugin'])) {

    $plugin_dir = basename($_REQUEST['check_plugin']);

    // confine the info file to the plugins directory (traversal safeguard, see #338)
    $addon_info_file = se_resolve_within(SE_PLUGINS, $plugin_dir . '/info.json');
    if ($addon_info_file === false) {
        exit;
    }

    // load plugin info
    $json = @file_get_contents($addon_info_file);
    $plugin_info = json_decode($json, true);

    $update_info = se_check_addon_update($plugin_info);

    if($update_info['status'] == 'update_available') {

        $vals = [
            'csrf_token' => $_SESSION['token'],
            'plugin_id' => $plugin_dir,
            'download_url' => $update_info['download_url']
        ];

        $update_btn = '<button name="update_addon_from_url" value="1" class="btn btn-sm btn-default text-success" 
                            hx-post="/admin-xhr/addons/write/"
                            hx-vals=\''.json_encode($vals).'\'
                            hx-target="#update-response-'.htmlspecialchars($plugin_dir, ENT_QUOTES).'"
                            >Update '.$icon['arrow_clockwise'].'</button>';
        echo $update_btn;
    } else if($update_info['status'] == 'up_to_date') {
        echo '<span class="badge text-bg-success">';
        echo 'Status: '.$icon['check_circle'];
        echo '</span>';
    } else {
        echo '<span class="badge badge-se">';
        echo 'Status: '.$icon['question_circle'];
        echo '</span>';
    }
    echo '</span>';

    exit;
}

// list all themes
if($_REQUEST['action'] == 'list_themes') {

    $all_themes = get_all_templates();
    $all_theme_info = se_get_all_themes();

    foreach($all_themes as $template) {

        if($template == 'administration') {
            continue;
        }

        $class = '';
        $active = '';
        if($template == $se_settings['template']) {
            $class = 'border-success border-2 border-opacity-50';
            $active = '(active)';
        }

        // get all layout templates from this theme
        $arr_layout_tpls = glob(SE_PUBLIC."/assets/themes/".$template."/templates/layout*.tpl");

        // Themes without an info.json (older themes, still shipping the retired
        // info.xml) simply fall back to their raw directory name, same as before.
        $theme_addon = $all_theme_info[$template]['addon'] ?? [];
        $theme_name = htmlspecialchars($theme_addon['name'] ?? $template, ENT_QUOTES);
        $theme_version = htmlspecialchars($theme_addon['version'] ?? '', ENT_QUOTES);

        echo '<div class="card mb-3 '.$class.'">';
        echo '<div class="card-header">'.$theme_name;
        if($theme_version !== '') {
            echo ' <span class="badge badge-se">'.$theme_version.'</span>';
        }
        echo ' '.$active.'</div>';
        echo '<div class="card-body">';

        echo '<div class="row">';
        echo '<div class="col-md-8">';

        echo '<form hx-post="/admin-xhr/addons/write/" hx-swap="none">';

        echo '<label>Layout</label>';
        echo '<select name="select_template" class="form-control image-picker">';
        foreach($arr_layout_tpls as $layout_tpl) {
            $active = '';
            if($se_settings['template_layout'] == basename($layout_tpl) && ($template == $se_settings['template'])) {
                $active = 'selected';
            }
            $tpl_name = basename($layout_tpl,'.tpl');
            $value = "$template<|-|>$tpl_name".'.tpl';
            echo '<option value="'.$value.'" '.$active.'>'.$tpl_name.'</option>';
        }
        echo '</select>';

        // Color variants of this theme, e.g. src/scss/skins/ocean.scss in
        // the default theme's Vite build -> dist/skins/ocean.css. Loaded
        // in addition to the theme's core stylesheet - see
        // templates/head.tpl in the theme itself.
        $stylesheets = glob(SE_PUBLIC."/assets/themes/".$template."/dist/skins/*.css");
        echo '<label>Stylesheet</label>';
        echo '<select name="select_template_sytlesheet" class="form-control">';
        echo '<option value="">'.$lang['label_default_template'].'</option>';
        foreach($stylesheets as $css) {

            $css_file = basename($css);
            $active = '';
            if($css_file == $se_settings['template_stylesheet']) {
                $active = 'selected';
            }

            $css_name = ucfirst(basename($css, '.css'));
            echo '<option value="'.htmlspecialchars($css_file, ENT_QUOTES).'" '.$active.'>'.htmlspecialchars($css_name, ENT_QUOTES).'</option>';
        }

        echo '</select><hr>';

        echo '<input type="submit" class="btn btn-success btn-md" name="save_default_layout" value="Layout '.$lang['save'].'">';
        echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';

        echo '</form>';

        echo '</div>';
        echo '<div class="col-md-4">';
        $theme_poster = '/themes/'.$template.'/images/icon.png';
        $real_theme_poster = SE_PUBLIC.'/assets'.$theme_poster;
        if (!file_exists($real_theme_poster) && !is_file($real_theme_poster) && !is_readable($real_theme_poster)) {
            $theme_poster = '/themes/administration/images/poster-addons.png';
        }
        echo '<img src="'.$theme_poster.'" class="img-fluid rounded">';
        echo '<a class="btn btn-default btn-sm mt-3 w-100" href="/admin/addons/theme/'.$template.'/">'.$lang['btn_options'].'</a>';
        echo '</div>';
        echo '</div>';

        echo '</div>';
        echo '</div>';


    }

    exit;
}
