<?php
/**
 * API v1 - products (scope products:read)
 *
 * GET /api/v1/products/            list of public products (no variants)
 *     ?lang=de                     only products in this language
 *     ?page=1&per_page=20          pagination, per_page max. 100
 * GET /api/v1/products/{id}/       a single product or variant,
 *                                  products include their variants
 *
 * Visibility mirrors the frontend: drafts (status 2) and unreleased
 * products are never returned, "ghost" products (status 3) are left out
 * of the list but can be fetched by id - like a direct link.
 *
 * variables
 * @var array $requestPathParts from routing.php
 * @var object $db_posts
 */

$api_product_id = $requestPathParts[3] ?? '';

// released: no release date, or one in the past
// (Medoo needs a named key for a nested OR group)
$api_released = [
    'releasedate' => null,
    'releasedate[<=]' => time()
];

// --- single product ---

if ($api_product_id !== '') {

    if (!ctype_digit($api_product_id)) {
        se_api_error(404, 'Product not found');
    }

    $product = $db_posts->get('se_products', '*', [
        'AND' => [
            'id' => (int) $api_product_id,
            'type' => ['p', 'v'],
            'status' => [1, 3],
            'OR #released' => $api_released
        ]
    ]);

    if (!is_array($product)) {
        se_api_error(404, 'Product not found');
    }

    $data = se_api_format_product($product);

    if ($product['type'] === 'p') {
        $variants = $db_posts->select('se_products', '*', [
            'AND' => [
                'parent_id' => (int) $product['id'],
                'type' => 'v',
                'status' => [1, 3],
                'OR #released' => $api_released
            ],
            'ORDER' => ['priority' => 'DESC', 'id' => 'DESC']
        ]);
        $data['variants'] = array_map('se_api_format_product', $variants);
    }

    se_api_respond(['data' => $data]);
}

// --- list ---

$api_page = max(1, (int) ($_GET['page'] ?? 1));
$api_per_page = min(100, max(1, (int) ($_GET['per_page'] ?? 20)));

$api_where = [
    'type' => 'p',
    'status' => 1,
    'OR #released' => $api_released
];

if (isset($_GET['lang']) && $_GET['lang'] !== '') {
    if (!preg_match('/^[a-zA-Z-]{2,20}$/', $_GET['lang'])) {
        se_api_error(400, 'Invalid lang parameter');
    }
    $api_where['product_lang'] = $_GET['lang'];
}

$api_total = (int) $db_posts->count('se_products', ['AND' => $api_where]);

$products = $db_posts->select('se_products', '*', [
    'AND' => $api_where,
    // same default order as the shop listing (se_get_products())
    'ORDER' => ['fixed' => 'ASC', 'priority' => 'DESC', 'id' => 'DESC'],
    'LIMIT' => [($api_page - 1) * $api_per_page, $api_per_page]
]);

se_api_respond([
    'data' => array_map('se_api_format_product', $products),
    'meta' => [
        'page' => $api_page,
        'per_page' => $api_per_page,
        'total' => $api_total,
        'total_pages' => (int) ceil($api_total / $api_per_page)
    ]
]);
