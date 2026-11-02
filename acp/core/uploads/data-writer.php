<?php

/**
 * SwiftyEdit /admin-xhr/uploads/write/
 * media library: delete files, search, sorting, pagination, folders
 * and the sync of the database with the files on the server
 *
 * global variables
 * @var array $lang
 * @var object $db_content
 * @var string $languagePack
 * @var string $img_path
 * @var string $files_path
 */

require_once __DIR__.'/functions.php';

// delete a file with all its language entries and its thumbnail
if(isset($_POST['delete'])) {

    $media = se_get_media_data_by_id((int) $_POST['delete']);
    if(!is_array($media)) {
        exit;
    }

    // media DB paths are stored as "../images/..." / "../files/...", the files are in public/assets/
    $delete_file = (string) $media['media_file'];
    $delete_thumb = (string) $media['media_thumb'];
    $delete_file_src = '';
    if(str_starts_with($delete_file, '../images/') || str_starts_with($delete_file, '../files/')) {
        $delete_file_src = se_resolve_within(SE_PUBLIC.'/assets', substr($delete_file, 3));
    }
    if($delete_file_src === false || $delete_file_src === '') {
        exit;
    }

    if(!is_file($delete_file_src)) {
        // deleted via FTP, the entry is useless without the file
        se_delete_media_data($delete_file);
        show_toast($lang['msg_error_file_not_found'], 'info');
    } else if(unlink($delete_file_src)) {
        se_delete_media_data($delete_file);
        if(str_starts_with($delete_thumb, '../images_tmb/')) {
            $delete_thumb_src = se_resolve_within(SE_PUBLIC.'/assets/images_tmb', substr($delete_thumb, strlen('../images_tmb/')));
            if($delete_thumb_src !== false && is_file($delete_thumb_src)) {
                unlink($delete_thumb_src);
            }
        }
        show_toast($lang['msg_success_file_delete'], 'success');
    } else {
        show_toast($lang['msg_error_file_delete'].' ('.basename($delete_file).')', 'danger');
    }
    header("HX-Trigger: update_uploads_list");
    exit;
}

// pagination
if(isset($_POST['pagination_uploads'])) {
    $_SESSION['pagination_uploads'] = (int) $_POST['pagination_uploads'];
    header("HX-Trigger: update_uploads_list");
    exit;
}

// text search in the file names
if(isset($_POST['uploads_text_filter'])) {
    $add_text_filter = sanitizeUserInputs($_POST['uploads_text_filter']);
    $text_filter = $_SESSION['uploads_text_filter'] ?? '';
    if($add_text_filter != '' && !in_array($add_text_filter, explode(' ', $text_filter))) {
        $_SESSION['uploads_text_filter'] = trim($text_filter.' '.$add_text_filter);
    }
    $_SESSION['pagination_uploads'] = 0;
    header("HX-Trigger: update_uploads_list");
    exit;
}

// remove keyword from filter list
if(isset($_POST['rmkey'])) {
    // the keywords are stored sanitized (see above), the button sends the plain keyword
    $remove_keyword = sanitizeUserInputs($_POST['rmkey']);
    $keywords = explode(' ', $_SESSION['uploads_text_filter'] ?? '');
    $keywords = array_filter($keywords, static fn($keyword) => $keyword != '' && $keyword != $remove_keyword);
    $_SESSION['uploads_text_filter'] = implode(' ', $keywords);
    $_SESSION['pagination_uploads'] = 0;
    header("HX-Trigger: update_uploads_list");
    exit;
}

// sorting column and direction
if(isset($_POST['sorting_media_list'])) {
    $sorting = (string) $_POST['sorting_media_list'];
    $_SESSION['sorting_media_list'] = in_array($sorting, ['media_lastedit', 'media_file', 'media_filesize']) ? $sorting : 'media_lastedit';
    header("HX-Trigger: update_uploads_list");
    exit;
}

if(isset($_POST['sorting_media_dir'])) {
    $_SESSION['sorting_media_direction'] = ($_POST['sorting_media_dir'] == 'ASC') ? 'ASC' : 'DESC';
    header("HX-Trigger: update_uploads_list");
    exit;
}

// change directory - only folders of the media library (assets/images, assets/files)
if(isset($_POST['selected_folder'])) {
    $selected_folder = se_media_folder(se_filter_filepath((string) $_POST['selected_folder']));
    if($selected_folder !== false) {
        $_SESSION['disk'] = $selected_folder;
        $_SESSION['pagination_uploads'] = 0;
    }
    header("HX-Trigger: update_uploads_list");
    exit;
}

// create a new folder inside the selected one
if(isset($_POST['new_folder'])) {
    $folder_name = clean_filename((string) $_POST['new_folder']);
    if($folder_name !== '') {
        $create_path = SE_PUBLIC.'/'.se_media_current_folder().'/'.$folder_name;
        if(!is_dir($create_path)) {
            mkdir($create_path, 0755, true);
        }
    }
    header("HX-Trigger: update_directories, update_uploads_list");
    exit;
}

// delete a subfolder with its content
if(isset($_POST['delete_dir'])) {
    if(delete_folder((string) $_POST['delete_dir']) === true) {
        $_SESSION['disk'] = $img_path;
        $_SESSION['pagination_uploads'] = 0;
        header("HX-Trigger: update_directories, update_uploads_list");
    } else {
        show_toast($lang['msg_error_file_delete'], 'danger');
    }
    exit;
}

/**
 * sync the database with the files on the server,
 * e.g. someone uploaded or deleted files via FTP
 * new files get an entry, entries of missing files are removed
 */
if(($_POST['rebase'] ?? '') == 'files_to_database') {

    // media DB paths are stored as "../images/..." / "../files/..."
    $all_files = [];
    foreach(array_merge(se_scandir_recursive($img_path), se_scandir_recursive($files_path)) as $path) {
        if(is_dir($path) || basename($path) == 'index.html') {
            continue;
        }
        $all_files[] = str_replace('assets/', '../', $path);
    }

    $known_files = array_unique($db_content->select("se_media", "media_file"));

    $added = 0;
    foreach(array_diff($all_files, $known_files) as $filename) {
        $file_src = str_replace('../', 'assets/', $filename);
        $filemtime = filemtime($file_src);
        $db_content->insert("se_media", [
            "media_file" => $filename,
            "media_lang" => $languagePack,
            "media_filesize" => filesize($file_src),
            "media_lastedit" => $filemtime,
            "media_upload_time" => $filemtime,
            "media_type" => mime_content_type($file_src)
        ]);
        $added++;
    }

    $missing_files = array_values(array_diff($known_files, $all_files));
    $removed = count($missing_files);
    if($removed > 0) {
        $db_content->delete("se_media", ["media_file" => $missing_files]);
    }

    show_toast(str_replace(
        ['{found}', '{added}', '{removed}'],
        [(string) count($all_files), (string) $added, (string) $removed],
        $lang['msg_info_media_rebase']
    ), 'info');
    header("HX-Trigger: update_uploads_list");
    exit;
}
