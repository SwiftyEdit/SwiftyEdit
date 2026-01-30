<?php

function show_sysdocs_index() {
    global $Parsedown, $doc_filepath, $languagePackFallback;
    $languagePack = $_SESSION['lang'];

    $docs_root = '../docs/v2/en/*.md';
    if (is_dir('../docs/v2/'.$languagePack)) {
        $docs_root = '../docs/v2/'.$languagePack;
    } else if(is_dir('../docs/v2/'.$languagePackFallback)) {
        $docs_root = '../docs/v2/'.$languagePackFallback;
    }
    $docsfiles = glob($docs_root.'/*.md');

    $parsed_files = [];
    foreach ($docsfiles as $doc) {
        if (str_starts_with(basename($doc), 'tip-')) continue;

        $parsed_file = se_parse_docs_file($doc);
        $parsed_files[] = [
            "title" => $parsed_file['header']['title'],
            "priority" => $parsed_file['header']['priority'],
            "btn" => $parsed_file['header']['btn'],
            "file" => basename($doc),
            "fullpath" => $doc,
            "level" => get_doc_level_universal(basename($doc))
        ];
    }

    usort($parsed_files, function($a, $b) {
        return strnatcmp($a['file'], $b['file']);
    });

    $list = '<div class="card mb-3">';
    $list .= '<div class="card-header"><h6>SwiftyEdit</h6></div>';
    $list .= '<div class="list-group list-group-flush">';

    foreach ($parsed_files as $item) {
        $active = ($doc_filepath === $item['fullpath']) ? 'active' : '';
        $hx_get = '/admin-xhr/docs/read/?file=' . $item['fullpath'];

        $class = 'list-group-level-'.$item['level'];

        $list .= '<button class="list-group-item list-group-item-action '.$class.' ' . $active . '" 
                  hx-get="' . $hx_get . '" hx-target="#helpModal"
                  title="' . $item['title'] . '">';
        $list .= $item['btn'];
        $list .= '</button>';
    }

    $list .= '</div>';
    $list .= '</div>';

    return $list;
}

function get_doc_level_universal($filename) {
    preg_match_all('/(\d{2})-/', $filename, $matches);

    if (empty($matches[1])) {
        return 0;
    }

    $blocks = $matches[1];
    $level = 0;

    // Count non-zero blocks after the first
    for ($i = 1; $i < count($blocks); $i++) {
        if ((int)$blocks[$i] > 0) {
            $level++;
        }
    }

    return $level;
}




function show_themedocs_index() {

    global $Parsedown, $doc_filepath;
    $languagePack = $_SESSION['lang'];
    $list = '';
    $themes = get_all_templates();

    $list = '<div class="card">';
    $list .= '<div class="card-header"><h6>Themes</h6></div>';
    $list .= '<div class="list-group list-group-flush">';
    foreach($themes as $theme) {

        $theme_dir = basename($theme);
        $theme_path = SE_PUBLIC.'/assets/themes/'.$theme_dir.'/';
        $theme_readme_file = $theme_path.'readme.md';
        if(is_file($theme_readme_file)) {

            $active = '';
            if($doc_filepath == $theme_readme_file) {
                $active = 'active';
            }

            $hx_get = '/admin-xhr/docs/read/?file='.$theme_readme_file;
            $hx_target = '#helpModal';

            $list .= '<button class="list-group-item list-group-item-action '.$active.'" hx-get="'.$hx_get.'" hx-target="'.$hx_target.'">';
            $list .= $theme_dir;
            $list .= '</button>';

        }
    }

    $list .= '</div>';
    $list .= '</div>';

    return $list;
}


function show_plugins_index() {
    global $Parsedown, $doc_filepath;
    $languagePack = $_SESSION['lang'];
    $plugins = se_get_all_addons();

    $list = '<div class="card">';
    $list .= '<div class="card-header"><h6>Plugins</h6></div>';
    $list .= '<div class="list-group list-group-flush">';
    foreach($plugins as $k => $v) {

        $plugin_dir = basename($k);
        $plugin_path = SE_ROOT.'plugins/'.$plugin_dir.'/';

        $plugin_readme_file = $plugin_path.'readme.md';
        if(is_file($plugin_readme_file)) {

            $active = '';
            if($doc_filepath == $plugin_readme_file) {
                $active = 'active';
            }

            $hx_get = '/admin-xhr/docs/read/?file='.$plugin_readme_file;
            $hx_target = '#helpModal';

            $list .= '<button class="list-group-item list-group-item-action '.$active.'" hx-get="'.$hx_get.'" hx-target="'.$hx_target.'">';
            $list .= $plugin_dir;
            $list .= '</button>';

       }
    }

    $list .= '</div>';
    $list .= '</div>';

    return $list;
}