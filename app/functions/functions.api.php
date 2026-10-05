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
 * @param array $options 'restricted_prices' => bool (key has products:prices),
 *                       'render' => bool (resolve snippets/shortcodes in texts)
 * @return array
 */
function se_api_format_product(array $product, array $options = []): array {

    $restricted_prices = !empty($options['restricted_prices']);
    $render = !empty($options['render']);

    // HTML as stored, or with snippets/shortcodes resolved on request (?render=1)
    $teaser = htmlspecialchars_decode((string) $product['teaser']);
    $text = htmlspecialchars_decode((string) $product['text']);
    if ($render) {
        // same placeholder values as on the product page
        $render_vars = [
            'page_title' => $product['meta_title'] != '' ? $product['meta_title'] : $product['title'],
            'page_url' => $product['product_canonical_url'],
            'sku' => $product['product_number']
        ];
        $teaser = se_api_render_text($teaser, $product['product_lang'], $render_vars);
        $text = se_api_render_text($text, $product['product_lang'], $render_vars);
    }

    return [
        'id' => (int) $product['id'],
        'uuid' => $product['uuid'] !== '' ? $product['uuid'] : null,
        'type' => $product['type'] === 'v' ? 'variant' : 'product',
        'parent_id' => $product['type'] === 'v' ? (int) $product['parent_id'] : null,
        'lang' => $product['product_lang'],
        'title' => se_api_plain($product['title']),
        'variant_title' => se_api_plain($product['product_variant_title']),
        'teaser' => $teaser,
        'text' => $text,
        'slug' => $product['slug'],
        'url' => $product['product_canonical_url'],
        'images' => se_api_image_urls($product['images']),
        'categories' => se_api_categories($product['categories']),
        'tags' => se_api_tags('product', (int) $product['id']),
        'product_number' => se_api_plain($product['product_number']),
        'manufacturer' => se_api_plain($product['product_manufacturer']),
        'ean' => $product['product_ean'],
        'mpn' => se_api_plain($product['product_mpn']),
        'price' => se_api_format_product_price($product, $restricted_prices),
        'unit' => se_api_plain($product['product_unit']),
        'unit_content' => se_api_plain($product['product_unit_content']),
        'meta_title' => se_api_plain($product['meta_title']),
        'meta_description' => se_api_plain($product['meta_description']),
        // the "tags" column holds the meta keywords, tags live in se_tags_relations
        'meta_keywords' => se_api_plain($product['tags']),
        'released_at' => !empty($product['releasedate']) ? (int) $product['releasedate'] : null,
        'updated_at' => !empty($product['lastedit']) ? (int) $product['lastedit'] : null
    ];
}

/**
 * Absolute URLs for a "<->" separated list of image paths ("/images/foo.jpg")
 *
 * @param mixed $images
 * @return array
 */
function se_api_image_urls(mixed $images): array {

    global $se_base_url;

    $urls = [];
    foreach (explode('<->', (string) $images) as $image) {
        $image = trim($image);
        // "null" is stored as a placeholder for "no image" (e.g. category thumbnails)
        if ($image !== '' && $image !== 'null') {
            $urls[] = rtrim($se_base_url, '/') . '/' . ltrim($image, '/');
        }
    }
    return $urls;
}

/**
 * Categories of a record - stored as "<->" separated cat_hash values
 *
 * @param mixed $hashes
 * @return array list of ['name' => string, 'slug' => string]
 */
function se_api_categories(mixed $hashes): array {

    $record_hashes = array_filter(explode('<->', (string) $hashes));
    if (empty($record_hashes)) {
        return [];
    }

    $categories = [];
    foreach (se_get_categories() as $category) {
        if (in_array($category['cat_hash'], $record_hashes, true)) {
            $categories[] = [
                'name' => se_api_plain($category['cat_name']),
                'slug' => $category['cat_name_clean']
            ];
        }
    }
    return $categories;
}

/**
 * All cat_hash values of a category slug - a slug can exist once per
 * language, so it can stand for several categories
 *
 * @param string $slug cat_name_clean
 * @return array
 */
function se_api_category_hashes(string $slug): array {

    $hashes = [];
    foreach (se_get_categories() as $category) {
        if ($category['cat_name_clean'] === $slug) {
            $hashes[] = $category['cat_hash'];
        }
    }
    return $hashes;
}

/**
 * Tags of a record, from se_tags_relations (same source as the frontend)
 *
 * @param string $type 'post' | 'product' | ...
 * @param int $id
 * @return array list of ['name' => string, 'slug' => string]
 */
function se_api_tags(string $type, int $id): array {

    $tags = [];
    foreach (se_get_content_tags($type, $id) as $tag) {
        $tags[] = [
            'name' => se_api_plain($tag['tag_name']),
            'slug' => $tag['tag_name_clean']
        ];
    }
    return $tags;
}

/**
 * Resolve snippets and shortcodes in a text for the API (?render=1)
 *
 * A reduced version of text_parser() (app/functions/func_basics.php):
 * - snippets are loaded in the record's language, not the visitor's
 * - placeholders get the same values as on the record's detail page
 * - [script], [plugin] and [include] are left untouched - they execute PHP
 *   or read files and produce theme markup that is meaningless for an
 *   external client
 * - theme_text_parser() and the admin helpers are not used
 *
 * @param string $html decoded HTML
 * @param string $lang language of the record
 * @param array $vars 'page_title', 'page_url', 'sku' of the record
 * @return string
 */
function se_api_render_text(string $html, string $lang, array $vars): string {

    global $se_settings;
    static $shortcodes = null;

    if ($html === '') {
        return '';
    }

    // placeholders, same values as on the detail page
    // (app/template-setup.php + app/handlers/products-display.php)
    se_set_snippet_var('site_name', $se_settings['pagename'] ?? '');
    se_set_snippet_var('page_title', $vars['page_title'] ?? '');
    se_set_snippet_var('page_url', $vars['page_url'] ?? '');
    se_set_snippet_var('date', date($se_settings['dateformat']));
    se_set_snippet_var('time', date($se_settings['timeformat']));
    se_set_snippet_var('date_iso', date('Y-m-d'));
    se_set_snippet_var('year', date('Y'));
    se_set_snippet_var('sku', $vars['sku'] ?? '');

    // remove <p> tags around shortcodes, like text_parser()
    $html = str_replace(['<p>[', ']</p>'], ['[', ']'], $html);

    // shortcodes inside <pre> and <code> stay as they are
    $html = preg_replace_callback(
        '#<(pre|code)\b[^>]*>.*?</\1>#si',
        fn($m) => str_replace(['[', ']'], ['&#91;', '&#93;'], $m[0]),
        $html
    );

    // [snippet]name[/snippet]
    $html = preg_replace_callback(
        '/\[snippet\](.*?)\[\/snippet\]/si',
        fn($m) => se_get_snippet($m[1], $lang, 'content'),
        $html
    );

    // [snippet=name]tpl[/snippet] or [snippet=name]...[/snippet]
    $html = preg_replace_callback(
        '/\[snippet=(.*?)\](.*?)\[\/snippet\]/si',
        fn($m) => se_get_snippet($m[1], $lang, $m[2] == 'tpl' ? 'tpl' : 'content'),
        $html
    );

    // [snippet=name]
    $html = preg_replace_callback(
        '/\[snippet=(.*?)\]/si',
        fn($m) => se_get_snippet($m[1], $lang, 'content'),
        $html
    );

    // shortcodes are not language specific, same as in text_parser()
    if ($shortcodes === null) {
        $shortcodes = se_get_shortcodes();
    }
    foreach ($shortcodes as $shortcode) {
        if ($shortcode['snippet_shortcode'] != '') {
            $html = str_replace($shortcode['snippet_shortcode'], se_replace_snippet_vars($shortcode['snippet_content']), $html);
        }
    }

    return $html;
}

/**
 * Price of a product for the API, null if prices are not public
 * mirrors se_get_product_price_tag() (price group, tax class), but returns
 * numbers instead of a formatted price tag and without the "lowest price
 * across variants" logic - variants are returned with their own price
 *
 * @param array $product
 * @param bool $restricted_prices the key may see prices that the shop only
 *                                shows to logged-in customers (products:prices)
 * @return array|null ['net' => float, 'gross' => float, 'tax_rate' => float, 'currency' => string]
 */
function se_api_format_product_price(array $product, bool $restricted_prices = false): ?array {

    global $se_settings;

    // price hidden for this product - a display decision, applies to every key
    if ((int) $product['product_pricetag_mode'] === 2) {
        return null;
    }

    // prices only for logged-in customers - needs the products:prices scope
    if ((int) $se_settings['posts_price_visibility'] === 2 && !$restricted_prices) {
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
 * Turn a blog post row into its public API representation
 * whitelist only - internal columns (editor, hits, template values, ...)
 * must never reach the API
 *
 * @param array $post row from se_posts
 * @param array $options 'render' => bool (resolve snippets/shortcodes in texts)
 * @return array
 */
function se_api_format_post(array $post, array $options = []): array {

    global $se_base_url;

    $types = ['m' => 'message', 'i' => 'image', 'g' => 'gallery', 'v' => 'video', 'l' => 'link', 'f' => 'file'];
    $base = rtrim($se_base_url, '/') . '/';

    // no detail page -> no url, like the post_href in posts-list.php
    $url = $post['post_canonical_url'];
    if ((int) $post['post_hide_detail_page'] === 1 || $url === '') {
        $url = null;
    }

    // HTML as stored, or with snippets/shortcodes resolved on request (?render=1)
    $teaser = htmlspecialchars_decode((string) $post['post_teaser']);
    $text = htmlspecialchars_decode((string) $post['post_text']);
    if (!empty($options['render'])) {
        $render_vars = [
            'page_title' => $post['post_meta_title'] != '' ? $post['post_meta_title'] : $post['post_title'],
            'page_url' => (string) $url
        ];
        $teaser = se_api_render_text($teaser, $post['post_lang'], $render_vars);
        $text = se_api_render_text($text, $post['post_lang'], $render_vars);
    }

    // gallery images live in public/assets/galleries/{year of post_date}/gallery{id}/
    // (see posts-display.php), newest first like in the frontend
    $gallery = [];
    if ($post['post_type'] === 'g') {
        $gallery_dir = 'assets/galleries/' . date('Y', (int) $post['post_date']) . '/gallery' . (int) $post['post_id'] . '/';
        $gallery_files = glob(SE_ROOT . 'public/' . $gallery_dir . '*_img.jpg') ?: [];
        rsort($gallery_files);
        foreach ($gallery_files as $gallery_file) {
            $gallery[] = $base . $gallery_dir . basename($gallery_file);
        }
    }

    $link = null;
    if ($post['post_link'] !== '') {
        $link = [
            'url' => $post['post_link'],
            'text' => se_api_plain($post['post_link_text'])
        ];
    }

    $file = null;
    if ($post['post_file_attachment'] !== '' || $post['post_file_attachment_external'] !== '') {
        $file = [
            // stored relative, e.g. "../files/manual.pdf" (see posts-display.php)
            'url' => $post['post_file_attachment'] !== '' ? $base . ltrim(str_replace('../', '/', $post['post_file_attachment']), '/') : null,
            'external_url' => $post['post_file_attachment_external'] !== '' ? $post['post_file_attachment_external'] : null,
            'license' => se_api_plain($post['post_file_license']),
            'version' => se_api_plain($post['post_file_version'])
        ];
    }

    return [
        'id' => (int) $post['post_id'],
        'uuid' => $post['post_uuid'] !== '' ? $post['post_uuid'] : null,
        'type' => $types[$post['post_type']] ?? $post['post_type'],
        'lang' => $post['post_lang'],
        'title' => se_api_plain($post['post_title']),
        'teaser' => $teaser,
        'text' => $text,
        'slug' => $post['post_slug'],
        'url' => $url,
        'images' => se_api_image_urls($post['post_images']),
        'gallery' => $gallery,
        'video_url' => $post['post_video_url'] !== '' ? $post['post_video_url'] : null,
        'link' => $link,
        'file' => $file,
        'categories' => se_api_categories($post['post_categories']),
        'tags' => se_api_tags('post', (int) $post['post_id']),
        'author' => se_api_plain($post['post_author']),
        'source' => se_api_plain($post['post_source']),
        'meta_title' => se_api_plain($post['post_meta_title']),
        'meta_description' => se_api_plain($post['post_meta_description']),
        // like products, the "post_tags" column holds the meta keywords
        'meta_keywords' => se_api_plain($post['post_tags']),
        'released_at' => !empty($post['post_releasedate']) ? (int) $post['post_releasedate'] : null,
        'updated_at' => !empty($post['post_lastedit']) ? (int) $post['post_lastedit'] : null
    ];
}

/**
 * Turn an event row into its public API representation
 * whitelist only - internal columns (editor, hits, template values, ...)
 * must never reach the API
 *
 * @param array $event row from se_events
 * @param array $options 'render' => bool (resolve snippets/shortcodes in texts)
 * @return array
 */
function se_api_format_event(array $event, array $options = []): array {

    // HTML as stored, or with snippets/shortcodes resolved on request (?render=1)
    $teaser = htmlspecialchars_decode((string) $event['teaser']);
    $text = htmlspecialchars_decode((string) $event['text']);
    if (!empty($options['render'])) {
        $render_vars = [
            'page_title' => $event['meta_title'] != '' ? $event['meta_title'] : $event['title'],
            'page_url' => $event['canonical_url']
        ];
        $teaser = se_api_render_text($teaser, $event['event_lang'], $render_vars);
        $text = se_api_render_text($text, $event['event_lang'], $render_vars);
    }

    // event_guestlist: 1 = deactivated, 2 = registered users, 3 = everybody
    // the number of confirmations is only public if event_guestlist_public_nbr = 2
    // (same as on the event page, see events-display.php)
    $guestlist = null;
    if ((int) $event['event_guestlist'] === 2 || (int) $event['event_guestlist'] === 3) {
        $confirmed = null;
        if ((int) $event['event_guestlist_public_nbr'] === 2) {
            $confirmed = (int) se_get_event_confirmation_data($event['id'])['evc'];
        }
        $guestlist = [
            'access' => (int) $event['event_guestlist'] === 2 ? 'registered' : 'everybody',
            'limit' => $event['event_guestlist_limit'] !== '' ? (int) $event['event_guestlist_limit'] : null,
            'confirmed' => $confirmed
        ];
    }

    return [
        'id' => (int) $event['id'],
        'uuid' => $event['uuid'] !== '' ? $event['uuid'] : null,
        'lang' => $event['event_lang'],
        'title' => se_api_plain($event['title']),
        'teaser' => $teaser,
        'text' => $text,
        'slug' => $event['slug'],
        'url' => $event['canonical_url'] !== '' ? $event['canonical_url'] : null,
        'start_at' => !empty($event['event_startdate']) ? (int) $event['event_startdate'] : null,
        'end_at' => !empty($event['event_enddate']) ? (int) $event['event_enddate'] : null,
        'location' => [
            'street' => se_api_plain($event['event_street']),
            'street_nbr' => se_api_plain($event['event_street_nbr']),
            'zip' => se_api_plain($event['event_zip']),
            'city' => se_api_plain($event['event_city'])
        ],
        // HTML, decoded the same way as on the event page
        'price_note' => html_entity_decode((string) $event['event_price_note'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'guestlist' => $guestlist,
        'images' => se_api_image_urls($event['images']),
        'categories' => se_api_categories($event['categories']),
        'tags' => se_api_tags('event', (int) $event['id']),
        'author' => se_api_plain($event['author']),
        'meta_title' => se_api_plain($event['meta_title']),
        'meta_description' => se_api_plain($event['meta_description']),
        // like products and posts, the "tags" column holds the meta keywords
        'meta_keywords' => se_api_plain($event['tags']),
        'released_at' => !empty($event['releasedate']) ? (int) $event['releasedate'] : null,
        'updated_at' => !empty($event['lastedit']) ? (int) $event['lastedit'] : null
    ];
}

/**
 * Turn a page row into its public API representation
 * whitelist only - internal columns (password hash, user groups, editor
 * source, template settings, ...) must never reach the API
 *
 * @param array $page row from se_pages
 * @param array $options 'render' => bool (resolve snippets/shortcodes in the content)
 * @return array
 */
function se_api_format_page(array $page, array $options = []): array {

    global $se_base_url;

    // on SQLite, columns a page was saved without are NULL rather than ''
    // (the schema generator drops defaults) - normalize everything but the
    // parent id, where NULL means "no parent"
    foreach ($page as $key => $value) {
        if ($value === null && $key !== 'page_parent_id') {
            $page[$key] = '';
        }
    }

    $base = rtrim($se_base_url, '/') . '/';
    $url = $page['page_canonical_url'] !== '' ? $page['page_canonical_url'] : $base . ltrim($page['page_permalink'], '/');

    // final HTML (see install/contents/se_pages.php) - stripslashes() like
    // the frontend does in app/template-setup.php
    $content = stripslashes((string) $page['page_content']);
    if (!empty($options['render'])) {
        $content = se_api_render_text($content, $page['page_language'], [
            'page_title' => $page['page_title'],
            'page_url' => $url
        ]);
    }

    // {"de":"/de/seite/","en":""} - stored as JSON, possibly HTML-encoded
    $translations = [];
    $translation_urls = json_decode(html_entity_decode((string) $page['page_translation_urls'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
    if (is_array($translation_urls)) {
        foreach ($translation_urls as $translation_lang => $translation_url) {
            if (is_string($translation_url) && $translation_url !== '') {
                $translations[$translation_lang] = str_starts_with($translation_url, 'http') ? $translation_url : $base . ltrim($translation_url, '/');
            }
        }
    }

    // tree, see install/contents/se_pages.php: pages without a parent are
    // either the language's home page ("portal") or single pages that are
    // not part of the navigation
    $parent_id = ($page['page_parent_id'] !== null && $page['page_parent_id'] !== '') ? (int) $page['page_parent_id'] : null;
    $is_home = $page['page_sort'] === 'portal';
    $in_navigation = $page['page_status'] === 'public' && ($parent_id !== null || $is_home);

    $redirect = null;
    if ($page['page_redirect'] !== '') {
        $redirect = [
            'url' => $page['page_redirect'],
            'code' => $page['page_redirect_code'] !== '' ? (int) $page['page_redirect_code'] : null
        ];
    }

    return [
        'id' => (int) $page['page_id'],
        'lang' => $page['page_language'],
        'title' => se_api_plain($page['page_title']),
        'linkname' => se_api_plain($page['page_linkname']),
        'content' => $content,
        'slug' => $page['page_permalink'],
        'url' => $url,
        'parent_id' => $parent_id,
        'position' => (int) $page['position'],
        'is_home' => $is_home,
        'in_navigation' => $in_navigation,
        'type' => $page['page_type_of_use'] !== '' ? $page['page_type_of_use'] : 'normal',
        'module' => $page['page_modul'] !== '' ? $page['page_modul'] : null,
        'redirect' => $redirect,
        'target' => $page['page_target'] !== '' ? $page['page_target'] : null,
        'translations' => (object) $translations,
        'images' => se_api_image_urls($page['page_thumbnail']),
        'tags' => se_api_tags('page', (int) $page['page_id']),
        'meta_description' => se_api_plain($page['page_meta_description']),
        'meta_keywords' => se_api_plain($page['page_meta_keywords']),
        'meta_robots' => $page['page_meta_robots'],
        'meta_author' => se_api_plain($page['page_meta_author']),
        'updated_at' => !empty($page['page_lastedit']) ? (int) $page['page_lastedit'] : null
    ];
}

/**
 * Turn a category row into its public API representation
 * whitelist only - template settings must never reach the API
 *
 * @param array $category row from se_categories
 * @param array $options 'render' => bool (resolve snippets/shortcodes in texts)
 * @return array
 */
function se_api_format_category(array $category, array $options = []): array {

    // HTML as stored (the category pages output it as is), or with
    // snippets/shortcodes resolved on request (?render=1)
    $teaser = (string) $category['cat_teaser'];
    $text = (string) $category['cat_text'];
    if (!empty($options['render'])) {
        $render_vars = [
            'page_title' => $category['cat_title'] != '' ? $category['cat_title'] : $category['cat_name']
        ];
        $teaser = se_api_render_text($teaser, (string) $category['cat_lang'], $render_vars);
        $text = se_api_render_text($text, (string) $category['cat_lang'], $render_vars);
    }

    return [
        'id' => (int) $category['cat_id'],
        'uuid' => ($category['uuid'] ?? '') !== '' ? $category['uuid'] : null,
        'lang' => $category['cat_lang'],
        'name' => se_api_plain($category['cat_name']),
        'slug' => $category['cat_name_clean'],
        'title' => se_api_plain($category['cat_title']),
        'teaser' => $teaser,
        'text' => $text,
        'description' => se_api_plain($category['cat_description']),
        'keywords' => se_api_plain($category['cat_keywords']),
        'images' => se_api_image_urls($category['cat_thumbnail']),
        'sort' => (int) $category['cat_sort']
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
