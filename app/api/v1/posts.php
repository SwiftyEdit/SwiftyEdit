<?php
/**
 * API v1 - blog posts (scope posts:read)
 *
 * GET /api/v1/posts/               list of public posts
 *     ?lang=de                     only posts in this language
 *     ?type=message,image          only these post types (message, image,
 *                                  gallery, video, link, file)
 *     ?category=news               only posts in this category (slug)
 *     ?page=1&per_page=20          pagination, per_page max. 100
 * GET /api/v1/posts/{id}/          a single post
 *     ?render=1                    (both) resolve snippets/shortcodes in
 *                                  teaser and text, see se_api_render_text()
 *
 * Drafts (post_status 2) and posts with a release date in the future are
 * never returned.
 *
 * variables
 * @var array $requestPathParts from routing.php
 * @var object $db_posts
 * @var string $db_type from app/database.php
 */

$api_post_id = $requestPathParts[3] ?? '';

$api_format_options = [
    'render' => ($_GET['render'] ?? '') === '1'
];
$api_format = fn($post) => se_api_format_post($post, $api_format_options);

// public and released: no release date, or one in the past
$api_where = 'post_status = 1 AND (post_releasedate IS NULL OR post_releasedate <= :api_now)';
$api_map = [':api_now' => time()];

// --- single post ---

if ($api_post_id !== '') {

    if (!ctype_digit($api_post_id)) {
        se_api_error(404, 'Post not found');
    }

    $api_map[':api_post_id'] = (int) $api_post_id;
    $post = $db_posts->query("SELECT * FROM se_posts WHERE post_id = :api_post_id AND $api_where", $api_map)->fetch(PDO::FETCH_ASSOC);

    if (!is_array($post)) {
        se_api_error(404, 'Post not found');
    }

    se_api_respond(['data' => $api_format($post)]);
}

// --- list ---

$api_pagination = se_api_pagination();
$api_page = $api_pagination['page'];
$api_per_page = $api_pagination['per_page'];
$api_offset = $api_pagination['offset'];

if (isset($_GET['lang']) && $_GET['lang'] !== '') {
    if (!preg_match('/^[a-zA-Z-]{2,20}$/', $_GET['lang'])) {
        se_api_error(400, 'Invalid lang parameter');
    }
    $api_where .= ' AND post_lang = :api_lang';
    $api_map[':api_lang'] = $_GET['lang'];
}

if (isset($_GET['type']) && $_GET['type'] !== '') {
    $api_type_codes = ['message' => 'm', 'image' => 'i', 'gallery' => 'g', 'video' => 'v', 'link' => 'l', 'file' => 'f'];
    $api_type_placeholders = [];
    foreach (explode(',', $_GET['type']) as $i => $api_type) {
        $api_type = trim($api_type);
        if (!isset($api_type_codes[$api_type])) {
            se_api_error(400, 'Invalid type parameter');
        }
        $api_type_placeholders[] = ':api_type' . $i;
        $api_map[':api_type' . $i] = $api_type_codes[$api_type];
    }
    $api_where .= ' AND post_type IN (' . implode(',', $api_type_placeholders) . ')';
}

if (isset($_GET['category']) && $_GET['category'] !== '') {
    $api_cat_hashes = se_api_category_hashes($_GET['category']);
    if (empty($api_cat_hashes)) {
        se_api_error(400, 'Unknown category');
    }
    $api_cat_conditions = [];
    foreach ($api_cat_hashes as $i => $api_cat_hash) {
        $api_cat_conditions[] = 'post_categories LIKE :api_cat' . $i;
        $api_map[':api_cat' . $i] = '%' . $api_cat_hash . '%';
    }
    $api_where .= ' AND (' . implode(' OR ', $api_cat_conditions) . ')';
}

$api_total = (int) $db_posts->query("SELECT COUNT(*) FROM se_posts WHERE $api_where", $api_map)->fetchColumn();

// same order as the blog listing (se_get_post_entries()): fixed posts
// first, then by release day, priority and id
if ($db_type === 'sqlite') {
    $api_sortdate = "strftime('%Y-%m-%d', datetime(post_releasedate, 'unixepoch'))";
} else {
    $api_sortdate = "FROM_UNIXTIME(post_releasedate, '%Y-%m-%d')";
}
$posts = $db_posts->query(
    "SELECT * FROM se_posts WHERE $api_where
     ORDER BY post_fixed ASC, $api_sortdate DESC, post_priority DESC, post_id DESC
     LIMIT $api_offset, $api_per_page",
    $api_map
)->fetchAll(PDO::FETCH_ASSOC);

se_api_respond([
    'data' => array_map($api_format, $posts),
    'meta' => [
        'page' => $api_page,
        'per_page' => $api_per_page,
        'total' => $api_total,
        'total_pages' => (int) ceil($api_total / $api_per_page)
    ]
]);
