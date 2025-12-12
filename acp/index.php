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
 * @var string $tinyMCE_config_contents
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


require '../acp/core/access.php';
require '../acp/core/functions.php';

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


$all_mods = se_get_all_addons();
$cnt_mods = count($all_mods);
$all_plugins = se_get_all_addons();
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
        $real_img_src = SE_ROOT.'/languages/' . $l . '/flag.png';
        $encoded_flag = base64_encode(file_get_contents($real_img_src));
        $active_lang[$l]['flag'] = 'data:image/png;base64,'.$encoded_flag;
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


?>
<!DOCTYPE html>
<html lang="<?php echo htmlentities($languagePack); ?>" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <title>SwiftyEdit / <?php echo htmlentities($se_section); ?></title>

    <link rel="icon" type="image/png" sizes="32x32" href="/themes/administration/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/themes/administration/images/favicon-16x16.png">
    <link rel="icon" type="image/x-icon" href="/themes/administration/images/favicon.ico"/>

    <link rel="stylesheet" href="/themes/administration/dist/backend.css?v=2025-04-23" type="text/css" media="screen, projection">

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

    <script src="/themes/administration/dist/backend.js?v=2025-04-23"></script>
    <script src="/themes/administration/dist/tinymce/tinymce.min.js"></script>
    <script src="/themes/administration/dist/tinymce-jquery/tinymce-jquery.js"></script>


    <?php
    include_once '../acp/core/templates.php';
    ?>

</head>
<body>




<div id="page-content">

    <?php
    if (is_file('../maintenance.html')) {
        echo '<div class="alert alert-danger rounded-0 m-0">';
        echo $lang['update_msg_modus_activated'];
        echo '</div>';
    }
    ?>



    <?php
    $page_header_class = 'ph-dashboard';
    if($se_section != '') {
        $page_header_class = 'ph-'.$se_section;
    }
    echo '<div class="'.$page_header_class.' page-header">';
    require '../acp/core/nav_top_filter.php';
    require '../acp/core/nav_top.php';
    echo '</div>';
    ?>



    <div id="container">
        <?php include '../acp/core/' . $maininc; ?>
    </div>

    <div id="page-sidebar">
        <div id="page-sidebar-inner">
            <?php include '../acp/core/nav_sidebar.php'; ?>
        </div>
    </div>

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
        <p>
            <img src="/themes/administration/images/swiftyedit_icon.svg" alt="se-logo" width="60px"><br>
            <b>SwiftyEdit</b><br>
            copyright © <?php echo date('Y'); ?>, <a href="https://swiftyedit.com/" target="_blank">SwiftyEdit.com</a>
        </p>
        <p class="d-none"><?php echo microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']; ?></p>
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

                    <div class="row">
                        <div class="col-md-9">
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
                        <div class="col-md-3">
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


<script type="text/javascript">

    $(function () {

        /* toggle editor class [mceEditor|plain|aceEditor_html] */
        var editor_mode = localStorage.getItem('editor_mode');
        if (!editor_mode) {
            editor_mode = 'optE1';
            localStorage.setItem("editor_mode", editor_mode);
        }

        $('input[name="optEditor"]').on("change", function () {
            var button = $("input[name='optEditor']:checked").val();
            localStorage.setItem("editor_mode", button);
            switchEditorMode(button);
        });

        if (editor_mode !== 'optE1') {
            switchEditorMode(editor_mode);
        } else {
            <?php echo $tinyMCE_config_contents; ?>
        }

        //setAceEditor();

        $("input[value=" + editor_mode + "]").parent().addClass('active');

        function switchEditorMode(mode) {

            var textEditor = $('textarea[class*=switchEditor]');
            textEditor.removeClass();
            textEditor.removeAttr('style');
            var divEditor = $('.aceCodeEditor');

            if (mode == 'optE1') {
                /* switch to wysiwyg */
                textEditor.addClass('mceEditor form-control switchEditor');
                textEditor.css("display", "flex");
                divEditor.remove();
                /* load configs again */
                <?php echo $tinyMCE_config_contents; ?>
                tinymce.EditorManager.execCommand('mceAddEditor', false, '#textEditor');
            }
            if (mode == 'optE2') {
                /* switch to plain textarea */
                if (tinymce.get().length > 0) {
                    tinymce.EditorManager.execCommand('mceRemoveEditor', true, '#textEditor');
                    $('div.mceEditor').remove();
                    tinymce.remove('.switchEditor');
                    tinymce.remove();
                }
                divEditor.remove();
                textEditor.addClass('plain form-control switchEditor');
                textEditor.css("visibility", "visible");
                textEditor.css("display", "flex");
            }
            if (mode == 'optE3') {
                /* switch to ace editor */
                if (tinymce.get().length > 0) {
                    tinymce.EditorManager.execCommand('mceRemoveEditor', true, '#textEditor');
                    $('div.mceEditor').remove();
                    tinymce.remove();
                }
                textEditor.addClass('aceEditor_code form-control switchEditor');
                setAceEditor();
            }

            $("input[name='optEditor']").parent().removeClass('active');
            $("input[value=" + mode + "]").parent().addClass('active');

        }

        function setAceEditor() {
            if ($('.aceEditor_code').length != 0) {
                $('textarea[class*=switchEditor]').each(function () {

                    var textarea = $(this);
                    var textarea_id = textarea.attr('id');
                    var editDiv = $('<div>', {
                        position: 'absolute',
                        'class': textarea.attr('class') + ' aceCodeEditor'
                    }).insertBefore(textarea);

                    var HTMLtextarea = $('textarea[class*=aceEditor_code]').hide();
                    var aceEditor = ace.edit(editDiv[0]);
                    aceEditor.$blockScrolling = Infinity;
                    aceEditor.getSession().setMode('ace/mode/html');
                    aceEditor.getSession().setValue(textarea.val());
                    aceEditor.setTheme('ace/theme/'+ace_theme);
                    aceEditor.getSession().setUseWorker(false);
                    aceEditor.setShowPrintMargin(false);

                    aceEditor.getSession().on('change', function () {
                        textarea.val(aceEditor.getSession().getValue());
                    });

                });
            }
        }


    });


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