<?php

/**
 * sidebar of the ACP, markup in acp/templates/layout/nav-sidebar.twig
 *
 * global variables
 * @var string $se_section current module
 * @var string $query the current url (without /admin/)
 * @var string $db_type
 * @var object $twig
 */

// path of the request with a trailing slash, the active sub page is the
// menu item whose link it starts with ($subinc is not known yet at this point)
$current_path = '/admin/'.trim((string) parse_url($query ?? '', PHP_URL_PATH), '/').'/';

echo $twig->render('layout/nav-sidebar.twig', [
    'section' => $se_section,
    'current_path' => $current_path,
    'show_database' => ($db_type == 'sqlite')
]);
