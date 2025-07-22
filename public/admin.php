<?php
session_start();
error_reporting(0);

// Fallback if $_GET[‘query’] does not work properly
$query = $_GET['query'] ?? null;

// Fallback starten, wenn kein query vorhanden
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

// check, if it is a XHR

$isXhr = isset($_SERVER['HTTP_HX_REQUEST']) || (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    );

if ($isXhr) {
    http_response_code(400);
    echo 'Invalid request';
    exit;
}


// If no query is available (e.g., call from /admin/ directly)
if (empty($query)) {
    if ($_SESSION['user_class'] === 'administrator') {
        // admin, redirect to the dashboard
        header('Location: /admin/dashboard/');
        exit;
    } else {
        // No admin, show login
        include '../acp/login.php';
        exit;
    }
}

// When logged in as admin → load backend
if ($_SESSION['user_class'] === 'administrator') {
    include '../acp/index.php';
    exit;
}

// No admin, show login
include '../acp/login.php';
exit;
