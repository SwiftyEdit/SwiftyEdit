<?php

const INSTALLER = TRUE;
require_once __DIR__.'/functions.php';
require_once '../install/php/functions.php';

// helpers
if(isset($_POST['helper_update_table'])) {
    include __DIR__.'/helpers_update_data.php';
}

// load data from swiftyedit.net
if(isset($_POST['load_update_data'])) {

    if($_POST['load_update_data'] == 'alpha') {
        $remote_file = basename($_POST['file']);
    }

    $source_file = 'https://swiftyedit.net/releases/v2/files/'.$remote_file;

    $download_dir = __DIR__.'/download/';
    $extract_dir = __DIR__.'/download/extract';
    mkdir("$extract_dir",0777,true);

    if(is_dir("$extract_dir")) {
        copy("$source_file","$download_dir/$remote_file");
    }

    $archive = new ZipArchive;
    if($archive->open("$download_dir/$remote_file") === TRUE) {
        $archive->extractTo("$extract_dir");
        $archive->close();
        echo '<div class="alert alert-info">Download complete. File: '.basename($remote_file).'</div>';
    } else {
        echo '<div class="alert alert-warning">Error: can not open zip file</div>';
    }
    header( "HX-Trigger: update_downloads_list");
    exit;
}

// delete downloaded update files
if(isset($_POST['remove_download'])) {

    $download_dir = __DIR__.'/download/';
    $remove_dir_name = basename($_POST['remove_download']);

    $remove_dir = $download_dir.'extract/'.$remove_dir_name;
    $remove_zip_file = $download_dir.$remove_dir_name.'.zip';

    if(is_file("$remove_zip_file")) {
        unlink("$remove_zip_file");
    }

    if(is_dir("$remove_dir")) {
        rmdir_recursive("$remove_dir");
    }
    header( "HX-Trigger: update_downloads_list");
    exit;
}

// install update, copy new files
if(isset($_POST['install_update'])) {

    $source_directory = basename($_POST['install_update']);
    $_SESSION['protocol'] = '';
    move_new_files($source_directory);
    update_database();

    echo '<div style="height:350px;overflow:auto;margin:0;" class="well well-sm">';
    echo '<h3>ERRORS: '.$_SESSION['errors_cnt'].'</h3>';
    $protocol = explode('<|>', $_SESSION['protocol']);
    $protocol = array_filter($protocol);
    echo '<ul>';
    foreach($protocol as $v) {
        echo '<li>'.$v. '</li>';
    }
    echo '</ul>';
    echo '</div>';
    $installer_url = '<a href="/install/">/install/</a>';
    echo '<div class="alert alert-info">'.str_replace("{url}","$installer_url",$lang['update_msg_post_install']).'</div>';

    exit;

}