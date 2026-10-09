<?php

/**
 * routing - write data
 */


require_once 'header.php';

// writers change data - POST only, and always with a valid CSRF token
// (header.php only checks it for a non-empty $_POST)
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    se_plain_response('Method Not Allowed', 405);
}
if (!is_string($_POST['csrf_token'] ?? null) || !hash_equals((string) ($_SESSION['token'] ?? ''), $_POST['csrf_token'])) {
    se_plain_response('Error: CSRF Token is invalid', 403);
}

// module permission - same check as the module's UI router
$required_permission = se_acp_xhr_permission($_REQUEST['query']);
if ($required_permission !== null && !se_hasPermission($required_permission)) {
    se_plain_response($lang['rm_no_access'], 403);
}

$writer = match (true) {
    str_starts_with($_REQUEST['query'], 'addons/') => 'core/addons/data-writer.php',
    str_starts_with($_REQUEST['query'], 'settings/') => 'core/settings/data-writer.php',
    str_starts_with($_REQUEST['query'], 'categories/') => 'core/categories/data-writer.php',
    str_starts_with($_REQUEST['query'], 'tags/') => 'core/tags/data-writer.php',
    str_starts_with($_REQUEST['query'], 'pages/') => 'core/pages/data-writer.php',
    str_starts_with($_REQUEST['query'], 'snippets/') => 'core/snippets/data-writer.php',
    str_starts_with($_REQUEST['query'], 'uploads/') => 'core/uploads/data-writer.php',
    str_starts_with($_REQUEST['query'], 'update/') => 'core/update/data-writer.php',
    str_starts_with($_REQUEST['query'], 'shop/') => 'core/shop/data-writer.php',
    str_starts_with($_REQUEST['query'], 'blog/') => 'core/blog/data-writer.php',
    str_starts_with($_REQUEST['query'], 'events/') => 'core/events/data-writer.php',
    str_starts_with($_REQUEST['query'], 'users/settings/') => 'core/users/settings-writer.php',
    str_starts_with($_REQUEST['query'], 'users/') => 'core/users/data-writer.php',
    str_starts_with($_REQUEST['query'], 'inbox/') => 'core/inbox/data-writer.php',
    str_starts_with($_REQUEST['query'], 'widgets/') => 'core/widgets/data-writer.php',
    str_starts_with($_REQUEST['query'], 'dashboard/') => 'core/dashboard/data-writer.php',
    default => ''
};

if($writer != '') {
    include_once $writer;
    exit;
}

