<?php

require_once __DIR__.'/../bootstrap.php';

$xhr_path = $requestPathParts[2] ?? '';
$xhr_path = trim($xhr_path, '/');

$routes = [
    'cart' => 'cart.php',
    'checks' => 'checks.php',
    'comments' => 'comments.php',
    'counter' => 'counter.php',
    'guestlist' => 'ajax.guestlist.php',
    'vote' => 'ajax.votings.php',
    'votes' => 'votes.php',
    'login' => 'login.php',
    'orders' => 'orders.php',
    'order-withdrawal' => 'order-withdrawal.php',
    'password-reset' => 'password-reset.php',
    'products' => 'products.php',
    'statusbox' => 'statusbox.php',
    'search' => 'search.php',
    'profile' => 'profile.php',
    'wishlist' => 'wishlist.php'
];

if (!preg_match('#^[a-z0-9\-_]+$#i', $xhr_path)) {
    http_response_code(400);
    exit('Invalid XHR request');
}

// endpoints with personal data, orders or actions in the user's name: check
// the session against the account on every request, not only every few
// minutes (see se_validate_user_session()) - here, before the endpoints
// release the session lock with session_write_close()
$sensitive_routes = ['cart', 'comments', 'guestlist', 'vote', 'orders', 'order-withdrawal', 'profile', 'wishlist'];
if (in_array($xhr_path, $sensitive_routes, true)) {
    se_validate_user_session();
}

if (isset($routes[$xhr_path])) {
    require __DIR__ . '/' . $routes[$xhr_path];
    exit;
} else {
    http_response_code(404);
    exit();
}