<?php

require_once __DIR__.'/../bootstrap.php';

$xhr_path = $requestPathParts[2] ?? '';
$xhr_path = trim($xhr_path, '/');

$routes = [
    'comments' => 'comments.php',
    'counter' => 'counter.php',
    'vote' => 'ajax.votings.php',
    'votes' => 'votes.php',
    'login' => 'login.php',
    'orders' => 'orders.php',
    'password-reset' => 'password-reset.php',
    'statusbox' => 'statusbox.php',
    'search' => 'search.php'
];

if (!preg_match('#^[a-z0-9\-_]+$#i', $xhr_path)) {
    http_response_code(400);
    exit('Invalid XHR request');
}

if (isset($routes[$xhr_path])) {
    require __DIR__ . '/' . $routes[$xhr_path];
    exit;
} else {
    http_response_code(404);
    exit();
}