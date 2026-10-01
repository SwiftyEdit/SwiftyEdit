<?php
/**
 * API v1 - pages (scope pages:read)
 *
 * GET /api/v1/pages/               list of public pages
 *     ?lang=de                     only pages in this language
 *     ?parent=4                    only children of this page,
 *     ?parent=none                 or only pages without a parent
 *     ?type=imprint                only pages with this type of use
 *     ?page=1&per_page=20          pagination, per_page max. 100
 * GET /api/v1/pages/{id}/          a single page
 *     ?render=1                    (both) resolve snippets/shortcodes in
 *                                  the content, see se_api_render_text()
 *
 * Visibility: the list only contains "public" pages, a "ghost" page can be
 * fetched by id (like a direct link). Drafts, private pages, password
 * protected pages and pages restricted to user groups are never returned -
 * an API key is not a logged-in user (see app/template-setup.php).
 *
 * variables
 * @var array $requestPathParts from routing.php
 * @var object $db_content
 */

$api_page_id = $requestPathParts[3] ?? '';

$api_format_options = [
    'render' => ($_GET['render'] ?? '') === '1'
];
$api_format = fn($page) => se_api_format_page($page, $api_format_options);

// no password, no user group restriction - on SQLite these columns are
// NULL rather than '' for pages saved without them
$api_where = "COALESCE(page_psw, '') = '' AND COALESCE(page_usergroup, '') = ''";
$api_map = [];

// --- single page ---

if ($api_page_id !== '') {

    if (!ctype_digit($api_page_id)) {
        se_api_error(404, 'Page not found');
    }

    $api_map[':api_page_id'] = (int) $api_page_id;
    $page = $db_content->query(
        "SELECT * FROM se_pages WHERE page_id = :api_page_id AND page_status IN ('public', 'ghost') AND $api_where",
        $api_map
    )->fetch(PDO::FETCH_ASSOC);

    if (!is_array($page)) {
        se_api_error(404, 'Page not found');
    }

    se_api_respond(['data' => $api_format($page)]);
}

// --- list ---

$api_page = max(1, (int) ($_GET['page'] ?? 1));
$api_per_page = min(100, max(1, (int) ($_GET['per_page'] ?? 20)));

$api_where .= " AND page_status = 'public'";

if (isset($_GET['lang']) && $_GET['lang'] !== '') {
    if (!preg_match('/^[a-zA-Z-]{2,20}$/', $_GET['lang'])) {
        se_api_error(400, 'Invalid lang parameter');
    }
    $api_where .= ' AND page_language = :api_lang';
    $api_map[':api_lang'] = $_GET['lang'];
}

if (isset($_GET['parent']) && $_GET['parent'] !== '') {
    if ($_GET['parent'] === 'none') {
        $api_where .= ' AND page_parent_id IS NULL';
    } else if (ctype_digit($_GET['parent'])) {
        $api_where .= ' AND page_parent_id = :api_parent';
        $api_map[':api_parent'] = (int) $_GET['parent'];
    } else {
        se_api_error(400, 'Invalid parent parameter');
    }
}

if (isset($_GET['type']) && $_GET['type'] !== '') {
    if (!preg_match('/^[a-z0-9_]{1,50}$/', $_GET['type'])) {
        se_api_error(400, 'Invalid type parameter');
    }
    $api_where .= ' AND page_type_of_use = :api_type';
    $api_map[':api_type'] = $_GET['type'];
}

$api_total = (int) $db_content->query("SELECT COUNT(*) FROM se_pages WHERE $api_where", $api_map)->fetchColumn();

$api_offset = ($api_page - 1) * $api_per_page;

// sibling order of the page tree
$pages = $db_content->query(
    "SELECT * FROM se_pages WHERE $api_where
     ORDER BY position ASC, page_id ASC
     LIMIT $api_offset, $api_per_page",
    $api_map
)->fetchAll(PDO::FETCH_ASSOC);

se_api_respond([
    'data' => array_map($api_format, $pages),
    'meta' => [
        'page' => $api_page,
        'per_page' => $api_per_page,
        'total' => $api_total,
        'total_pages' => (int) ceil($api_total / $api_per_page)
    ]
]);
