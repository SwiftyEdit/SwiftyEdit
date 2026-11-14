<?php

/**
 * The full log in /admin/dashboard/log/, the list is loaded by the dashboard data-reader
 *
 * @var array $lang
 * @var array $icon
 * @var object $twig core/twig.php
 */

echo $twig->render('dashboard/log.twig', [
    'reader_uri' => '/admin-xhr/dashboard/read/',
    // only these values, the reader checks them again
    'filter' => in_array($_GET['filter'] ?? '', ['content', 'security'], true) ? $_GET['filter'] : 'all',
    'can_view_log' => se_hasPermission('drm_acp_sensitive_files')
]);
