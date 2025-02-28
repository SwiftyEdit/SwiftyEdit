<?php

function compare_versions() {

    global $lang, $icon, $se_environment, $remote_versions_array, $se_base_url;

    $hx_writer_url = '/admin/update/write/';
    $hx_vals = ['csrf_token' => $_SESSION['token']];

    // read version.json
    $version_file = file_get_contents(SE_ROOT.'version.json');
    $se_version = json_decode($version_file, true);

    echo '<ul class="list-group list-group-flush mb-1">';
    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
    echo 'Version: '.$remote_versions_array['version']['stable']['title'] .'<br>';
    echo 'Build: '.$remote_versions_array['version']['stable']['build'] .'<br>';
    echo 'Date: ' .$remote_versions_array['version']['stable']['date'];
    $update_stable = '';
    if($se_version['build'] < $remote_versions_array['version']['stable']['build']) {
        $filename_stable = basename($remote_versions_array['version']['stable']['file']);
        $hx_vals += ["file" => "$filename_stable"];
        echo '<button class="btn btn-default btn-sm" hx-post="'.$hx_writer_url.'" hx-vals=\''.json_encode($hx_vals).'\' hx-target="#updateResponse" hx-indicator="#updateIndicator" hx-swap="outerHTML" name="load_update_data" value="stable">'.$lang['btn_choose_this_update'].'</button>';
        $update_stable = $lang['update_msg_stable'];
    } else {
        echo '<button class="btn btn-default" disabled>'.$lang['btn_choose_this_update'].'</button>';
    }
    echo '</li>';
    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
    echo 'Version: '.$remote_versions_array['version']['beta']['title'] .'<br>';
    echo 'Build: '.$remote_versions_array['version']['beta']['build'] .'<br>';
    echo 'Date: '.$remote_versions_array['version']['beta']['date'];
    $update_beta = '';
    if($se_version['build'] < $remote_versions_array['version']['beta']['build']) {
        $filename_beta = basename($remote_versions_array['version']['beta']['file']);
        $hx_vals += ["file" => "$filename_beta"];
        echo '<button class="btn btn-default btn-sm" hx-post="'.$hx_writer_url.'" hx-vals=\''.json_encode($hx_vals).'\' hx-target="#updateResponse" hx-indicator="#updateIndicator" hx-swap="outerHTML" name="load_update_data" value="beta">'.$lang['btn_choose_this_update'].'</button>';
        $update_beta = $lang['update_msg_beta'];
    } else {
        echo '<button class="btn btn-default" disabled>'.$lang['btn_choose_this_update'].'</button>';
    }
    echo '</li>';
    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
    echo 'Version: '.$remote_versions_array['version']['alpha']['title'] .'<br>';
    echo 'Build: '.$remote_versions_array['version']['alpha']['build'] .'<br>';
    echo 'Date: ' .$remote_versions_array['version']['alpha']['date'];
    $update_alpha = '';
    echo '<div class="w-50">';
    if($se_version['build'] < $remote_versions_array['version']['alpha']['build']) {
        $filename_alpha = basename($remote_versions_array['version']['alpha']['file']);
        $hx_vals += ["file" => "$filename_alpha"];
        echo '<button class="btn btn-default btn-sm w-100" hx-post="'.$hx_writer_url.'" hx-vals=\''.json_encode($hx_vals).'\' hx-target="#updateResponse" hx-indicator="#updateIndicator" hx-swap="outerHTML" name="load_update_data" value="alpha">'.$lang['btn_choose_this_update'].'</button>';
        $update_alpha = $lang['update_msg_alpha'];
    } else {
        echo '<button class="btn btn-default btn-sm w-100" disabled>'.$lang['btn_choose_this_update'].'</button>';
    }
    if($se_environment == 'd') {
        $filename_alpha = basename($remote_versions_array['version']['alpha']['file']);
        $hx_vals += ["file" => "$filename_alpha"];
        echo '<button class="btn btn-default btn-sm w-100 mt-1" hx-post="'.$hx_writer_url.'" hx-vals=\''.json_encode($hx_vals).'\' hx-target="#updateResponse" hx-indicator="#updateIndicator" hx-swap="outerHTML" name="load_update_data" value="alpha">'.$lang['btn_choose_this_update'].' '.$icon['arrow_clockwise'].'</button>';
    }
    echo '</div>';

    echo '</li>';
    echo '</ul>';

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
 * @return array
 */
function get_remote_versions(): array {
    $remote_versions_file = file_get_contents("https://swiftyedit.net/releases/v2/versions.json");
    $remote_versions_array = json_decode($remote_versions_file,true);
    return $remote_versions_array;
}


/**
 * get the files from the extracted zip file
 * and copy them to their destination
 * @return void
 */
function move_new_files($source) {

    global $remote_file;
    $cnt_errors = 0;

    $sources_path = '../acp/core/update/download/extract/';
    $sources_dir = basename($source);


    if(is_dir($sources_path.$sources_dir))	{
        $new_files = scandir_recursive($sources_path.$sources_dir);
    } else {
        echo '<div class="alert alert-danger">No Source found: '. $sources_path.$sources_dir .'</div>';
    }


    /* at first, the install folder */
    rmdir_recursive('../install');
    copy_recursive($sources_path.$sources_dir."/install","../install");

    /* payment addons */
    copy_recursive($sources_path.$sources_dir."/plugins/se_invoice-pay","../plugins/se_invoice-pay");
    copy_recursive($sources_path.$sources_dir."/plugins/se_cash-pay","../plugins/se_cash-pay");

    if(!is_array($new_files)) {
        $_SESSION['protocol'] .= "ERROR can not scan target files<|>";
    }

    /* now copy the other files and directories */
    foreach($new_files as $value) {

        if(str_contains("$value","/install/")) { continue; }
        if(str_contains("$value","/data/")) { continue; }
        if(str_contains("$value","/plugins/")) { continue; }
        if(str_contains("$value",".github")) { continue; }
        if(str_contains("$value",".idea")) { continue; }
        if(str_starts_with(basename($value),".")) { continue; }
        if($value === '.' || $value === '..') {continue;}
        if(str_contains("$value","robots.txt")) { continue; }

        // copy files from 'download/extract/*'
        $target = '..' . substr($value, strlen($sources_path.$sources_dir));
        copy_recursive("$value","$target");

    }

    $_SESSION['errors_cnt'] = $cnt_errors;
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
 * @param $source
 * @param $target
 * @return mixed|string|void
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


/**
 * Update the database
 * build an array from all php files in directory ../install/contents
 * @return void
 */
function update_database() {

    $all_tables = glob("../install/contents/*.php");

    for($i=0;$i<count($all_tables);$i++) {

        unset($db_path,$table_name,$database,$table_type);

        include $all_tables[$i]; // returns $cols and $table_name

        $is_table = table_exists("$database","$table_name");

        if($is_table < 1) {
            add_table("$database","$table_name",$cols);

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