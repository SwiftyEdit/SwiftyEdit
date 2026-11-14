<?php

/**
 * SwiftyEdit Update Script, markup in acp/templates/update/index.twig
 *
 *  1. load the zip file from swiftyedit.net
 *  2. mkdir acp/update and acp/update/extract
 *     copy the zip file into /acp/update and extract the files
 *  3. copy the file maintenance.html from /install/ to /public/ (starts the update mode in frontend)
 *  4. copy the files from acp/update/extract to their destination
 *  5. run the update script and check up the database
 *  6. delete maintenance.html from /public/ - (ends the update modus in frontend)
 *
 * the channels and the downloaded releases are loaded by the update data-reader,
 * download and installation are handled by the update data-writer
 *
 * global variables
 * @var array $se_version (date, version, build)
 * @var string $se_base_url
 * @var object $twig
 * @var bool $se_backend_updates from config.php
 */

set_time_limit(0);

echo $twig->render('update/index.twig', [
    'version' => $se_version,
    'base_url' => $se_base_url,
    'updates_disabled' => ($se_backend_updates === false),
    'zip_missing' => !extension_loaded('zip'),
    'reader_uri' => '/admin-xhr/update/read/'
]);
