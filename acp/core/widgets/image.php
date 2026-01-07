<?php

error_reporting(0);

// Only logged-in admins may see backend images
if (($_SESSION['user_class'] ?? null) !== 'administrator') {
    http_response_code(403);
    exit;
}

$src = $_GET['src'] ?? '';
if ($src === '') {
    http_response_code(400);
    exit;
}

// Strict input validation
if (!preg_match('#^[a-zA-Z0-9/_.-]+$#', $src)) {
    http_response_code(400);
    exit;
}

// Whitelist Extensions
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'bmp'];
$extension = strtolower(pathinfo($src, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions, true)) {
    http_response_code(403);
    exit;
}

// Base paths relative to public/
$rootDocs = realpath(__DIR__ . '/../../../docs');
$rootPlugins = realpath(__DIR__ . '/../../../plugins');
$rootThemes = realpath(__DIR__ . '/assets/themes');

$basePath = null;
$relative = '';

if (str_starts_with($src, 'docs/')) {
    $basePath = $rootDocs;
    $relative = substr($src, 5); // strlen('docs/')
} elseif (str_starts_with($src, 'plugins/')) {
    $basePath = $rootPlugins;
    $relative = substr($src, 8); // strlen('plugins/')
} elseif (str_starts_with($src, 'themes/')) {
    $basePath = $rootThemes;
    $relative = substr($src, 7); // strlen('themes/')
} else {
    http_response_code(400);
    exit;
}

if (!$basePath || !$relative) {
    http_response_code(500);
    exit;
}

// Normalize path separators
$relative = str_replace('\\', '/', $relative);

// Build full path
$path = $basePath . DIRECTORY_SEPARATOR . $relative;
$real = realpath($path);

// Security checks:
// 1. File must exist and be readable
// 2. Must be inside basePath (prevents directory traversal)
// 3. Must be a regular file (not directory or symlink)
if (!$real ||
    !is_file($real) ||
    !is_readable($real) ||
    strpos($real, $basePath . DIRECTORY_SEPARATOR) !== 0) {
    http_response_code(404);
    exit;
}

// Double-check extension after realpath resolution
$realExtension = strtolower(pathinfo($real, PATHINFO_EXTENSION));
if (!in_array($realExtension, $allowedExtensions, true)) {
    http_response_code(403);
    exit;
}

// Get MIME type with fallback
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $real);
finfo_close($finfo);

// Whitelist MIME types
$allowedMimes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'image/svg+xml',
    'image/x-icon',
    'image/bmp',
    'image/vnd.microsoft.icon'
];

if (!in_array($mime, $allowedMimes, true)) {
    http_response_code(403);
    exit;
}

// Security headers
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($real));
header('X-Content-Type-Options: nosniff'); // prevent MIME-Sniffing
header('Cache-Control: private, max-age=3600'); // cache for 1 hour

// Output file
readfile($real);
exit;