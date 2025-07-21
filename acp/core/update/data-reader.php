<?php

require_once __DIR__.'/functions.php';

if($_GET['action'] == 'read_versions') {

    $remote_versions_array = get_remote_versions();
    compare_versions();
    exit;
}

// check if there are downloaded files to install
if($_GET['action'] == 'check_download') {

    $extract_dir = __DIR__.'/download/extract';

    $hx_vals = [
        "csrf_token"=> $_SESSION['token']
    ];

    echo '<div class="card">';
    echo '<div class="card-header">Loaded files for installation</div>';
    echo '<div class="card-body">';
    if(is_dir($extract_dir)) {
        $get_downloads = scandir($extract_dir);

        foreach($get_downloads as $download) {
            if($download == '.' OR $download == '..') {continue;}
            if(str_starts_with($download, '.')) {continue;}

            echo '<div class="row">';
            echo '<div class="col-md-4">'.$download.'</div>';
            echo '<div class="col-md-8 text-end">';
            echo '<button class="btn btn-default text-success" hx-post="/admin/xhr/update/write/" hx-vals=\''.json_encode($hx_vals).'\' hx-target="#updateDone" hx-indicator="#htmxIndicator" hx-swap="innerHTML" name="install_update" value="'.$download.'">'.$icon['sync_alt'].' Install</button>';
            echo '<button class="btn btn-danger" hx-post="/admin/xhr/update/write/" hx-vals=\''.json_encode($hx_vals).'\' hx-target="#updateDone" hx-indicator="#htmxIndicator" hx-swap="innerHTML" name="remove_download" value="'.$download.'">'.$icon['trash_alt'].' Delete</button>';
            echo '</div>';
            echo '</div>';
        }

    } else {
        echo '<p>There are no files available for an installation. Select a source above, if available.</p>';
    }

    echo '</div>';
    exit;
}