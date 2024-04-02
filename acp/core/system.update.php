<?php

/**
 * SwiftyEdit Update Script
 * Get ZIP Files from SwiftyEdit.net
 *
 * Global variables
 * @var array $lang language file
 */


set_time_limit (0);

//prohibit unauthorized access
require 'core/access.php';
include 'updatelist.php';

define('INSTALLER', TRUE);
include '../install/php/functions.php';

if(!extension_loaded('zip')) {
    echo '<div class="alert alert-warning mb-4">The required extension <strong>ZIP</strong> is not installed</div>';
}

$_SESSION['protocol'] = '';
$_SESSION['errors_cnt'] = 0;

/* build an array from all php files in folder ../install/contents */
$all_tables = glob("../install/contents/*.php");

$remote_versions_file = file_get_contents("https://swiftyedit.net/releases/versions.json");
$remote_versions_array = json_decode($remote_versions_file,true);


echo '<fieldset>';
echo '<legend>'.$lang['nav_btn_update'].'</legend>';

compare_versions();

if(isset($_GET['a']) && $_GET['a'] == 'start') {

    if(isset($_GET['source']) && $_GET['source'] == 'alpha') {
        $remote_file = $remote_versions_array['version']['alpha']['file'];
    }
    if(isset($_GET['source']) && $_GET['source'] == 'beta') {
        $remote_file = $remote_versions_array['version']['beta']['file'];
    }
    if(isset($_GET['source']) && $_GET['source'] == 'stable') {
        $remote_file = $remote_versions_array['version']['stable']['file'];
    }

    start_update();
}

echo '</fieldset>';


if(isset($_GET['a']) && $_GET['a'] == 'start') {
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
    echo '<div class="alert alert-info">'.$lang['update_msg_post_install'].'</div>';
}

/**
 * start the update
 * 1. load the zip file from swiftyedit.net
 * 2. mkdir acp/update and acp/update/extract
 * 		copy the zip file into /acp/update and extract the files
 * 3. copy the file maintenance.html to the root (starts the update modus in frontend)
 * 4. copy the files to their destination
 * 5. run the updatescript and check up the database
 * 6. delete maintenance.html from root - (ends the update modus in frontend)
 *
 */


function start_update() {

    global $remote_file;
    global $se_content_files;

    $get_file = $remote_file;
    $source_file = 'https://swiftyedit.net/releases/files/'.$remote_file;

    mkdir("update", 0777);
    mkdir("update/extract", 0777);

    if(is_dir("update")) {
        copy("$source_file","./update/$get_file");
    }

    $archive = new ZipArchive;

    if($archive->open("update/$get_file") === TRUE) {
        $archive->extractTo('update/extract');
        $archive->close();
    } else {
        echo '<div class="alert alert-warning mb-4">Error: can not open zip file</div>';
    }

    copy('../install/maintenance.html', '../maintenance.html');

    move_new_files();

    if(!is_array($se_content_files)) {
        /* update single file database */
        update_database();
    } else {
        /* update multisite database */
        for($i=0;$i<count($se_content_files);$i++) {
            $db = 'content/SQLite/'.$se_content_files[$i]['file'];
            update_database($db);
        }
    }
    remove_old_files();

    /**
     * remove the update and ../install directory
     */

    rmdir_recursive("update");
    unlink("../maintenance.html");

}



/**
 * get the files from the extracted zip file
 * and copy them to their destination
 */

function move_new_files() {

    global $remote_file;
    $cnt_errors = 0;

    $get_file = basename("$remote_file",".zip");

    if(is_dir("update/extract/$get_file"))	{
        $new_files = scandir_recursive("update/extract/$get_file");
    } else {
        echo '<div class="alert alert-danger">No Source found: '. $get_file .'</div>';
    }


    /* at first, the install folder */
    copy_recursive("update/extract/$get_file/install","../install");

    /* payment addons */
    copy_recursive("update/extract/$get_file/content/modules/se_invoice.pay","../content/modules/se_invoice.pay");
    copy_recursive("update/extract/$get_file/content/modules/se_cash.pay","../content/modules/se_cash.pay");

    /* now copy the other files and directories */
    foreach($new_files as $value) {

        $i++;

        if(preg_match("#\/install\/#i", "$value")) {
            continue;
        }

        if(preg_match("#\/content\/#i", "$value")) {
            continue;
        }

        if(preg_match("#\/modules\/#i", "$value")) {
            continue;
        }

        if(preg_match("#\/.github\/#i", "$value")) {
            continue;
        }

        if(preg_match("#\/.idea\/#i", "$value")) {
            continue;
        }

        if(substr(basename($value), 0,1) == ".") { continue;}
        if($value === '.' || $value === '..') {continue;}
        if(basename($value) == "README.md") { continue;}
        if(basename($value) == "robots.txt") { continue;}
        if(basename($value) == "_htaccess") { continue;}
        if(basename($value) == "CODE_OF_CONDUCT.md") { continue;}


        /**
         * copy files from 'update/extract/*'
         */
        $target = '../' . substr($value, strlen("update/extract/$get_file/"));
        $status = copy_recursive("$value","$target");

    }

    $_SESSION['errors_cnt'] = $cnt_errors;

}



/**
 * remove old versions or unused files
 * $remove_files from core/updatelist
 */

function remove_old_files() {
    global $remove_files;

    foreach ($remove_files as $file) {
        if(is_file("$file")) {
            unlink("$file");
        }
    }
}



/**
 * compare installed and remote version
 */

function compare_versions() {

    global $lang;
    global $remote_versions_array;
    global $se_base_url;
    global $icon;
    global $se_environment;

    /**
     * from versions.php
     * @var string $se_version_date fe: 2022-10-07
     * @var string $se_version_title fe: beta 1.0
     * @var string $se_version_build fe: 123
     */

    if(is_file("versions.php")){
        include 'versions.php';
    } else {
        $se_version_build = '';
    }

    echo '<div class="row">';
    echo '<div class="col-6">';
    /* installed version */
    echo '<div class="card h-100">';
    echo '<div class="card-header">'.$icon['database'].'  '. $se_base_url .'</div>';
    echo '<div class="card-body">';
    echo '<p>Version: '.$se_version_title.' (Build '.$se_version_build.')</p>';
    echo '<p>'.$se_version_date.'</p>';
    echo '</div>';
    echo '</div>';

    echo '</div>';
    echo '<div class="col-6">';
    /* available versions */

    echo '<div class="card h-100">';
    echo '<div class="card-header">'.$icon['server'].'  SwiftyEdit Server</div>';
    echo '<ul class="list-group list-group-flush">';
    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
    echo 'Version: '.$remote_versions_array['version']['stable']['title'] .' ';
    echo 'Build: '.$remote_versions_array['version']['stable']['build'] .' ';
    echo $remote_versions_array['version']['stable']['date'];
    $update_stable = '';
    if($se_version_build < $remote_versions_array['version']['stable']['build']) {
        echo '<a class="btn btn-success ml-auto" href="?tn=system&sub=update&a=start&source=stable">'.$lang['btn_choose_this_update'].'</a>';
        $update_stable = $lang['update_msg_stable'];
    }
    echo '</li>';
    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
    echo 'Version: '.$remote_versions_array['version']['beta']['title'] .' ';
    echo 'Build: '.$remote_versions_array['version']['beta']['build'] .' ';
    echo $remote_versions_array['version']['beta']['date'];
    $update_beta = '';
    if($se_version_build < $remote_versions_array['version']['beta']['build']) {
        echo '<a class="btn btn-danger btn-sm ml-auto" href="?tn=system&sub=update&a=start&source=beta">'.$lang['btn_choose_this_update'].'</a>';
        $update_beta = $lang['update_msg_beta'];
    }
    echo '</li>';
    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
    echo 'Version: '.$remote_versions_array['version']['alpha']['title'] .' ';
    echo 'Build: '.$remote_versions_array['version']['alpha']['build'] .' ';
    echo $remote_versions_array['version']['alpha']['date'];
    $update_alpha = '';
    if($se_version_build < $remote_versions_array['version']['alpha']['build']) {
        echo '<a class="btn btn-danger btn-sm ml-auto" href="?tn=system&sub=update&a=start&source=alpha">'.$lang['btn_choose_this_update'].'</a>';
        $update_alpha = $lang['update_msg_alpha'];
    }
    if($se_environment == 'd') {
        echo '<a class="btn btn-danger btn-sm ml-auto" href="?tn=system&sub=update&a=start&source=alpha">install alpha again</a>';
    }

    echo '</li>';
    echo '</ul>';
    echo '</div>';


    echo '</div>';
    echo '</div>';
    echo '<hr class="shadow-line">';

    if($update_stable == '') {
        echo '<div class="alert alert-success">';
        echo $icon['check_circle'].' '.$lang['update_msg_no_update_available'];
        echo '</div>';
    } else {
        echo '<div class="alert alert-success">';
        echo $icon['info_circle'].' '.$lang['update_msg_update_available'];
        echo '</div>';
    }

    if($update_beta != '') {
        echo '<div class="alert alert-info">';
        echo $icon['info_circle'].' '.$update_beta;
        echo '</div>';
    }
    if($update_alpha != '') {
        echo '<div class="alert alert-danger">';
        echo $icon['info_circle'].' '.$update_alpha;
        echo '</div>';
    }

}


/**
 * returns all files and directories
 * return array()
 */

function scandir_recursive($dir) {
    $root = scandir($dir);
    foreach($root as $value) {
        if($value === '.' || $value === '..') {continue;}
        $result[]="$dir/$value";
        if(is_dir("$dir/$value")) {
            foreach(scandir_recursive("$dir/$value") as $value) {
                $result[]=$value;
            }
        }
    }
    if(is_array($result)) {
        $result = array_filter($result);
    }

    return $result;
}


/**
 * copy/move directory with its including contents
 */

function copy_recursive($source, $target) {

    if(is_dir($source)) {
        if(!is_dir("$target")) {
            $_SESSION['protocol'] .= "missing: $target <|>";
            mkdir_recursive($target,0777);
        }

        $dir = dir($source);
        while(FALSE !== ($entry = $dir->read())) {

            if($entry == '.' || $entry == '..') { continue; }

            $sub = $source . '/' . $entry;

            if(is_dir($sub)) {
                chmod("$sub", 0755);
                copy_recursive($sub, $target . '/' . $entry);
                //continue;
            }
            copy($sub, $target . '/' . $entry);
        }

        $dir->close();
    } else {
        chmod("$target", 0777);
        unlink("$target");
        if(copy($source, $target)) {
            $_SESSION['protocol'] .= '<b>copied:</b> '.$target.'<|>';
        } else {
            $errors = error_get_last();
            $_SESSION['protocol'] .= '<b class="text-danger">ERROR:</b> '.$errors['type']. '</b> ' . $errors['message'].'<|>';
            $_SESSION['errors_cnt']++;
            return $errors['message'];
        }
    }
}



/**
 * Update the database
 */

function update_database() {

    /**
     * build an array from all php files in folder ../install/contents
     * @var string $database database name for example content
     * @var string $table_name name of the table
     * @var string $table_type 'virtual' for virtual tables
     * @var array $cols
     */

    $all_tables = glob("../install/contents/*.php");

    for($i=0;$i<count($all_tables);$i++) {

        unset($db_path,$table_name,$database,$table_type);

        include $all_tables[$i]; // returns $cols and $table_name

        $is_table = table_exists("$database","$table_name");

        if($is_table < 1) {
            if($table_type == 'virtual') {
                add_virtual_table("$database","$table_name",$cols);
            } else {
                add_table("$database","$table_name",$cols);
            }

            $_SESSION['protocol'] .= '<b class="text-success">new table:</b> '.$table_name.' in '.$database.'<|>';
        }


        $existing_cols = get_columns("$database","$table_name");


        foreach ($cols as $k => $v) {

            if(!array_key_exists("$k", $existing_cols)) {
                //update_table -> column, type, table, database
                update_table("$k","$cols[$k]","$table_name","$database");
                $_SESSION['protocol'] .= '<b class="text-success">new column:</b> '.$k.' in table '.$table_name.'<|>';
            }

        } // eo foreach

        /* updates are done, check all columns again */
        $existing_cols = get_columns("$database","$table_name");

    }
}




/**
 * delete directory (recursive)
 */

function rmdir_recursive($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if(filetype($dir."/".$object) == "dir") {
                    rmdir_recursive($dir."/".$object);
                } else {
                    unlink($dir."/".$object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

/**
 * create directory (recursive)
 */

function mkdir_recursive($dir, $chmod=0777){
    $dirs = explode('/', $dir);
    $directory='';
    foreach ($dirs as $part) {
        $directory .= $part.'/';
        if(!is_dir($directory) && strlen($directory)>0) {
            mkdir($directory, $chmod);
            chmod("$directory", $chmod);
            $_SESSION['protocol'] .= "created: $directory <|>";
        }
    }
}