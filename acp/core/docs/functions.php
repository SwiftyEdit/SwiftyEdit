<?php


function show_sysdocs_index() {

    global $Parsedown, $doc_filepath;
    $languagePack = $_SESSION['lang'];

    $docs_root = '../acp/docs/en/*.md';
    if(is_dir('../acp/docs/'.$languagePack)) {
        $docs_root = '../acp/docs/'.$languagePack;
    }
    $docsfiles = glob($docs_root.'/*.md');


    foreach($docsfiles as $doc) {
        // skip tooltips
        if (str_starts_with(basename($doc), 'tip-')) {
            continue;
        }

        $parsed_file = se_parse_docs_file($doc);
        $parsed_files[] = [
            "title" => $parsed_file['header']['title'],
            "priority" => $parsed_file['header']['priority'],
            "btn" => $parsed_file['header']['btn'],
            "file" => $doc
        ];
    }

    $sorted_parsed_files = se_array_multisort($parsed_files, 'priority', SORT_ASC);

    $list = '<div class="card mb-3">';
    $list .= '<div class="card-header"><h6>SwiftyEdit</h6></div>';
    $list .= '<div class="list-group list-group-flush">';
    foreach($sorted_parsed_files as $k => $v) {

        $active = '';
        if($doc_filepath == $sorted_parsed_files[$k]['file']) {
            $active = 'active';
        }

        $hx_get = '/admin-xhr/docs/read/?file='.$sorted_parsed_files[$k]['file'];
        $hx_target = '#helpModal';

        $list .= '<button class="list-group-item list-group-item-action '.$active.'" hx-get="'.$hx_get.'" hx-target="'.$hx_target.'">';
        $list .= $sorted_parsed_files[$k]['btn'];
        $list .= '</button>';

    }
    $list .= '</div>';
    $list .= '</div>';

    return $list;
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