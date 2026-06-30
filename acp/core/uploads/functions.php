<?php

function show_sort_arrow() {
    global $icon,$lang;
    if($_SESSION['sort_direction'] == 'ASC') {
        $ic = '<span title="'.$lang['ascending'].'"><i class="bi bi-caret-up-fill"></i></span>';
    } else {
        $ic = '<span title="'.$lang['descending'].'"><i class="bi bi-caret-down-fill"></i></span>';
    }
    return $ic;
}

function delete_folder($dir) {

    $dir = se_filter_filepath($dir);

    // confine deletion to a subfolder of the public directory; reject traversal
    // attempts and refuse to operate on the public root itself
    $resolved = se_resolve_within(SE_PUBLIC, $dir);
    if ($resolved === false || $resolved === rtrim(SE_PUBLIC, '/')) {
        return false;
    }

    $delete_folder = SE_PUBLIC.'/'.$dir;
    $files = array_diff(scandir($delete_folder), array('.','..'));
    foreach ($files as $file) {
        if(is_dir("$dir/$file")) {
            delete_folder("$dir/$file");
        } else {
            unlink("$dir/$file");
            $filename = $dir.'/'.$file;
            $filename = str_replace(SE_PUBLIC, '', $filename);
            $filename = str_replace('assets/', '../', $filename);
            se_delete_media_data("$filename");
        }

    }
    return rmdir($delete_folder);
}