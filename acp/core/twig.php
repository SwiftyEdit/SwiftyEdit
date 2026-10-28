<?php

/**
 * Twig environment for the backend templates in acp/templates/
 * shared by acp/index.php (full pages) and acp/header.php (xhr requests)
 *
 * expects $icon, $lang, $se_settings and $se_environment to be set
 */

$loader = new \Twig\Loader\FilesystemLoader(SE_ROOT.'/acp/templates');
$twig = new \Twig\Environment($loader, [
    'cache' => SE_CONTENT.'/cache/twig',
    // In dev, recompile on every change; in production, trust the cache
    // (mirrors the $se_environment check in acp/core/update/functions.php).
    'auto_reload' => $se_environment === 'd',
]);

$twig->addGlobal('icon', $icon);
$twig->addGlobal('lang', $lang);
$twig->addGlobal('csrf_token', $_SESSION['token']);
$twig->addGlobal('se_settings', $se_settings);

$twig_globals = [
    'server_name' => $_SERVER['SERVER_NAME'],
    'request_uri' => $_SERVER['REQUEST_URI'],
];

$twig->addGlobal('global', $twig_globals);
