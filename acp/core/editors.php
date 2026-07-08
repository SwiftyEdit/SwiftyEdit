<?php

/**
 * Resolve the active theme's editor content styling and the TinyMCE helper
 * endpoints. The editor plugins (public/assets/editors/*) read these values
 * from their init scripts so the editing surface matches the front-end theme
 * currently being edited.
 *
 * Exposes to the ACP scope:
 * @var string $editor_css_url          URL of the theme's editor content CSS
 * @var string $editor_img_list_url     TinyMCE image-list endpoint of the theme
 * @var string $editor_link_list_url    TinyMCE link-list endpoint of the theme
 * @var string $tinyMCE_config_override optional full TinyMCE config shipped by
 *                                      the active theme; when present it
 *                                      replaces the plugin's built-in config
 *
 * @var array $se_prefs global preferences
 */

$editor_tpl_folder = $se_prefs['prefs_template'];

if (isset($page_template)) {
    $editor_tpl_folder = $page_template;
}

if ($editor_tpl_folder == 'use_standard') {
    $editor_tpl_folder = $se_prefs['prefs_template'];
}

/**
 * Locate the editor content stylesheet in the following order:
 * 1. ../themes/.../dist/
 * 2. ../themes/.../config/
 * 3. ../themes/.../css/
 */

$theme_src = '../public/assets/themes/' . $editor_tpl_folder . '/';

if (is_file($theme_src . 'dist/editor.css')) {
    $editor_styles = $theme_src . 'dist/editor.css';
} else if (is_file($theme_src . 'config/editor.css')) {
    $editor_styles = $theme_src . 'config/editor.css';
} else {
    $editor_styles = $theme_src . 'css/editor.css';
}

if (!is_file($editor_styles)) {
    $editor_tpl_folder = 'default';
    $editor_styles = '../public/assets/themes/default/dist/editor.css';
}

// filesystem path -> web URL (/themes/<theme>/... via the .htaccess rewrite)
$editor_css_url = str_replace('../public/assets/themes/', '/themes/', $editor_styles);

// TinyMCE image/link list endpoints of the active theme, falling back to default
$editor_img_list_url  = '/themes/' . $editor_tpl_folder . '/php/tinymce-images.php';
$editor_link_list_url = '/themes/' . $editor_tpl_folder . '/php/tinymce-links.php';

if (!is_file('../public/assets/themes/' . $editor_tpl_folder . '/php/tinymce-images.php')) {
    $editor_img_list_url = '/themes/default/php/tinymce-images.php';
}
if (!is_file('../public/assets/themes/' . $editor_tpl_folder . '/php/tinymce-links.php')) {
    $editor_link_list_url = '/themes/default/php/tinymce-links.php';
}

/**
 * Optional per-theme TinyMCE config override.
 *
 * If the active theme ships a full tinyMCE_config.js (a
 * "$(function(){ $('textarea.mceEditor, ...').tinymce({ ... }); })" script) it
 * replaces the TinyMCE plugin's built-in config. Resolved from the active theme
 * only (dist > config > js); no default-theme fallback, so it stays optional.
 */
$tinyMCE_config_override = '';
if (is_file($theme_src . 'dist/tinyMCE_config.js')) {
    $tinyMCE_config_override = file_get_contents($theme_src . 'dist/tinyMCE_config.js');
} else if (is_file($theme_src . 'config/tinyMCE_config.js')) {
    $tinyMCE_config_override = file_get_contents($theme_src . 'config/tinyMCE_config.js');
} else if (is_file($theme_src . 'js/tinyMCE_config.js')) {
    $tinyMCE_config_override = file_get_contents($theme_src . 'js/tinyMCE_config.js');
}
