<?php

/**
 * options and readme of a theme in /admin/addons/theme/<theme>/, markup in acp/templates/addons/theme-options.twig
 * the options are defined by the theme in php/options.php, saved by the addons data-writer
 *
 * global variables
 * @var string $query - the current url
 * @var object $twig
 */

$path = explode('/', $query);
$theme = preg_replace('/[^a-zA-Z0-9_-]/', '', $path[2] ?? '');
$theme_root = SE_THEMES.'/'.$theme;

if($theme === '' || $theme === 'administration' || !is_dir($theme_root)) {
    echo $twig->render('addons/theme-options.twig', ['theme' => null]);
    return;
}

// saved values, stored html-encoded (sanitizeUserInputs) - Twig escapes on output
$saved_values = array_column(se_get_theme_options($theme), 'theme_value', 'theme_label');

$fields = [];
foreach(se_get_theme_option_fields($theme) as $key => $option) {
    $value = html_entity_decode((string) ($saved_values['theme_'.$key] ?? ''), ENT_QUOTES, 'UTF-8');
    $options = [];
    foreach($option['options'] ?? [] as $option_value => $option_label) {
        $options[] = ['value' => $option_value, 'label' => $option_label, 'selected' => ((string) $option_value === $value)];
    }
    $fields[] = [
        'name' => 'theme_'.$key,
        'label' => $option['label'] ?? $key,
        'type' => $option['type'] ?? 'text',
        'value' => $value,
        'options' => $options
    ];
}

// the readme is part of the installed theme
$readme = '';
if(is_file($theme_root.'/readme.md')) {
    $readme = (new Parsedown())->text((string) file_get_contents($theme_root.'/readme.md'));
}

echo $twig->render('addons/theme-options.twig', [
    'theme' => $theme,
    'fields' => $fields,
    'readme' => $readme,
    'writer_uri' => '/admin-xhr/addons/write/'
]);
