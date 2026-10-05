<?php

/**
 * SwiftyEdit /admin/uploads/
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $db_content
 * @var string $languagePack
 * @var array $lang_codes
 * @var array $se_labels
 */

use Medoo\Medoo;

$writer_uri = '/admin-xhr/uploads/edit/';
$delete_uri = '/admin-xhr/uploads/delete/';
$reader_uri = '/admin-xhr/uploads/read/';

if($_REQUEST['action'] == 'list_active_searches') {

    $btn_remove_keyword = '';

    if(isset($_SESSION['uploads_text_filter']) AND $_SESSION['uploads_text_filter'] != "") {
        $all_filter = explode(" ", $_SESSION['uploads_text_filter']);
        foreach($all_filter as $f) {
            if ($_REQUEST['rm_keyword'] == "$f" || $f == "") { continue; }
            $btn_remove_keyword .= '<button class="btn btn-sm btn-default" name="rmkey" value="'.$f.'" hx-post="/admin-xhr/uploads/write/" hx-swap="none" hx-include="[name=\'csrf_token\']">'.$icon['x'].' '.$f.'</button> ';
        }
    }

    if($btn_remove_keyword != '') {
        echo '<div class="d-inline">';
        echo '<p style="padding-top:5px;">' . $btn_remove_keyword . '</p>';
        echo '</div><hr>';
    }
}

if($_REQUEST['action'] == 'show_stats') {

    $media_cnt_images = $db_content->count("se_media",[
        "media_file[~]" => "../images/"
    ]);

    $media_cnt_files = $db_content->count("se_media",[
        "media_file[~]" => "../files/"
    ]);

    echo '<table class="table">';
    echo '<tr>';
    echo '<td>Images</td><td>'.$media_cnt_images.'</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>Files</td><td>'.$media_cnt_files.'</td>';
    echo '</tr>';
    echo '</table>';
}


// show select for directories
if($_REQUEST['action'] == 'select_directory') {

    $path_img = 'assets/images';
    $dirs_img = se_get_dirs_rec($path_img);
    array_unshift($dirs_img, $path_img);
    $path_files = 'assets/files';
    $dirs_files = se_get_dirs_rec($path_files);
    array_unshift($dirs_files, $path_files);

    $disk = $_SESSION['disk'] ?? $path_img;

    echo '<form hx-post="/admin-xhr/uploads/write/" hx-swap="none" hx-trigger="change" method="POST" class="d-inline">';

    echo '<select name="selected_folder" class="form-control custom-select">';
    echo '<optgroup label="'.$lang['images'].'">';
    foreach($dirs_img as $d) {
        $selected = ($disk == $d) ? 'selected' : '';
        $short_d = str_replace($path_img, '', $d);
        echo '<option value="'.$d.'" '.$selected.'>'.basename($path_img).$short_d.'</option>';
    }
    echo '</optgroup>';
    echo '<optgroup label="'.$lang['files'].'">';
    foreach($dirs_files as $d) {
        $selected = ($disk == $d) ? 'selected' : '';
        $short_d = str_replace($path_files, '', $d);
        echo '<option value="'.$d.'" '.$selected.'>'.basename($path_files).$short_d.'</option>';
    }
    echo '</optgroup>';
    echo '</select>';

    echo '<input type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
    echo '</form>';

}

// show input for new directories
if($_REQUEST['action'] == 'input_new_directory') {
    echo '<form hx-post="/admin-xhr/uploads/write/" hx-swap="none" method="POST">';
    echo '<div class="input-group">';
    echo '<input type="text" name="new_folder" class="form-control">';
    echo '<div class="input-group-append">';
    echo '<input type="submit" name="submit" value="'.$lang['btn_create_new_folder'].'" class="btn btn-default">';
    echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
    echo '</div>';
    echo '</div>';
    echo '</form>';
}


// list files
if($_REQUEST['action'] == 'list') {

    // defaults
    $order_by = 'media_lastedit';
    $order_direction = 'DESC';
    $media_file = '/images';
    $limit_start = $_SESSION['pagination_page'] ?? 0;
    $nbr_show_items = 50;
    $nbr_show_pages = 10;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }

    $file_query = $_SESSION['disk'] ?? $media_file;
    $file_query = str_replace('assets/', '../', $file_query);

    if(str_starts_with($file_query, '../images')) {
        $tpl_list_files = file_get_contents('../acp/templates/list-files-thumbs.tpl');
    } else {
        $tpl_list_files = file_get_contents('../acp/templates/list-files-grid.tpl');
    }

    $order_key = $_SESSION['sorting_media_list'] ?? $order_by;
    $order_direction = $_SESSION['sorting_direction'] ?? $order_direction;

    if($_SESSION['uploads_text_filter'] != '') {
        $uploads_text_filter = trim($_SESSION['uploads_text_filter']);
    } else {
        $uploads_text_filter = '/';
    }

    // whitelist sort keys, they are used inside a raw SQL expression below
    if(!in_array($order_key, ['media_lastedit', 'media_file', 'media_filesize'])) {
        $order_key = $order_by;
    }
    if(!in_array($order_direction, ['ASC', 'DESC'])) {
        $order_direction = 'DESC';
    }

    // se_media stores one row per file and language.
    // The list shows one entry per file, regardless of the selected language.
    $media_where = [
        "AND" => [
            "media_id[>]" => 0,
            "media_file[~]" => ["AND" => ["$file_query%","%$uploads_text_filter%"]]
        ]];

    $media_data_cnt = count($db_content->select("se_media", "media_file",
        $media_where + ["GROUP" => "media_file"]
    ));

    $page_files = $db_content->select("se_media", [
            "media_file",
            "sort_value" => Medoo::raw("MAX(<$order_key>)")
        ],
        $media_where + [
            "GROUP" => "media_file",
            "ORDER" => ["sort_value" => $order_direction],
            "LIMIT" => [$limit_start, $nbr_show_items]
        ]
    );
    $page_files = array_column($page_files, 'media_file');

    // fetch all language rows of the files on this page and group them by file
    $media_rows_by_file = [];
    if(count($page_files) > 0) {
        $media_rows = $db_content->select("se_media", "*", [
            "media_file" => $page_files
        ]);
        foreach($media_rows as $row) {
            $media_rows_by_file[$row['media_file']][$row['media_lang']] = $row;
        }
    }

    $nbr_pages = ceil($media_data_cnt/$nbr_show_items);

    echo se_print_pagination('/admin-xhr/uploads/write/',$nbr_pages,$_SESSION['pagination_page']);

    echo '<div class="row">';

    // flags are base64 encoded images, build them only once per language
    $lang_flags = [];
    foreach($lang_codes as $lang_code) {
        $lang_flags[$lang_code] = return_language_flag_src($lang_code);
    }

    foreach($page_files as $file) {

        $file_rows = $media_rows_by_file[$file] ?? [];
        if(count($file_rows) < 1) {
            continue;
        }

        // prefer the row of the current backend language, fall back to any existing row
        $media = $file_rows[$languagePack] ?? reset($file_rows);
        $form_id = 'media-form-'.$media['media_id'];

        $list_tpl = $tpl_list_files;
        $preview_src = str_replace('../', '/', $media['media_file']);
        $preview_filename = str_replace('/images/', '', $preview_src);
        $preview_lastedit = se_format_datetime($media['media_lastedit']);
        $preview_filesize = readable_filesize($media['media_filesize']);
        $media_file_hits = (int) $media['media_file_hits'];

        // one flag per available language, languages without data are greyed out
        $media_lang_thumb = '';
        foreach($lang_codes as $lang_code) {
            $flag_attr = isset($file_rows[$lang_code]) ? '' : ' style="filter:grayscale(1);opacity:.5"';
            $media_lang_thumb .= '<button type="submit" form="'.$form_id.'" name="set_lang" value="'.$lang_code.'" class="btn btn-link p-0 border-0 align-baseline"'.$flag_attr.' title="'.$lang_code.'">';
            $media_lang_thumb .= '<img src="'.$lang_flags[$lang_code].'" width="15" alt="'.$lang_code.'">';
            $media_lang_thumb .= '</button> ';
        }

        $delete_btn = '<button class="btn btn-default btn-sm text-danger" name="delete" value="'.$media['media_id'].'" hx-post="'.$delete_uri.'" hx-target="#response" hx-confirm="'.$lang['msg_confirm_delete_media'].'" hx-swap="innerHTML" hx-include="[name=\'csrf_token\']">'.$icon['trash_alt'].'</button> ';
        $edit_btn = '<button type="submit" class="btn btn-default btn-sm text-success w-100">'.$icon['edit'].' '.$lang['edit'].'</button>';


        $labels = '';
        if($media['media_labels'] != '') {
            $get_media_labels = explode(',',$media['media_labels']);
            foreach($get_media_labels as $media_label) {

                foreach($se_labels as $l) {
                    if($media_label == $l['label_id']) {
                        $label_color = $l['label_color'];
                        $label_title = $l['label_title'];
                    }
                }

                $labels .= '<span class="label-dot" style="background-color:'.$label_color.';" title="'.$label_title.'"></span>';
            }
        }


        $list_tpl = str_replace("{short_filename}","$preview_filename",$list_tpl);
        $list_tpl = str_replace("{preview_link}","$preview_filename",$list_tpl);
        $list_tpl = str_replace("{preview_img}",'<img src="'.$preview_src.'" class="card-img-top">',$list_tpl);
        $list_tpl = str_replace("{show_filetime}","$preview_lastedit",$list_tpl);
        $list_tpl = str_replace("{filesize}","$preview_filesize",$list_tpl);
        $list_tpl = str_replace("{media_file_hits}","$media_file_hits",$list_tpl);
        $list_tpl = str_replace("{labels}","$labels",$list_tpl);
        $list_tpl = str_replace("{lang_thumb}","$media_lang_thumb",$list_tpl);
        $list_tpl = str_replace("{form_id}","$form_id",$list_tpl);
        $list_tpl = str_replace("{media_file}",htmlspecialchars($media['media_file'], ENT_QUOTES, 'UTF-8'),$list_tpl);
        $list_tpl = str_replace("{edit_button}","$edit_btn",$list_tpl);
        $list_tpl = str_replace("{delete_button}","$delete_btn",$list_tpl);
        $list_tpl = str_replace("{csrf_token}",$_SESSION['token'],$list_tpl);

        echo $list_tpl;

    }

    echo '</div>';

    if($_SESSION['disk'] != 'assets/images' AND $_SESSION['disk'] != 'assets/files' AND $_SESSION['disk'] != '') {
        $delete_dir_btn = '<form hx-post="/admin-xhr/uploads/write/" hx-confirm="' . $lang['msg_confirm_delete_directory'] . '" hx-swap="none" class="mt-3 text-end">';
        $delete_dir_btn .= '<button name="delete_dir" value="' . $_SESSION['disk'] . '" class="btn btn-danger">';
        $delete_dir_btn .= $icon['trash_alt'] . ' ' . $_SESSION['disk'];
        $delete_dir_btn .= '</button>';
        $delete_dir_btn .= '<input type="hidden" name="csrf_token" value="' . $_SESSION['token'] . '">';
        $delete_dir_btn .= '</form>';
        echo $delete_dir_btn;
    }
}