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

if(!extension_loaded('zip')) {
    echo '<div class="alert alert-danger mb-4">The required extension <strong>ZIP</strong> is not installed</div>';
}

// notices from swiftyedit.net - filled by the read_versions request below (hx-swap-oob).
// Shown first, because they should be read before choosing an update.
echo '<div id="updateNotices"></div>';

echo '<div class="row mb-2">';
echo '<div class="col-6">';
/* installed version */
echo '<div class="card h-100">';
echo '<div class="card-header">'.$icon['database'].'  '. $se_base_url .'</div>';
echo '<div class="card-body">';
echo '<p>Version: '.$se_version['version'].'<br>Build '.$se_version['build'].'<br>Date: '.$se_version['date'].'</p>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="col-6">';

/* remote version */
echo '<div class="card h-100">';
echo '<div class="card-header">'.$icon['server'].'  SwiftyEdit Server</div>';
echo '<div class="card-body">';

echo '<div id="" class="" hx-get="/admin-xhr/update/read/?action=read_versions" hx-trigger="load">';
echo '<div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span>';
echo '</div>';

echo '</div>';
echo '</div>';

echo '</div>';
echo '</div>';

/* downloaded files - placed directly below the channel buttons, so the result
   of a download (and later of the installation) shows up right where it was triggered */
echo '<div class="card mb-2">';
// indicators for download (load_update_data) and installation (install_update) sit in the
// header, outside the swap targets - so they survive the swaps and take no space in the body
echo '<div class="card-header d-flex align-items-center">Loaded files for installation';
echo '<span id="updateIndicator" class="htmx-indicator ms-auto"><span class="spinner-border spinner-border-sm" role="status"></span><span class="sr-only">Loading...</span></span>';
echo '<span id="htmxIndicator" class="htmx-indicator ms-auto"><span class="spinner-border spinner-border-sm" role="status"></span><span class="sr-only">Loading...</span></span>';
echo '</div>';
echo '<div class="card-body">';

// response of a download (load_update_data)
echo '<div id="updateResponse"></div>';

echo '<div hx-get="/admin-xhr/update/read/?action=check_download" hx-trigger="load, update_downloads_list from:body">';
echo '<div class="spinner-border spinner-border-sm me-2" role="status"></div><span class="sr-only">Loading...</span>';
echo '</div>';

// protocol of an installation (install_update)
echo '<div id="updateDone"></div>';

echo '</div>';
echo '</div>';
