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
 * variables
 * @var string $se_version_build from versions.php
 *
 * Global variables
 * @var array $lang language file
 * @var array $icon icons
 */

set_time_limit (0);

$remote_versions_file = file_get_contents("https://swiftyedit.net/releases/v2/versions.json");
$remote_versions_array = json_decode($remote_versions_file,true);

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['arrow_clockwise'].' '.$lang['update'];
echo '<div class="ms-auto">core updates build: <code>'.$se_version_build.'</code></div>';
echo '</div>';

const INSTALLER = TRUE;
require '../install/php/functions.php';
require __DIR__.'/functions.php';

if(!extension_loaded('zip')) {
    echo '<div class="alert alert-danger mb-4">The required extension <strong>ZIP</strong> is not installed</div>';
}

compare_versions();



