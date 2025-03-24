<?php

/**
 * SwiftyEdit Update Script
 *
 *  1. load the zip file from swiftyedit.net
 *  2. mkdir acp/update and acp/update/extract
 *     copy the zip file into /acp/update and extract the files
 *  3. copy the file maintenance.html from /install/ to /public/ (starts the update mode in frontend)
 *  4. copy the files from acp/update/extract to their destination
 *  5. run the update script and check up the database
 *  6. delete maintenance.html from /public/ - (ends the update modus in frontend)
 *
 *
 * Global variables
 * @var array $se_version (date, version, build)
 * @var array $lang language file
 * @var array $icon icons
 */

set_time_limit (0);


echo '<div class="subHeader d-flex align-items-center">';
echo $icon['arrow_clockwise'].' '.$lang['update'];
echo '<div class="ms-auto">core updates build: <code>'.$se_version['build'].'</code></div>';
echo '</div>';

const INSTALLER = TRUE;
require '../install/php/functions.php';
require __DIR__.'/functions.php';

echo '<div id="updateResponse">';
echo '<div id="updateIndicator" class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>';
echo '</div>';

if(!extension_loaded('zip')) {
    echo '<div class="alert alert-danger mb-4">The required extension <strong>ZIP</strong> is not installed</div>';
}

echo '<div class="row mb-2">';
echo '<div class="col-6">';
/* installed version */
echo '<div class="card h-100">';
echo '<div class="card-header">'.$icon['database'].'  '. $se_base_url .'</div>';
echo '<div class="card-body">';
echo '<p>Version: '.$se_version['version'].'<br>Build '.$se_version['build'].'<br>Date: '.$se_version['date'].'</p>';

echo '<div id="updateDone">';
echo '<div id="htmxIndicator" class="d-flex align-items-center htmx-indicator"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span></div>';
echo '</div>';

echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="col-6">';

/* remote version */
echo '<div class="card h-100">';
echo '<div class="card-header">'.$icon['server'].'  SwiftyEdit Server</div>';
echo '<div class="card-body">';

echo '<div id="" class="" hx-get="/admin/update/read/?action=read_versions" hx-trigger="load">';
echo '<div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span>';
echo '</div>';

echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';


echo '<div id="" class="" hx-get="/admin/update/read/?action=check_download" hx-trigger="load, update_downloads_list from:body">';
echo '<div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span>';
echo '</div>';