<?php
session_start();
error_reporting(0);

// administrators only
if ($_SESSION['user_class'] !== 'administrator') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Fallback if $_GET[‘query’] does not work properly
$query = $_GET['query'] ?? null;
if (!$query) {
    // 1. Evaluate REQUEST_URI
    if (isset($_SERVER['REQUEST_URI'])) {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = preg_replace('#//+#', '/', $path); // doppelte Slashes entfernen
        if (preg_match('#^/admin(?:/)?(.*)$#', $path, $matches)) {
            $query = $matches[1] ?? '';
        }
    }

    // 2. PATH_INFO as backup
    if (!$query && !empty($_SERVER['PATH_INFO'])) {
        $query = ltrim($_SERVER['PATH_INFO'], '/');
    }

    // 3. Evaluate query string
    if (!$query && isset($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'], $output);
        if (isset($output['query'])) {
            $query = $output['query'];
        }
    }

    // Save result in superglobals
    if ($query !== null) {
        $_GET['query'] = $query;
        $_REQUEST['query'] = $query;
    }
}

if (!$query) {
    http_response_code(400);
    echo 'Missing query parameter';
    exit;
}

// check, if it is a XHR

$isXhr = isset($_SERVER['HTTP_HX_REQUEST']) || (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    );

if (!$isXhr) {
    http_response_code(400);
    echo 'Invalid XHR request ';
    exit;
}


// Routing based on query
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