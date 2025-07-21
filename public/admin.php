<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);

// Fallback für $query, falls $_GET['query'] nicht zuverlässig gesetzt wird
$query = $_GET['query'] ?? null;

if (!$query && isset($_SERVER['REQUEST_URI'])) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#^/admin(?:/)?(.*)$#', $path, $matches)) {
        $query = $matches[1] ?? '';
        $_GET['query'] = $query;
        $_REQUEST['query'] = $query;
    }
}

// Wenn kein Query vorhanden ist (z. B. Aufruf von /admin/ direkt)
if (empty($query)) {
    if ($_SESSION['user_class'] === 'administrator') {
        // Admin eingeloggt → Weiterleitung ins Dashboard
        header('Location: /admin/dashboard/');
        exit;
    } else {
        // Nicht eingeloggt → Login anzeigen
        include '../acp/login.php';
        exit;
    }
}

// Wenn eingeloggt als Admin → lade Admin-Routing
if ($_SESSION['user_class'] === 'administrator') {
    include '../acp/index.php';
    exit;
}

// Nicht Admin → Login anzeigen
include '../acp/login.php';
exit;
