<?php

/**
 * catalog of installable plugins and themes in /admin/addons/catalog/, markup in acp/templates/addons/catalog.twig
 * the grid is loaded by the addons data-reader
 *
 * global variables
 * @var object $twig
 * @var bool $se_upload_addons from config.php
 */

echo $twig->render('addons/catalog.twig', [
    'enabled' => (bool) $se_upload_addons,
    'reader_uri' => '/admin-xhr/addons/read/'
]);
