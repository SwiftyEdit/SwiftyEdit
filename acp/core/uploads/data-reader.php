<?php

$writer_uri = '/admin/uploads/edit/';
$delete_uri = '/admin/uploads/delete/';
$reader_uri = '/admin/uploads/read/';


if($_REQUEST['action'] == 'list') {

    // defaults
    $order_by = 'media_lastedit';
    $order_direction = 'DESC';
    $media_file = '../images';
    $limit_start = $_SESSION['pagination_page'] ?? 0;
    $nbr_show_items = 50;
    $nbr_show_pages = 10;

    if($limit_start > 0) {
        $limit_start = ($limit_start*$nbr_show_items);
    }


    $file_query = $_SESSION['disk'] ?? $media_file;
    $file_query = str_replace('assets/', '../', $file_query);

    $order_key = $_SESSION['sorting_media_list'] ?? $order_by;
    $order_direction = $_SESSION['sorting_direction'] ?? $order_direction;

    if($_SESSION['uploads_text_filter'] != '') {
        $uploads_text_filter = trim($_SESSION['uploads_text_filter']);
    } else {
        $uploads_text_filter = '../';
    }

    $media_where = [
        "AND" => [
            "media_id[>]" => 0,
            "media_file[~]" => ["AND" => ["$file_query%","%$uploads_text_filter%"]],
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

        $preview_src = str_replace('../', '/', $media['media_file']);
        $preview_filename = str_replace('/images/', '', $preview_src);
        $preview_lastedit = se_format_datetime($media['media_lastedit']);
        $preview_filesize = readable_filesize($media['media_filesize']);

        $delete_btn = '<button class="btn btn-default text-danger" name="delete" value="'.$media['media_id'].'" hx-post="'.$delete_uri.'" hx-target="#response" hx-swap="innerHTML" hx-include="[name=\'csrf_token\']">'.$icon['trash_alt'].'</button> ';


        echo '<div class="col-md-2">';

        echo '<div class="card h-100">';
        echo '<div class="card-header">'.$preview_filename.'</div>';
        echo '<img src="'.$preview_src.'" class="card-img-top">';
        echo '<div class="card-body">';
        echo $preview_lastedit.'<br>'.$preview_filesize;
        echo '</div>';
        echo '<div class="card-footer">';

        echo '<a class="btn btn-default text-success" href="'.$writer_uri.$media['media_id'].'/">'.$icon['edit'].'</a>';
        echo $delete_btn;

        echo '</div>';
        echo '</div>';

        echo '</div>';
    }

    echo '</div>';

}