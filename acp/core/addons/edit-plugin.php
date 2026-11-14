<?php

/**
 * backend pages of a plugin in /admin/addons/plugin/<plugin>/<page>/, markup in acp/templates/addons/plugin-page.twig
 * the page itself is the plugin's backend/<page>.php, listed in the navigation of its info.json
 *
 * global variables
 * @var string $query - the current url
 * @var array $icon
 * @var object $twig
 */

$path = explode('/', $query);

$addon = preg_replace('/[^a-zA-Z0-9_-]/', '', $path[2] ?? '');
$include_file = preg_replace('/[^a-zA-Z0-9_-]/', '', $path[3] ?? '');
$addon_info_file = SE_PLUGINS.'/'.$addon.'/info.json';

if($addon === '' || !is_file($addon_info_file)) {
    echo $twig->render('addons/plugin-page.twig', ['plugin' => null]);
    return;
}

// used by the pages of the plugins
$this_addon_root_url = '/admin/addons/plugin/'.$addon;
$this_addon_root = SE_PLUGINS.'/'.$addon;

$addon_info = json_decode((string) file_get_contents($addon_info_file), true);
$addon_info = is_array($addon_info) ? $addon_info : [];
$addon_lang = se_return_addon_translations($addon);

$navigation = [];
foreach($addon_info['navigation'] ?? [] as $nav) {
    $navigation[] = [
        'url' => $this_addon_root_url.'/'.$nav['file'].'/',
        'text' => $addon_lang[$nav['text']] ?? $nav['text'],
        'active' => ($include_file == $nav['file'])
    ];
}

// the help opens the first doc (docs/{lang}/*.md by priority, else readme.md)
require_once __DIR__.'/../docs/functions.php';
$help_link = '';
$help_files = se_docs_context_files('plugin', $addon);
if(!empty($help_files)) {
    $help_link = se_print_plugin_docs_link($addon, basename($help_files[0]['path']), $icon['question'], null, 'btn btn-sm btn-default rounded-circle');
}

// the page of the plugin prints its own markup, it becomes the content of the card
$content = '';
$addon_include_file = $this_addon_root.'/backend/'.$include_file.'.php';
if($include_file !== '' && is_file($addon_include_file)) {
    ob_start();
    include $addon_include_file;
    $content = (string) ob_get_clean();
}

echo $twig->render('addons/plugin-page.twig', [
    'plugin' => [
        'name' => $addon_info['addon']['name'] ?? $addon,
        // the name links to the first page of the plugin
        'start_url' => $this_addon_root_url.'/'.($addon_info['navigation'][0]['file'] ?? 'start').'/',
        'help_link' => $help_link
    ],
    'navigation' => $navigation,
    'content' => $content
]);
