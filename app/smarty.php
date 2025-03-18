<?php

// init smarty
use Smarty\Smarty;

$smarty = new Smarty;
$smarty->setErrorReporting(0);
$smarty->setCompileDir('../data/cache/templates_c/');
$smarty->setCacheDir('../data/cache/cache/');

$smarty->registerPlugin('modifier', 'htmlentities', 'htmlentities');
$smarty->registerPlugin('modifier', 'strtolower', 'strtolower');

$cache_id = md5($swifty_slug.$mod_slug);

if($se_prefs['prefs_smarty_cache'] == 1) {
    $smarty->setCaching(Smarty::CACHING_LIFETIME_CURRENT);
    if(is_numeric($se_prefs['prefs_smarty_cache_lifetime'])) {
        $smarty->setCacheLifetime($se_prefs['prefs_smarty_cache_lifetime']);
    }
} else {
    $smarty->setCaching(Smarty::CACHING_OFF);
}

if($se_prefs['prefs_smarty_compile_check'] == 1) {
    $smarty->compile_check = true;
} else {
    $smarty->compile_check = false;
}

/* reset of the user-defined theme */
if(isset($_POST['reset_theme'])) {
    unset($_SESSION['prefs_template'],$_SESSION['prefs_template_stylesheet']);
}

/**
 * $prefs_usertemplate - off|on|overwrite
 * this option is intended for theme developers
 */

if($se_prefs['prefs_usertemplate'] == 'on' OR $se_prefs['prefs_usertemplate'] == 'overwrite') {

    /* set the theme - defined by the user */
    if(isset($_POST['set_theme'])) {
        $set_theme = $themes_path.'/'.sanitizeUserInputs($_POST['set_theme']);
        if(is_dir($set_theme)) {
            $_SESSION['prefs_template'] = sanitizeUserInputs($_POST['set_theme']);
            unset($_SESSION['prefs_template_stylesheet']);
        }
    }

    /**
     * set the theme and stylesheet - defined by the user
     * example: $_POST['set_theme_stylesheet'] = './styles/default/css/dark.css';
     */

    if(isset($_POST['set_theme_stylesheet'])) {
        $set_theme_stylesheet = explode("/",$_POST['set_theme_stylesheet']);

        $set_theme_folder = $set_theme_stylesheet[2];
        $set_stylesheet = $set_theme_stylesheet[4];

        if(is_dir($themes_path."/$set_theme_folder")) {
            $_SESSION['prefs_template'] = sanitizeUserInputs($set_theme_folder);
        }

        if(is_file($themes_path."/$set_theme_folder/css/$set_stylesheet")) {
            $_SESSION['prefs_template_stylesheet'] = sanitizeUserInputs($set_stylesheet);
        }
    }


    if($_SESSION['prefs_template'] != '') {
        $se_prefs['prefs_template'] = $_SESSION['prefs_template'];
    }

    if($_SESSION['prefs_template_stylesheet'] != '') {
        $se_prefs['prefs_template_stylesheet'] = $_SESSION['prefs_template_stylesheet'];
    }

}

// default template
$se_template = $se_prefs['prefs_template'] ?: 'default';
$se_template_layout = $se_prefs['prefs_template_layout'] ?: 'layout_default.tpl';
$se_template_stylesheet = '';
if(isset($se_prefs['prefs_template_stylesheet'])) {
    $se_template_stylesheet = $se_prefs['prefs_template_stylesheet'];
}

if($page_contents['page_template'] == "use_standard") {
    $se_template = $se_prefs['prefs_template'] ?: 'default';
}

if($page_contents['page_template_layout'] == "use_standard") {
    $se_template_layout = $se_prefs['prefs_template_layout'] ?: 'layout_default.tpl';
}

/* page has its own theme/template */
if(is_dir($themes_path.'/'.$page_contents['page_template'].'/templates/')) {
    $se_template = $page_contents['page_template'];
    $se_template_layout = $page_contents['page_template_layout'];
    $se_template_stylesheet = $page_contents['page_template_stylesheet'];

    if($se_prefs['prefs_usertemplate'] == 'overwrite') {
        /* the user theme has the same tpl file, so we can overwrite */
        if(is_file($themes_path.'/'.$_SESSION['prefs_template'].'/templates/'.$page_contents['page_template_layout'])) {
            $se_template = $_SESSION['prefs_template'];
            $se_template_layout = $page_contents['page_template_layout'];
            //$se_template_stylesheet = $se_template_stylesheet;
        }
    }
}

$se_template = basename($se_template);
$se_template_layout = basename($se_template_layout);
$se_template_stylesheet = basename($se_template_stylesheet);

$smarty->assign('hidden_csrf_token', "$hidden_csrf_token", true);

$smarty->assign('se_template', $se_template);
$smarty->assign('se_template_layout', $se_template_layout);

if($se_template_stylesheet != '') {
    $smarty->assign('se_template_stylesheet', $se_template_stylesheet);
}

if(is_file($themes_path."/$se_template/php/index.php")) {
    include $themes_path.'/'.$se_template.'/php/index.php';
}

$smart_template_dirs = array();

if($se_template != 'default') {
    $smart_template_dirs[] = $themes_path.'/'.$se_template.'/templates/';
    $smart_template_dirs[] = $themes_path.'/default/templates/';
} else {
    $smart_template_dirs[] = $themes_path.'/default/templates/';
}

//$smarty->template_dir = 'styles/'.$se_template.'/templates/';
$smarty->setTemplateDir($smart_template_dirs);