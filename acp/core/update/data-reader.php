<?php

require_once __DIR__.'/functions.php';

if($_GET['action'] == 'read_versions') {

    $remote_versions_array = get_remote_versions();
    compare_versions();

    // notices from the same versions.json, swapped into their own area below
    // the version cards (see index.php) - no second request to swiftyedit.net
    echo '<div id="updateNotices" hx-swap-oob="true">'.se_render_update_notices($remote_versions_array).'</div>';
    exit;
}

// check if there are downloaded files to install
if($_GET['action'] == 'check_download') {

    $extract_dir = __DIR__.'/download/extract';

    $hx_vals = [
        "csrf_token"=> $_SESSION['token']
    ];

    // only the list itself - the surrounding card is part of index.php
    $downloads = [];
    if(is_dir($extract_dir)) {
        foreach(scandir($extract_dir) as $download) {
            if(str_starts_with($download, '.')) {continue;}
            $downloads[] = $download;
        }
    }

    if($downloads !== []) {
        foreach($downloads as $download) {
            echo '<div class="row">';
            echo '<div class="col-md-4">'.$download.'</div>';
            echo '<div class="col-md-8 text-end">';
            echo '<button class="btn btn-default text-success" hx-post="/admin-xhr/update/write/" hx-vals=\''.json_encode($hx_vals).'\' hx-target="#updateDone" hx-indicator="#htmxIndicator" hx-swap="innerHTML" name="install_update" value="'.$download.'">'.$icon['sync_alt'].' Install</button>';
            echo '<button class="btn btn-danger" hx-post="/admin-xhr/update/write/" hx-vals=\''.json_encode($hx_vals).'\' hx-target="#updateDone" hx-indicator="#htmxIndicator" hx-swap="innerHTML" name="remove_download" value="'.$download.'">'.$icon['trash_alt'].' Delete</button>';
            echo '</div>';
            echo '</div>';
        }

    } else {
        echo '<p class="mb-0">There are no files available for an installation. Select a source above, if available.</p>';
    }

    exit;
}