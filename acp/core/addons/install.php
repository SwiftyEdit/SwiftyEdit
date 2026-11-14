<?php

/**
 * install a plugin or theme from the url of its info.json in /admin/addons/install/,
 * markup in acp/templates/addons/install.twig, the steps are handled by the addons data-writer
 *
 * global variables
 * @var object $twig
 * @var bool $se_upload_addons from config.php
 */

echo $twig->render('addons/install.twig', [
    'enabled' => (bool) $se_upload_addons,
    'writer_uri' => '/admin-xhr/addons/write/'
]);
