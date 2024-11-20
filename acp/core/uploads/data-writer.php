<?php


/**
 * delete media
 */

if(isset($_POST['delete'])) {

    $delete_id = (int) $_POST['delete'];
    $get_file_data = se_get_media_data_by_id($delete_id);

    $delete_file = $get_file_data['media_file'];
    $delete_thumb = $get_file_data['media_thumb'];

    $delete_file_src = str_replace('../', 'assets/', $delete_file);
    $delete_thumb_src = str_replace('../', 'assets/', $delete_thumb);

    if(is_file($delete_file_src)) {
        if(unlink($delete_file_src)) {
            se_delete_media_data($delete_file);
            if(is_file($delete_thumb)) {
                unlink($delete_thumb);
            }
            echo '<div class="alert alert-success alert-auto-close">'.$lang['msg_success_file_delete'].'</div>';
        } else {
            echo '<div class="alert alert-danger"><strong>'.$delete_file_src.'</strong><br>'.$lang['msg_error_file_delete'].'</div>';
        }
    } else {
        echo '<div class="alert alert-error">File ('.$delete_file_src.') not found</div>';
    }
    header( "HX-Trigger: update_uploads_list");
}




/**
 * pagination
 */

if(isset($_POST['pagination'])) {
    $_SESSION['pagination_page'] = (int) $_POST['pagination'];
    header( "HX-Trigger: update_uploads_list");
}


/**
 * filter files by text
 */
if(isset($_POST['uploads_text_filter'])) {

    $_SESSION['uploads_text_filter'] = $_SESSION['uploads_text_filter'] . ' ' . sanitizeUserInputs($_POST['uploads_text_filter']);

    header( "HX-Trigger: update_uploads_list");
}

/**
 * switch sorting
 * and sort direction
 */

if(isset($_POST['sorting'])) {
    if($_POST['sorting'] == 'date') {
        $_SESSION['sorting_media_list'] = 'media_lastedit';
    } else if($_POST['sorting'] == 'name') {
        $_SESSION['sorting_media_list'] = 'media_file';
    } else if($_POST['sorting'] == 'size') {
        $_SESSION['sorting_media_list'] = 'media_filesize';
    }

    if($_POST['sorting'] == 'direction') {
        if($_SESSION['sorting_direction'] == 'ASC') {
            $_SESSION['sorting_direction'] = 'DESC';
        } else if($_SESSION['sorting_direction'] == 'DESC') {
            $_SESSION['sorting_direction'] = 'ASC';
        }
    }

    header( "HX-Trigger: update_uploads_list");
}

// remove keyword from filter list
if(isset($_POST['rmkey'])) {
    print_r($_POST);
    $all_filter = explode(" ", $_SESSION['uploads_text_filter']);
    $_SESSION['uploads_text_filter'] = '';
    foreach($all_filter as $f) {
        if($_POST['rmkey'] == "$f") { continue; }
        if($f == "") { continue; }
        $_SESSION['uploads_text_filter'] .= "$f ";
    }
    header( "HX-Trigger: update_uploads_list");
}

// change directory
if(isset($_POST['selected_folder'])) {
    $_SESSION['disk'] = se_filter_filepath($_POST['selected_folder']);
    header( "HX-Trigger: update_uploads_list");
}

/**
 * create new folder
 */
if((isset($_POST['new_folder'])) && ($_POST['new_folder'] != '')) {
    $folder_name = clean_filename($_POST['new_folder']);
    $create_path = $_SESSION['disk'] . '/' . $folder_name;
    mkdir($create_path, 0777, true);
    header( "HX-Trigger: update_directories");
}

/**
 * rebase the database
 * we check if there are files which are not in the database
 * or if there are files in the database that not exists on the server
 * f.e. someone uploaded or deleted files via FTP
 */

if(isset($_POST['rebase'])) {
    if($_POST['rebase'] == "files_to_database") {

        $stats_files_to_db = 0;
        $stats_files_fromm_db = 0;

        $images_dir = 'assets/images';
        $files_dir = 'assets/files';

        $scan_images = se_scandir_recursive("$images_dir");
        $scan_files = se_scandir_recursive("$files_dir");
        $images_and_files = array_merge($scan_images, $scan_files);

        foreach ($images_and_files as $key => $value) {
            if(str_contains("$value","index.html")) { continue; }
            $all_files[] = str_replace('assets/', '../', $value);
        }

        $cnt_all_files = count($all_files);

        $mediaData = $db_content->select("se_media", "media_file");


        foreach($all_files as $filename) {
            if(!in_array($filename, $mediaData)) {
                // filename is not in database, mak an entry

                $file_src = str_replace("../","assets/",$filename);
                $filesize = filesize($file_src);
                $filemtime = filemtime($file_src);
                $filetype = mime_content_type(realpath($file_src));

                if($filetype == 'directory') {
                    continue;
                }

                $stats_files_to_db++;

                $db_content->insert("se_media", [
                    "media_file" => "$filename",
                    "media_lang" => "$languagePack",
                    "media_filesize" => "$filesize",
                    "media_lastedit" => "$filemtime",
                    "media_upload_time" => "$filemtime",
                    "media_type" => "$filetype"
                ]);
            }
        }



        foreach($mediaData as $k => $v) {
            if(!in_array($v, $all_files)) {
                $stats_files_fromm_db++;
                $db_content->delete("se_media", [
                    "media_file" => "$v"
                ]);
            }
        }


        echo '<p><code>'.$stats_files_to_db.'</code> were added to the database<br>';
        echo '<code>'.$stats_files_fromm_db.'</code> were removed from the database</p>';

    }

}