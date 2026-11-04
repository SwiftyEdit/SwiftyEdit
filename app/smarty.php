<?php

/**
 * init smarty, select the theme/layout
 *
 * global variables
 * @var array $se_settings bootstrap.php
 * @var array $page_contents routing.php
 * @var string $swifty_slug routing.php
 * @var string $mod_slug routing.php
 * @var string $themes_path config.php
 * @var string $hidden_csrf_token bootstrap.php
 */

use Smarty\Smarty;

$smarty = new Smarty;
$smarty->setErrorReporting(0);
$smarty->setCompileDir('../data/cache/templates_c/');

$smarty->registerPlugin('modifier', 'htmlentities', 'htmlentities');
$smarty->registerPlugin('modifier', 'strtolower', 'strtolower');
$smarty->registerPlugin('modifier', 'trim', 'trim');
$smarty->registerPlugin('function', 'img', 'se_smarty_function_img');
$smarty->registerPlugin('block', 'plugin', 'se_smarty_block_plugin');

$cache_id = md5($swifty_slug.$mod_slug);

if($se_settings['smarty_compile_check'] == 1) {
    $smarty->setCompileCheck(Smarty::COMPILECHECK_ON);
} else {
    $smarty->setCompileCheck(Smarty::COMPILECHECK_OFF);
}

/* reset of the user-defined theme */
if(isset($_POST['reset_theme'])) {
    unset($_SESSION['prefs_template'],$_SESSION['prefs_template_stylesheet']);
}

/**
 * $prefs_usertemplate - off|on|overwrite
 * this option is intended for theme developers
 */

if($se_settings['usertemplate'] == 'on' OR $se_settings['usertemplate'] == 'overwrite') {

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

        $set_theme_folder = $set_theme_stylesheet[2] ?? '';
        $set_stylesheet = $set_theme_stylesheet[4] ?? '';

        if(is_dir($themes_path."/$set_theme_folder")) {
            $_SESSION['prefs_template'] = sanitizeUserInputs($set_theme_folder);
        }

        if(is_file($themes_path."/$set_theme_folder/css/$set_stylesheet")) {
            $_SESSION['prefs_template_stylesheet'] = sanitizeUserInputs($set_stylesheet);
        }
    }


    if(($_SESSION['prefs_template'] ?? '') != '') {
        $se_settings['template'] = $_SESSION['prefs_template'];
    }

    if(($_SESSION['prefs_template_stylesheet'] ?? '') != '') {
        $se_settings['template_stylesheet'] = $_SESSION['prefs_template_stylesheet'];
    }

}

$se_page_theme = se_resolve_page_theme($page_contents, $se_settings, $themes_path);
$se_template = $se_page_theme['template'];
$se_template_layout = $se_page_theme['layout'];
$se_template_stylesheet = $se_page_theme['stylesheet'];

$smarty->assign('hidden_csrf_token', "$hidden_csrf_token", true);

$smarty->assign('se_template', $se_template);
$smarty->assign('se_template_layout', $se_template_layout);

if($se_template_stylesheet != '') {
    $smarty->assign('se_template_stylesheet', $se_template_stylesheet);
}

$smarty->assign('se_assets_version', se_get_theme_assets_version($themes_path, $se_template));

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