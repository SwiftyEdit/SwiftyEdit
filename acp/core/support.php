<?php

/**
 * show support offcanvas
 * check if is installed/activated support.mod
 * if not, show open source docs
 */
//error_reporting(E_ALL ^E_NOTICE);
$support_addon = SE_CONTENT.'/modules/support.mod';
$show_open_source_docs = true;

if(is_dir($support_addon)) {
    /* support.mod is installed */
    $show_open_source_docs = false;
    $get_se_addons = se_get_addons($t='module');

    if(is_array($get_se_addons)) {
        $search_key = array_column($get_se_addons, 'addon_dir');
        $found_key = array_search('support.mod', $search_key);

        if ($found_key !== false) {
            include $support_addon . '/index.php';
        } else {
            /* support.mod is not activated */
            echo '<div class="alert alert-info">';
            echo $lang['msg_support_addon_not_activated'];
            echo '</div>';
            $show_open_source_docs = true;
        }
    }
}


if($show_open_source_docs == true) {
    echo '<div class="card">';
    echo '<div class="card-body">';

    $Parsedown = new Parsedown();
    $docsfiles = glob('./docs/'.$languagePack.'/*.md');
    echo '<div class="list-group mb-3">';
    foreach($docsfiles as $doc) {

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

    foreach($sorted_parsed_files as $k => $v) {

        $data_file = str_replace("./docs/$languagePack/",'',$sorted_parsed_files[$k]['file']);

        echo '<button 
        type="button"
        class="show-doc list-group-item list-group-item-action"
        data-bs-toggle="modal" 
        data-bs-target="#infoModal"
        data-file="'.$data_file.'" 
        data-token="'.$_SESSION['token'].'">';
        echo $sorted_parsed_files[$k]['btn'];
        echo '</button>';
    }

    echo '</div>';

    echo $lang['msg_community_edition'];

    echo '<ul>';
    echo '<li><a href="https://SwiftyEdit.org" title="" target="_blank">SwiftyEdit.org</a></li>';
    echo '<li><a href="https://github.com/SwiftyEdit/" title="" target="_blank">GitHub.com</a></li>';
    echo '</ul>';

    echo '</div>';
    echo '</div>';
}
