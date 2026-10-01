<?php

/**
 * functions for the public/headless API (see app/handlers/api-routes.php)
 * used by the API itself and by the ACP (Settings > API keys)
 */

/**
 * Get the scope catalog from app/api/scopes.php
 *
 * @return array scope => ['label' => language key]
 */
function se_api_get_scopes(): array {
    static $scopes = null;
    if ($scopes === null) {
        $scopes = include SE_ROOT . 'app/api/scopes.php';
    }
    return $scopes;
}

/**
 * Reduce a list of scopes to the ones that exist in the catalog
 *
 * @param array $scopes
 * @return array
 */
function se_api_filter_scopes(array $scopes): array {
    $known = array_keys(se_api_get_scopes());
    return array_values(array_intersect($known, $scopes));
}

/**
 * Generate a new random API key
 * format: "se_" + 48 hex characters (24 random bytes)
 *
 * @return string plaintext key - only ever shown once, store the hash
 */
function se_api_generate_key(): string {
    return 'se_' . bin2hex(random_bytes(24));
}

/**
 * Hash an API key for storage and lookup
 * keys are random and high-entropy, so a fast hash is sufficient here
 * (unlike passwords) and allows looking a key up by its hash directly
 *
 * @param string $key
 * @return string
 */
function se_api_hash_key(string $key): string {
    return hash('sha256', $key);
}

/**
 * The part of a key that is stored in plaintext and shown in the ACP,
 * so an admin can tell keys apart without ever seeing them again
 *
 * @param string $key
 * @return string
 */
function se_api_key_prefix(string $key): string {
    return substr($key, 0, 10);
}

/**
 * Create a new API key
 *
 * @param string $label
 * @param array $scopes
 * @param int $user_id creator
 * @return string the plaintext key
 */
function se_api_create_key(string $label, array $scopes, int $user_id): string {

    global $db_user;

    $key = se_api_generate_key();

    // every column is written explicitly, the SQLite schema has no defaults
    $db_user->insert('se_api_keys', [
        'label' => $label,
        'key_hash' => se_api_hash_key($key),
        'key_prefix' => se_api_key_prefix($key),
        'scopes' => implode(',', se_api_filter_scopes($scopes)),
        'is_active' => 1,
        'created_by' => $user_id,
        'created_at' => time(),
        'last_used_at' => 0,
        'request_count' => 0
    ]);

    return $key;
}

/**
 * Read the API key from the "Authorization: Bearer <key>" request header
 * Apache with PHP-FPM/CGI may hide the header from $_SERVER unless it is
 * passed on explicitly, so all known places are checked
 *
 * @return string|null
 */
function se_api_get_bearer_token(): ?string {

    $header = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';

    if ($header === '' && function_exists('getallheaders')) {
        foreach (getallheaders() as $name => $value) {
            if (strtolower($name) === 'authorization') {
                $header = $value;
                break;
            }
        }
    }

    if (preg_match('/^Bearer\s+(\S+)$/i', trim($header), $matches)) {
        return $matches[1];
    }

    return null;
}

/**
 * Look up an active API key and record its usage
 *
 * @param string $key plaintext key from the request
 * @return array|null ['id' => int, 'label' => string, 'scopes' => array] or null if unknown/revoked
 */
function se_api_authenticate(string $key): ?array {

    global $db_user;

    $row = $db_user->get('se_api_keys', ['id', 'label', 'scopes'], [
        'key_hash' => se_api_hash_key($key),
        'is_active' => 1
    ]);

    if (!is_array($row)) {
        return null;
    }

    // usage is tracked from day one, so rate limiting can build on it later
    $db_user->update('se_api_keys', [
        'last_used_at' => time(),
        'request_count[+]' => 1
    ], [
        'id' => $row['id']
    ]);

    return [
        'id' => (int) $row['id'],
        'label' => $row['label'],
        'scopes' => array_values(array_filter(explode(',', (string) $row['scopes'])))
    ];
}

/**
 * Plain text value for the API - text is stored HTML-encoded in the
 * database, API clients get it decoded and escape it themselves
 *
 * @param mixed $value
 * @return string
 */
function se_api_plain(mixed $value): string {
    return html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Turn a product row into its public API representation
 * whitelist only - internal columns (purchase price, notes, after-sale
 * files, ...) must never reach the API
 *
 * @param array $product row from se_products
 * @return array
 */
function se_api_format_product(array $product): array {

    global $se_base_url;

    // images: stored as "<->" separated paths like "/images/foo.jpg"
    $images = [];
    foreach (explode('<->', (string) $product['images']) as $image) {
        $image = trim($image);
        if ($image !== '') {
            $images[] = rtrim($se_base_url, '/') . '/' . ltrim($image, '/');
        }
    }

    // categories: stored as comma separated cat_hash values
    $categories = [];
    $product_cat_hashes = array_filter(explode(',', (string) $product['categories']));
    if (!empty($product_cat_hashes)) {
        foreach (se_get_categories() as $category) {
            if (in_array($category['cat_hash'], $product_cat_hashes, true)) {
                $categories[] = [
                    'name' => se_api_plain($category['cat_name']),
                    'slug' => $category['cat_name_clean']
                ];
            }
        }
    }

    $tags = array_values(array_filter(array_map('trim', explode(',', se_api_plain($product['tags'])))));

    return [
        'id' => (int) $product['id'],
        'uuid' => $product['uuid'] !== '' ? $product['uuid'] : null,
        'type' => $product['type'] === 'v' ? 'variant' : 'product',
        'parent_id' => $product['type'] === 'v' ? (int) $product['parent_id'] : null,
        'lang' => $product['product_lang'],
        'title' => se_api_plain($product['title']),
        'variant_title' => se_api_plain($product['product_variant_title']),
        // HTML as stored - shortcodes/snippets are not resolved, since
        // text_parser() depends on the visitor's frontend context
        'teaser' => htmlspecialchars_decode((string) $product['teaser']),
        'text' => htmlspecialchars_decode((string) $product['text']),
        'slug' => $product['slug'],
        'url' => $product['product_canonical_url'],
        'images' => $images,
        'categories' => $categories,
        'tags' => $tags,
        'product_number' => se_api_plain($product['product_number']),
        'manufacturer' => se_api_plain($product['product_manufacturer']),
        'ean' => $product['product_ean'],
        'mpn' => se_api_plain($product['product_mpn']),
        'price' => se_api_format_product_price($product),
        'unit' => se_api_plain($product['product_unit']),
        'unit_content' => se_api_plain($product['product_unit_content']),
        'meta_title' => se_api_plain($product['meta_title']),
        'meta_description' => se_api_plain($product['meta_description']),
        'released_at' => !empty($product['releasedate']) ? (int) $product['releasedate'] : null,
        'updated_at' => !empty($product['lastedit']) ? (int) $product['lastedit'] : null
    ];
}

/**
 * Price of a product for the API, null if prices are not public
 * mirrors se_get_product_price_tag() (price group, tax class), but returns
 * numbers instead of a formatted price tag and without the "lowest price
 * across variants" logic - variants are returned with their own price
 *
 * @param array $product
 * @return array|null ['net' => float, 'gross' => float, 'tax_rate' => float, 'currency' => string]
 */
function se_api_format_product_price(array $product): ?array {

    global $se_settings;

    // prices hidden for this product, or only shown to logged-in users
    if ((int) $product['product_pricetag_mode'] === 2 || (int) $se_settings['posts_price_visibility'] === 2) {
        return null;
    }

    if (!empty($product['product_price_group']) && $product['product_price_group'] !== 'null') {
        $price_data = se_get_price_group_data($product['product_price_group']);
        $product_tax = $price_data['tax'] ?? '';
        $product_price_net = $price_data['price_net'] ?? 0;
    } else {
        $product_tax = $product['product_tax'];
        $product_price_net = $product['product_price_net'];
    }

    if ($product_tax == '1') {
        $tax_rate = $se_settings['posts_products_default_tax'];
    } else if ($product_tax == '2') {
        $tax_rate = $se_settings['posts_products_tax_alt1'];
    } else {
        $tax_rate = $se_settings['posts_products_tax_alt2'];
    }

    $prices = se_posts_calc_price($product_price_net, $tax_rate);

    return [
        'net' => round((float) $prices['net_raw'], 2),
        'gross' => round((float) $prices['gross_raw'], 2),
        'tax_rate' => (float) $tax_rate,
        'currency' => $product['product_currency'] !== '' ? $product['product_currency'] : $se_settings['posts_products_default_currency']
    ];
}

/**
 * Get all API keys, newest first (without the hash)
 *
 * @return array
 */
function se_api_get_keys(): array {

    global $db_user;

    return $db_user->select('se_api_keys', [
        'id', 'label', 'key_prefix', 'scopes', 'is_active',
        'created_by', 'created_at', 'last_used_at', 'request_count'
    ], [
        'ORDER' => ['id' => 'DESC']
    ]);
}
