<?php

/**
 * Backfill product_canonical_url for existing products and variants.
 *
 * Canonical urls used to be recomputed on every frontend request in
 * app/handlers/products-display.php from main_catalog_slug + slug (with
 * variants always pointing at their parent product). The ACP now writes an
 * explicit product_canonical_url at save time instead - see
 * se_prepareProductData() in acp/core/functions_shop.php - so the frontend
 * only has to read the stored value. This migration gives every existing
 * row that value once, using the exact same formula the frontend used to
 * apply on the fly.
 *
 * $se_base_url isn't reliably available here (this migration can run from
 * the standalone install/ updater, which never loads app/bootstrap.php), so
 * it's rebuilt from se_options directly, mirroring app/bootstrap.php.
 *
 * Product cache files (SE_CONTENT/cache/products/*.json) are only ever
 * (re)written on save/cache-rebuild (see se_updateProductCache() in
 * acp/core/functions_shop.php), so any that already exist for these
 * products would keep serving the old, canonical-url-less JSON on the
 * frontend even after this migration fixes the database. Deleting them
 * here is safe: se_get_product_data() falls back to a fresh "SELECT *"
 * whenever a cache file is missing, and the cache is lazily rebuilt from
 * there (or explicitly via the ACP's cache-rebuild action).
 */

return function ($db_content, $db_user, $db_posts) {

    $options = $db_content->select('se_options', ['option_key', 'option_value'], [
        'option_module' => 'se',
    ]);

    $settings = [];
    foreach ($options as $option) {
        if (str_starts_with($option['option_key'], 'prefs_')) {
            $settings[substr($option['option_key'], 6)] = $option['option_value'];
        }
    }

    $se_base_url = !empty($settings['cms_ssl_domain'])
        ? $settings['cms_ssl_domain'] . $settings['cms_base']
        : $settings['cms_domain'] . $settings['cms_base'];

    $products = $db_posts->select('se_products', [
        'id', 'type', 'parent_id', 'slug', 'main_catalog_slug', 'product_canonical_url',
    ]);

    $by_id = [];
    foreach ($products as $product) {
        $by_id[$product['id']] = $product;
    }

    foreach ($products as $product) {
        if (!empty($product['product_canonical_url'])) {
            continue;
        }

        // variants inherit the (already backfilled-in-memory) parent's data
        $source = ($product['type'] === 'v' && isset($by_id[$product['parent_id']]))
            ? $by_id[$product['parent_id']]
            : $product;

        $canonical_url = $se_base_url . $source['main_catalog_slug'] . $source['slug'];

        $db_posts->update('se_products', [
            'product_canonical_url' => $canonical_url,
        ], [
            'id' => $product['id'],
        ]);
    }

    // drop stale product cache files so the fix above is actually visible
    // on the frontend right away (see note above)
    $cache_dir = SE_CONTENT . '/cache/products/';
    if (is_dir($cache_dir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($cache_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'json') {
                unlink($file->getPathname());
            }
        }
    }
};
