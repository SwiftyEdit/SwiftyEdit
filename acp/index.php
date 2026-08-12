<?php

/**
 * SwiftyEdit - backend main file
 *
 * global variables
 * @var array $lang from language files
 * @var string $languagePack
 * @var string $lang_sign from languages/%lang%/index.php
 * @var string $lang_desc from languages/%lang%/index.php
 * @var string $languagePackFallback from languages/%lang%/index.php
 * @var array $icon from icons.php
 * @var array $se_prefs preferences
 *
 * from config
 * @var string $se_db_content filepath to sqlite file
 * @var string $se_db_user filepath to sqlite file
 * @var string $se_db_posts filepath to sqlite file
 * @var string $img_path filepath to image/uploads directory
 * @var string $files_path filepath to files upload directory
 *
 * from versions.php
 * @var string $se_version_title SwiftyEdit version title
 * @var string $se_version_build build number
 *
 * from editors.php
 * @var string $editor_css_url        active theme editor content CSS URL
 * @var string $editor_img_list_url   TinyMCE image-list endpoint
 * @var string $editor_link_list_url  TinyMCE link-list endpoint
 *
 * others
 * @var string $tn get parameter
 * @var string $sub
 * @var string $maininc
 *
 *
 */

session_start();
const SE_SECTION = "backend";

require '../vendor/autoload.php';

use Medoo\Medoo;

$purifier_config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($purifier_config);

require '../acp/core/icons.php';

require '../config.php';
if (is_file(SE_CONTENT . '/config.php')) {
    include SE_CONTENT . '/config.php';
}
if (is_file(SE_CONTENT . '/config_smtp.php')) {
    include SE_CONTENT . '/config_smtp.php';
}

// only show errors when explicitly running in development mode
if ($se_environment === 'd') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

$version_file = file_get_contents(SE_ROOT.'version.json');
$se_version = json_decode($version_file, true);


/**
 * connect the database
 * @var string $db_content
 * @var string $db_user
 * @var string $db_posts
 */

require SE_ROOT.'/app/database.php';

define("IMAGES_FOLDER", "$img_path");
define("FILES_FOLDER", "$files_path");


require '../acp/core/functions.php';
require '../acp/core/access.php';

if (!isset($_SESSION['editor_class'])) {
    $_SESSION['editor_class'] = "wysiwyg";
}

/* switch editor - plain text or wysiwyg */
if (isset($_GET['editor'])) {

    if ($_GET['editor'] == 'wysiwyg') {
        $_SESSION['editor_class'] = "wysiwyg";
    } elseif ($_GET['editor'] == 'plain') {
        $_SESSION['editor_class'] = "plain";
    } else {
        $_SESSION['editor_class'] = "code";
    }

}

if ($_SESSION['editor_class'] == "wysiwyg") {
    $editor_class = "mceEditor";
    $editor_small_class = "mceEditor_small";
} elseif ($_SESSION['editor_class'] == "plain") {
    $editor_class = "plain";
    $editor_small_class = "plain";
} else {
    $editor_class = "aceEditor_html";
    $editor_small_class = "aceEditor_html";
}



if(isset($_GET['query'])) {
    $query = se_clean_query($_GET['query']);
}
if(!isset($query)) {
    $query = '/admin/';
}
$se_path = explode("/", $query);

$se_sections = [
    "pages","snippets","shortcodes",
    "addons","users","categories",
    "settings","shop","events",
    "blog","inbox","uploads", "dashboard",
    "update"
];

$se_section = 'dashboard';
$maininc = "dashboard/router.php";

if(in_array($se_path[0], $se_sections)) {
    $se_section = $se_path[0];
    $maininc = $se_section."/router.php";
}


$all_plugins = se_get_all_addons();
$all_mods = $all_plugins;
$cnt_mods = count($all_mods);

/*
 * Content-format editors (mode "format", e.g. block-builder/markdown
 * plugins) are a separate axis from the WYSIWYG/code widget switcher below -
 * they don't wrap a <textarea> and must not become the default WYSIWYG
 * editor. se_bootstrap_editor_plugins() loads every editor addon's
 * global/index.php, where content-format plugins call se_register_editor().
 */
require_once SE_ROOT . 'app/functions/functions.editors.php';
$se_editor_addons = se_bootstrap_editor_plugins($all_plugins);

$se_wysiwyg_editor_addons = array_filter($se_editor_addons, static function ($editor_addon) {
    return ($editor_addon['mode'] ?? '') !== 'format';
});

/* default editor: first installed WYSIWYG/code editor (by order), else plain text */
$se_default_editor = 'plain';
if (!empty($se_wysiwyg_editor_addons)) {
    $se_first_editor = reset($se_wysiwyg_editor_addons);
    $se_default_editor = $se_first_editor['id'] ?? 'plain';
}

$se_labels = se_get_labels();
$cnt_labels = count($se_labels);
$all_langs = get_all_languages();

/**
 * read the settings
 * example use: $se_settings['default_language']
 */

$se_get_preferences = se_get_preferences();

foreach ($se_get_preferences as $k => $v) {
    $key = $se_get_preferences[$k]['option_key'];
    $value = $se_get_preferences[$k]['option_value'];

    /* $se_prefs['prefs_pagetitle'] */
    $se_prefs[$key] = $value;

    /* without the 'prefs_' prefix $se_prefs['pagetitle'] */
    if(substr($key,0,6) == 'prefs_') {
        $short_key = substr($key,6);
        $se_prefs[$short_key] = $value; // old
        $se_settings[$short_key] = $value; // new
    }
}

if ($se_settings['timezone'] != '') {
    date_default_timezone_set($se_settings['timezone']);
}

/* set language */

if (!isset($_SESSION['lang'])) {
    if ($se_settings['default_language'] != '') {
        $_SESSION['lang'] = $se_settings['default_language'];
    } else {
        $_SESSION['lang'] = $languagePack;
    }
}

if (isset($_GET['set_lang'])) {
    $set_lang = se_sanitize_lang_input($_GET['set_lang']);
    if (is_dir(SE_ROOT."languages/$set_lang/")) {
        $_SESSION['lang'] = "$set_lang";
    }
}

if (isset($_SESSION['lang'])) {
    $languagePack = basename($_SESSION['lang']);
}

$languagePack = $purifier->purify($languagePack);
require SE_ROOT.'/languages/'.$languagePack.'/index.php';
require SE_ROOT.'/languages/index.php';


/**
 * $default_lang_code (string) the default language code
 */

if ($se_settings['default_language'] != '') {
    include SE_ROOT.'/languages/' . $se_settings['default_language'] . '/index.php';
    $default_lang_code = $lang_sign; // de|en|es ...
}

/**
 * $lang_codes (array) all available lang codes
 * hide languages from $prefs_deactivated_languages
 * all active languages are stored in $active_lang
 */
if (isset($se_settings['deactivated_languages']) AND $se_settings['deactivated_languages'] != '') {
    $arr_lang_deactivated = json_decode($se_settings['deactivated_languages']);
}

foreach ($all_langs as $l) {
    if (isset($arr_lang_deactivated) && (in_array($l['lang_folder'], $arr_lang_deactivated))) {
        continue;
    }

    $langs[] = $l['lang_sign'];
}

$lang_codes = array_values(array_unique($langs));

foreach($lang_codes as $l) {

    $lang_file = SE_ROOT.'/languages/' . $l . '/index.php';

    if(is_file($lang_file)) {
        include $lang_file;
        $active_lang[$l]['sign'] = $lang_sign;
        $active_lang[$l]['name'] = $lang_desc;
        $active_lang[$l]['flag'] = '/admin-xhr/images/?src=languages/' . $l . '/flag.png';
    }
}

require_once SE_ROOT . 'app/hooks/hooks-meta.php';
require_once SE_ROOT . 'app/hooks/hooks-map-helper.php';
require_once SE_ROOT . 'app/hooks/hooks-backend.php';



// hooks - register meta information
foreach ($all_plugins as $pluginDir => $pluginData) {
    $metaPath = SE_ROOT . 'plugins/' . $pluginDir . '/hooks-backend/meta.php';
    if (!is_file($metaPath)) {
        continue;
    }

    // Load meta array from plugin file
    $meta = require $metaPath;

    // Skip invalid meta definitions
    if (!is_array($meta)) {
        continue;
    }

    // Register meta under plugin name (directory)
    se_register_backend_hook_meta($pluginDir, $meta);
}

// Load backend hook handlers for all plugins
foreach ($all_plugins as $pluginDir => $pluginData) {
    $backendHooksPath = SE_ROOT . 'plugins/' . $pluginDir . '/hooks-backend';
    if (!is_dir($backendHooksPath)) {
        continue;
    }

    foreach (glob($backendHooksPath . '/*.php') as $hookFile) {
        if (basename($hookFile) === 'meta.php') {
            continue;
        }
        require_once $hookFile;
    }
}

// Load global hook handlers (fire on both frontend and backend triggers)
// for all plugins
require_once SE_ROOT . 'app/hooks/hooks-global.php';

foreach ($all_plugins as $pluginDir => $pluginData) {
    $globalHooksPath = SE_ROOT . 'plugins/' . $pluginDir . '/hooks-global';
    if (!is_dir($globalHooksPath)) {
        continue;
    }

    foreach (glob($globalHooksPath . '/*.php') as $hookFile) {
        // Each file registers callbacks via se_add_global_hook(...)
        require_once $hookFile;
    }
}


/* build absolute URL */
if ($se_settings['cms_ssl_domain'] != '') {
    $se_base_url = $se_settings['cms_ssl_domain'] . $se_settings['cms_base'];
} else {
    $se_base_url = $se_settings['cms_domain'] . $se_settings['cms_base'];
}

if (!isset($_COOKIE['acptheme'])) {
    setcookie("acptheme", "dark_mono", time() + (3600 * 24 * 365));
}

if (isset($_GET['theme']) && ($_GET['theme'] == 'light_mono')) {
    setcookie("acptheme", 'light_mono', time() + (3600 * 24 * 365));
    $set_acptheme = 'light_mono';
}

if (isset($_GET['theme']) && ($_GET['theme'] == 'dark_mono')) {
    setcookie("acptheme", 'dark_mono', time() + (3600 * 24 * 365));
    $set_acptheme = 'dark_mono';
}


if (isset($set_acptheme)) {
    $acptheme = $set_acptheme;
} else {
    $acptheme = $_COOKIE["acptheme"];
}

// Keep the <title> tag readable even if the configured page name is very long.
$se_title_pagename = se_return_first_chars($se_settings['pagename'], 30, false);
if (strlen($se_settings['pagename']) > 30) {
    $se_title_pagename .= '…';
}

?>
<!DOCTYPE html>
<html lang="<?php echo htmlentities($languagePack); ?>" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlentities($se_title_pagename); ?> ~ <?php echo htmlentities($se_section); ?></title>

    <link rel="icon" type="image/png" sizes="32x32" href="/themes/administration/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/themes/administration/images/favicon-16x16.png">
    <link rel="icon" type="image/x-icon" href="/themes/administration/images/favicon.ico"/>

    <link rel="stylesheet" href="/themes/administration/dist/backend.css?v=2026-07-30" type="text/css" media="screen, projection">

    <script type="text/javascript">
        const languagePack = "<?php echo htmlentities($languagePack); ?>";
        let ace_theme;
        let tinymce_skin;
        ace_theme = 'chrome';
        tinymce_skin = 'oxide';

        const storedTheme = localStorage.getItem('backendTheme');

        if (storedTheme === 'dark') {
            ace_theme = 'twilight';
            tinymce_skin = 'oxide-dark';
        }
    </script>

    <script src="/themes/administration/dist/backend.js?v=2026-06-11"></script>
    <?php
    /* load the <head> assets of every installed editor plugin */
    foreach ($se_editor_addons as $editor_addon) {
        $se_editor_current = $editor_addon;
        $editor_head = SE_ROOT . 'plugins/' . $editor_addon['dir'] . '/backend/head.php';
        if (is_file($editor_head)) {
            include $editor_head;
        }
    }
    ?>


    <?php
    include_once '../acp/core/templates.php';
    ?>

</head>
<body>


<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas">
    <div class="offcanvas-header">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <?php include '../acp/core/nav_sidebar.php'; ?>
    </div>
</div>

<?php
if (is_file('../maintenance.html')) {
    echo '<div class="alert alert-danger rounded-0 m-0">';
    echo $lang['update_msg_modus_activated'];
    echo '</div>';
}
?>

<div class="d-flex" id="page-content">

    <nav id="sidebar" class="d-none d-lg-block">
        <?php include '../acp/core/nav_sidebar.php'; ?>
    </nav>


    <div class="flex-grow-1 main-scrollable">

    <?php
    $page_header_class = 'ph-dashboard';
    if($se_section != '') {
        $page_header_class = 'ph-'.$se_section;
    }
    echo '<header class="'.$page_header_class.' page-header">';
    require '../acp/core/nav_top_filter.php';
    require '../acp/core/nav_top.php';
    echo '</header>';
    ?>


    <div class="container-fluid py-3" id="container">
        <?php include '../acp/core/' . $maininc; ?>
        <?php include '../acp/core/editors.php'; ?>


    <div id="footer">
        <p class="text-center">
            <?php
            foreach($active_lang as $k => $v) {
                $lang_icon = '<img src="' . $v['flag'] . '" style="vertical-align: baseline; width:18px; height:auto;">';
                echo '<a class="btn btn-sm btn-default" href="?set_lang=' . $v['sign'] . '">' . $lang_icon . ' ' . $v['name'] . '</a> ';
            }
            ?>
        </p>
        <hr>
        <p><img src="/themes/administration/images/swiftyedit_icon.svg" alt="se-logo" width="60px"><br>
            <b>SwiftyEdit</b><br>copyright © <?php echo date('Y'); ?>, <a href="https://swiftyedit.com/" target="_blank">SwiftyEdit.com</a><br>
            <small class="text-muted se-privacy-blur">Build <?php echo $se_version['build']; ?></small></p>
        <p class="d-none"><?php echo microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']; ?></p>
    </div>

    </div>

    </div>
</div>
<div class="bottom-bar">
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
            data-bs-target="#uploadModal"><?php echo $icon['upload']; ?> Upload
    </button>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel"><?php echo $icon['upload']; ?> Upload</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="dropper" class="dropper-form" action="/admin/upload/" method="POST">
                    <!--
                    <input type="file" />
                    <button type="submit">Upload</button>
                    -->

                    <?php

                    $path_img = IMAGES_FOLDER;
                    $img_dirs = se_get_dirs_rec($path_img);

                    $path_files = FILES_FOLDER;
                    $files_dirs = se_get_dirs_rec($path_files);

                    $img_folder = basename($path_img);
                    $files_folder = basename($path_files);
                    ?>

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label><?php echo $lang['upload_destination']; ?></label>
                            <select name="upload_destination" class="form-control custom-select">
                                <optgroup label="<?php echo $lang['images']; ?>">
                                    <option value="<?php echo $path_img; ?>"><?php echo $img_folder; ?></option>
                                    <?php
                                    foreach($img_dirs as $d) {
                                        $short_d = str_replace($path_img, '', $d);
                                        echo '<option value="'.$d.'">'.$img_folder.$short_d.'</option>';
                                    }
                                    ?>
                                </optgroup>
                                <optgroup label="<?php echo $lang['files']; ?>">
                                    <option value="<?php echo $path_files; ?>"><?php echo $files_folder; ?></option>
                                    <?php
                                    foreach($files_dirs as $d) {
                                        $short_d = str_replace($path_files, '', $d);
                                        echo '<option value="'.$d.'">'.$files_folder.$short_d.'</option>';
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="file_mode" value="overwrite" id="overwrite">
                                <label class="form-check-label" for="overwrite">
                                    <?php echo $lang['upload_overwrite_existing_files']; ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="w" value="<?php echo $se_settings['maximagewidth']; ?>" />
                    <input type="hidden" name="w_tmb" value="<?php echo $se_settings['maxtmbwidth']; ?>" />
                    <input type="hidden" name="h" value="<?php echo $se_settings['maximageheight']; ?>" />
                    <input type="hidden" name="h_tmb" value="<?php echo $se_settings['maxtmbheight']; ?>" />
                    <input type="hidden" name="fz" value="<?php echo $se_settings['maxfilesize']; ?>" />
                    <input type="hidden" name="unchanged" value="<?php echo $se_settings['uploads_remain_unchanged']; ?>" />
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- modal for documentation -->
<div id="helpModal" class="modal fade"
     style="display: none"
     aria-hidden="true" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content"></div>
    </div>
</div>

<!-- modal for page infos -->
<div id="infoModal" class="modal fade"
     style="display: none"
     aria-hidden="true" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content"></div>
    </div>
</div>

<!-- fullscreen modal for content-format editors (blocks_v1, markdown_v1, ...) -
     see acp/templates/bs-form-input-content-editor.tpl's fullscreen button and
     the content-editor JS glue below, which moves a .content-editor-mount into
     .content-editor-fullscreen-slot on show and moves it back on hide -->
<div class="modal fade" id="contentEditorFullscreenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header py-2">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body content-editor-fullscreen-slot"></div>
        </div>
    </div>
</div>


<?php
/* load the footer init of every installed editor plugin (registers window.seEditors) */
foreach ($se_editor_addons as $editor_addon) {
    $se_editor_current = $editor_addon;
    $editor_init = SE_ROOT . 'plugins/' . $editor_addon['dir'] . '/backend/init.js.php';
    if (is_file($editor_init)) {
        include $editor_init;
    }
}
?>
<script type="text/javascript">

    window.seEditors = window.seEditors || {};

    /*
     * Content editor switch. Each editor plugin registers itself in
     * window.seEditors keyed by its editor id (e.g. "tinymce", "ace"). The
     * switch value "plain" is the built-in plain-text mode and has no
     * registered editor object.
     */
    var seDefaultEditor = '<?php echo $se_default_editor; ?>';

    function seCurrentEditorMode() {
        var editor_mode = localStorage.getItem('editor_mode');
        /* fall back to the default if the stored editor is no longer installed */
        if (editor_mode && editor_mode !== 'plain' && !window.seEditors[editor_mode]) {
            editor_mode = null;
        }
        if (!editor_mode) {
            editor_mode = seDefaultEditor;
        }
        return editor_mode;
    }

    function switchEditorMode(mode) {

        var textEditor = $('textarea[class*=switchEditor]');

        /* tear down whichever editor is currently attached */
        Object.keys(window.seEditors).forEach(function (id) {
            try {
                window.seEditors[id].detach(textEditor);
            } catch (e) {}
        });

        textEditor.removeClass();
        textEditor.removeAttr('style');

        var editor = window.seEditors[mode];
        if (editor) {
            textEditor.addClass((editor.cls || '') + ' form-control switchEditor');
            textEditor.css("display", "flex");
            editor.attach(textEditor);
        } else {
            /* plain textarea */
            textEditor.addClass('plain form-control switchEditor');
            textEditor.css("visibility", "visible");
            textEditor.css("display", "flex");
        }

        $("input[value=" + mode + "]").prop('checked', true);
        $("input[name='optEditor']").parent().removeClass('active');
        $("input[value=" + mode + "]").parent().addClass('active');

    }

    $(function () {

        var editor_mode = seCurrentEditorMode();
        localStorage.setItem("editor_mode", editor_mode);

        /*
         * Delegated binding (on document, not the specific radio elements) so
         * this keeps working after an HTMX swap replaces #pageContentField -
         * and its optEditor radios - with fresh DOM (e.g. switching the
         * content format back to "Legacy (HTML)", see
         * tpl_content_format_switch() in acp/core/templates.php). A direct
         * .on("change", fn) binding would only ever reach the radios present
         * at page-load time.
         */
        $(document).on("change", 'input[name="optEditor"]', function () {
            var button = $("input[name='optEditor']:checked").val();
            localStorage.setItem("editor_mode", button);
            switchEditorMode(button);
        });

        switchEditorMode(editor_mode);

    });

    /*
     * Content-format editors (blocks_v1, markdown_v1, ...). Each editor plugin
     * registers itself in window.seContentEditors keyed by its editor id, with
     * mount(el, content) / serialize(el) methods. Mount points are
     * .content-editor-mount divs (see acp/templates/bs-form-input-content-editor.tpl);
     * the paired hidden textarea.content-editor-value (looked up via
     * data-value-field, not DOM adjacency - the mount can be moved into the
     * fullscreen modal below) carries the serialized value into the (HTMX)
     * form submit.
     */
    window.seContentEditors = window.seContentEditors || {};

    function seMountContentEditors(scope) {
        (scope || document).querySelectorAll('.content-editor-mount').forEach(function (el) {
            if (el.dataset.mounted === '1') {
                return;
            }
            var editor = window.seContentEditors[el.dataset.editor];
            if (!editor) {
                el.innerHTML = '<div class="alert alert-warning">Editor "' + el.dataset.editor + '" not installed.</div>';
                return;
            }
            var content = null;
            try {
                content = JSON.parse(el.dataset.content || 'null');
            } catch (e) {}
            editor.mount(el, content);
            el.dataset.mounted = '1';
        });
    }

    $(function () {
        seMountContentEditors(document);
    });

    document.body.addEventListener('htmx:configRequest', function (evt) {
        document.querySelectorAll('.content-editor-mount').forEach(function (el) {
            var editor = window.seContentEditors[el.dataset.editor];
            var valueField = document.getElementById(el.dataset.valueField);
            if (!editor || !valueField) {
                return;
            }
            /*
             * The plugin's serialize() only returns its own inner "content"
             * value (a markdown string, a block tree, ...) - Core owns the
             * {"editor":...,"content":...} envelope on both the PHP side
             * (se_decode_editor_content()) and here, so plugins never need to
             * know the wrapper shape.
             */
            evt.detail.parameters[valueField.name] = JSON.stringify({
                editor: el.dataset.editor,
                content: editor.serialize(el)
            });
        });
    });

    document.body.addEventListener('htmx:afterSwap', function (evt) {
        /*
         * Deliberately not scoped to evt.detail.target: for an outerHTML
         * swap (used by the content-format switch, see
         * tpl_content_format_switch() in acp/core/templates.php), htmx
         * removes the original target element from the DOM and inserts the
         * new content in its place, but evt.detail.target still points at
         * that now-detached original element - scoping to it silently finds
         * nothing in the (correct) new content. Both functions below already
         * tolerate a document-wide scan (seMountContentEditors() is guarded
         * by data-mounted, switchEditorMode() already queries
         * document-wide internally), so scan the whole document instead.
         */
        seMountContentEditors(document);
        if (document.querySelector('textarea[class*=switchEditor]')) {
            switchEditorMode(seCurrentEditorMode());
        }
    });

    /*
     * Fullscreen toggle (see the .content-editor-fullscreen-btn in
     * bs-form-input-content-editor.tpl and #contentEditorFullscreenModal
     * above): moves the .content-editor-mount into the modal on open, moves
     * it back to its original spot in the form on close. The mount keeps its
     * SortableJS instances etc. intact across the move - only its position in
     * the DOM tree changes, nothing is re-mounted.
     */
    (function () {
        var modalEl = document.getElementById('contentEditorFullscreenModal');
        if (!modalEl) {
            return;
        }
        var slotEl = modalEl.querySelector('.content-editor-fullscreen-slot');
        var origin = null;

        modalEl.addEventListener('show.bs.modal', function (evt) {
            var trigger = evt.relatedTarget;
            var mountEl = trigger ? document.getElementById(trigger.dataset.editorTarget) : null;
            if (!mountEl) {
                return;
            }
            origin = { el: mountEl, parent: mountEl.parentNode, nextSibling: mountEl.nextSibling };
            slotEl.appendChild(mountEl);
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            if (origin) {
                origin.parent.insertBefore(origin.el, origin.nextSibling);
                origin = null;
            }
        });
    })();

    <?php
    $gc_maxlifetime = ini_get("session.gc_maxlifetime");
    if ($se_prefs['prefs_acp_session_lifetime'] > $gc_maxlifetime) {
        $maxlifetime = $se_prefs['prefs_acp_session_lifetime'];
    } else {
        $maxlifetime = $gc_maxlifetime;
    }

    if (isset($_COOKIE['identifier'])) {
        echo "var auto_logout = false;";
    } else {
        echo "var auto_logout = true;";
    }
    echo "var maxlifetime = '{$maxlifetime}';";
    ?>
    var countdown = {
        startInterval: function () {
            var currentId = setInterval(function () {
                $('#currentSeconds').html(maxlifetime);

                if (maxlifetime == 60) {
                    $('#expireDiv').removeClass('expire-hidden');
                    $('#expireDiv').addClass('expire-start');
                }
                if (maxlifetime == 30) {
                    $('#expireDiv').addClass('expire-soon');
                }
                if (maxlifetime == 15) {
                    $('#expireDiv').addClass('expire-danger');
                }
                if (maxlifetime < 0) {
                    window.location.href = "/index.php?goto=logout";
                }
                --maxlifetime;
            }, 1000);
            countdown.intervalId = currentId;
        }
    };
    if (auto_logout !== false) {
        countdown.startInterval();
    }

</script>

</body>
</html>