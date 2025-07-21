<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);

// Sicherheit: nur Administratoren
if ($_SESSION['user_class'] !== 'administrator') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Fallback für IONOS oder andere, falls $_GET['query'] nicht zuverlässig funktioniert
$query = $_GET['query'] ?? null;
if (!$query && isset($_SERVER['REQUEST_URI'])) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#^/admin/xhr/(.*)$#', $path, $matches)) {
        $query = $matches[1];
    }
}

if (!$query) {
    http_response_code(400);
    echo 'Missing query parameter';
    exit;
}

// Optionale Prüfung auf HTMX/XHR
/*
$isHtmx = isset($_SERVER['HTTP_HX_REQUEST']) || (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    );

if (!$isHtmx) {
    http_response_code(400);
    echo 'Invalid XHR request';
    exit;
}
*/

// Routing anhand von Query
if (str_contains($query, '/read/')) {
    include '../acp/data_reader.php';
    exit;
}

if (str_contains($query, '/write/') || str_contains($query, '/delete/')) {
    include '../acp/data_writer.php';
    exit;
}

if (str_contains($query, '/upload/')) {
    include '../acp/core/widgets/upload.php';
    exit;
}

http_response_code(404);
echo 'Unknown XHR route';
