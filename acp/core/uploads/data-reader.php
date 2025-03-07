<?php

$writer_uri = '/admin/uploads/edit/';
$delete_uri = '/admin/uploads/delete/';
$reader_uri = '/admin/uploads/read/';


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

    $media_where = [
        "AND" => [
            "media_id[>]" => 0,
            "media_file[~]" => ["AND" => ["$file_query%","%$uploads_text_filter%"]],
            "media_lang" => "$languagePack"
        ]];

    $media_order = [
        "ORDER" => [
            "$order_key" => "$order_direction"
        ]
    ];

    $media_limit = [
        "LIMIT" => [$limit_start, $nbr_show_items]
    ];

    $media_data_cnt = $db_content->count("se_media", $media_where);


    $media_data = $db_content->select("se_media","*",
        $media_where+$media_order+$media_limit
    );

    $nbr_pages = ceil($media_data_cnt/$nbr_show_items);

    echo se_print_pagination('/admin/uploads/write/',$nbr_pages,$_SESSION['pagination_page']);

    echo '<div class="row">';

    foreach($media_data as $media) {

        $list_tpl = $tpl_list_files;
        $preview_src = str_replace('../', '/', $media['media_file']);
        $preview_filename = str_replace('/images/', '', $preview_src);
        $preview_lastedit = se_format_datetime($media['media_lastedit']);
        $preview_filesize = readable_filesize($media['media_filesize']);
        $media_file_hits = (int) $media['media_file_hits'];

        $delete_btn = '<button class="btn btn-default btn-sm text-danger" name="delete" value="'.$media['media_id'].'" hx-post="'.$delete_uri.'" hx-target="#response" hx-confirm="'.$lang['msg_confirm_delete'].'" hx-swap="innerHTML" hx-include="[name=\'csrf_token\']">'.$icon['trash_alt'].'</button> ';
        $edit_btn = '<button class="btn btn-default btn-sm text-success w-100" name="file" value="'.$media['media_file'].'" >'.$icon['edit'].'</button>';


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
        $list_tpl = str_replace("{preview_img}",'<img src="'.$preview_src.'" class="card-img-top">',$list_tpl);
        $list_tpl = str_replace("{show_filetime}","$preview_lastedit",$list_tpl);
        $list_tpl = str_replace("{filesize}","$preview_filesize",$list_tpl);
        $list_tpl = str_replace("{media_file_hits}","$media_file_hits",$list_tpl);
        $list_tpl = str_replace("{labels}","$labels",$list_tpl);
        $list_tpl = str_replace("{edit_button}","$edit_btn",$list_tpl);
        $list_tpl = str_replace("{delete_button}","$delete_btn",$list_tpl);
        $list_tpl = str_replace("{csrf_token}",$_SESSION['token'],$list_tpl);

        echo $list_tpl;

    }

    echo '</div>';

}