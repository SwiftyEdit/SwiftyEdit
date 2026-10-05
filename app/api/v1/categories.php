<?php
/**
 * API v1 - categories (scope categories:read)
 *
 * GET /api/v1/categories/          list of categories, in the same order
 *                                  as in the frontend (cat_sort, descending)
 *     ?lang=de                     only categories in this language
 *     ?page=1&per_page=20          pagination, per_page max. 100
 * GET /api/v1/categories/{id}/     a single category
 *     ?render=1                    (both) resolve snippets/shortcodes in
 *                                  teaser and text, see se_api_render_text()
 *
 * Categories have no status, all of them are public. They are shared by
 * products, posts and events - the slug is what the ?category= filter of
 * those endpoints expects.
 *
 * variables
 * @var array $requestPathParts from routing.php
 */

$api_category_id = $requestPathParts[3] ?? '';

$api_format_options = [
    'render' => ($_GET['render'] ?? '') === '1'
];
$api_format = fn($category) => se_api_format_category($category, $api_format_options);

// se_get_categories() reads the same cache file as the frontend and is
// already ordered - categories are few, so filtering and paging is done here
$categories = se_get_categories();

// --- single category ---

if ($api_category_id !== '') {

    if (!ctype_digit($api_category_id)) {
        se_api_error(404, 'Category not found');
    }

    foreach ($categories as $category) {
        if ((int) $category['cat_id'] === (int) $api_category_id) {
            se_api_respond(['data' => $api_format($category)]);
        }
    }

    se_api_error(404, 'Category not found');
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
    $categories = array_values(array_filter($categories, fn($category) => $category['cat_lang'] === $_GET['lang']));
}

$api_total = count($categories);

se_api_respond([
    'data' => array_map($api_format, array_slice($categories, $api_offset, $api_per_page)),
    'meta' => [
        'page' => $api_page,
        'per_page' => $api_per_page,
        'total' => $api_total,
        'total_pages' => (int) ceil($api_total / $api_per_page)
    ]
]);
