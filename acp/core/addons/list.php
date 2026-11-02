<?php

/**
 * installed plugins and themes in /admin/addons/, markup in acp/templates/addons/list.twig
 * both lists are loaded by the addons data-reader
 *
 * global variables
 * @var array $icon
 * @var array $lang
 * @var object $twig
 * @var bool $se_upload_addons from config.php
 */

echo $twig->render('addons/list.twig', [
    'can_install' => ($se_upload_addons && se_hasPermission('drm_acp_sensitive_files')),
    'reader_uri' => '/admin-xhr/addons/read/'
]);
