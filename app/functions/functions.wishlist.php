<?php

/**
 * functions for wishlists (se_wishlists / se_wishlist_items)
 *
 * lists are owned by logged-in users only (no guest ownership).
 * a list's slug is a random uuid generated once at creation and never
 * regenerated, independent of its is_public state.
 */

// ----- CRUD -----

/**
 * create a new wishlist for the given (logged-in) user
 * @param int $user_id
 * @param string $name
 * @return int the new wishlist id, or 0 on failure
 */
function se_create_wishlist(int $user_id, string $name): int {

    global $db_content;

    if($user_id < 1 || trim($name) === '') {
        return 0;
    }

    $db_content->insert("se_wishlists", [
        "user_id" => $user_id,
        "name" => trim($name),
        "is_public" => 0,
        "slug" => se_generate_uuid(),
        "created_at" => time()
    ]);

    return (int) $db_content->id();
}

/**
 * get all wishlists belonging to a user, each enriched with item_count
 * @param int $user_id
 * @return array
 */
function se_get_user_wishlists(int $user_id): array {

    global $db_content;

    if($user_id < 1) {
        return [];
    }

    $lists = $db_content->select("se_wishlists", "*", [
        "user_id" => $user_id,
        "ORDER" => ["created_at" => "ASC"]
    ]);

    if(!is_array($lists)) {
        return [];
    }

    foreach($lists as $key => $list) {
        $lists[$key]['item_count'] = $db_content->count("se_wishlist_items", [
            "wishlist_id" => $list['id']
        ]);
    }

    return $lists;
}

/**
 * get one wishlist by id, but only if it is owned by $user_id
 * @param int $wishlist_id
 * @param int $user_id
 * @return array|null
 */
function se_get_wishlist_by_id(int $wishlist_id, int $user_id): ?array {

    global $db_content;

    if($wishlist_id < 1 || $user_id < 1) {
        return null;
    }

    $list = $db_content->get("se_wishlists", "*", [
        "id" => $wishlist_id,
        "user_id" => $user_id
    ]);

    return is_array($list) ? $list : null;
}

/**
 * get one wishlist by its public slug, regardless of owner. caller must
 * additionally check is_public before showing it publicly.
 * @param string $slug
 * @return array|null
 */
function se_get_wishlist_by_slug(string $slug): ?array {

    global $db_content;

    // slugs are stored without a trailing slash (se_generate_uuid()),
    // unlike product slugs - normalize whatever the caller passes in
    $slug = trim($slug, "/ \t\n\r\0\x0B");

    if($slug === '') {
        return null;
    }

    $list = $db_content->get("se_wishlists", "*", [
        "slug" => $slug
    ]);

    return is_array($list) ? $list : null;
}

/**
 * rename a wishlist (ownership-checked)
 * @param int $wishlist_id
 * @param int $user_id
 * @param string $new_name
 * @return bool
 */
function se_rename_wishlist(int $wishlist_id, int $user_id, string $new_name): bool {

    global $db_content;

    if(trim($new_name) === '' || se_get_wishlist_by_id($wishlist_id, $user_id) === null) {
        return false;
    }

    $db_content->update("se_wishlists", [
        "name" => trim($new_name)
    ], [
        "id" => $wishlist_id,
        "user_id" => $user_id
    ]);

    return true;
}

/**
 * set the is_public flag (ownership-checked). the slug is never touched here.
 * @param int $wishlist_id
 * @param int $user_id
 * @param bool $is_public
 * @return bool
 */
function se_set_wishlist_visibility(int $wishlist_id, int $user_id, bool $is_public): bool {

    global $db_content;

    if(se_get_wishlist_by_id($wishlist_id, $user_id) === null) {
        return false;
    }

    $db_content->update("se_wishlists", [
        "is_public" => $is_public ? 1 : 0
    ], [
        "id" => $wishlist_id,
        "user_id" => $user_id
    ]);

    return true;
}

/**
 * delete a wishlist and all of its items (ownership-checked)
 * @param int $wishlist_id
 * @param int $user_id
 * @return bool
 */
function se_delete_wishlist(int $wishlist_id, int $user_id): bool {

    global $db_content;

    if(se_get_wishlist_by_id($wishlist_id, $user_id) === null) {
        return false;
    }

    $db_content->delete("se_wishlist_items", [
        "wishlist_id" => $wishlist_id
    ]);

    $db_content->delete("se_wishlists", [
        "id" => $wishlist_id,
        "user_id" => $user_id
    ]);

    return true;
}

// ----- items -----

/**
 * add a product to a wishlist (ownership-checked). no-ops (returns the
 * existing item id) if the product is already in this list.
 *
 * $product_href is captured verbatim at add-time (same trick as
 * cart_product_slug on se_carts, see se_add_to_cart()) rather than
 * recomputed later - re-deriving it from se_get_type_of_use_pages()/
 * $swifty_slug at render time breaks as soon as the item is rendered
 * from a page other than the shop itself (e.g. the wishlist page).
 *
 * @param int $wishlist_id
 * @param int $user_id
 * @param int $product_id
 * @param string $product_href the product_href value submitted alongside
 *        the "add to wishlist" action (same hidden field used by add-to-cart)
 * @return int item id (new or existing), or 0 on failure/not owner
 */
function se_add_wishlist_item(int $wishlist_id, int $user_id, int $product_id, string $product_href = ''): int {

    global $db_content;

    if($product_id < 1 || se_get_wishlist_by_id($wishlist_id, $user_id) === null) {
        return 0;
    }

    $existing = $db_content->get("se_wishlist_items", "id", [
        "wishlist_id" => $wishlist_id,
        "product_id" => $product_id
    ]);

    if(is_numeric($existing)) {
        return (int) $existing;
    }

    $next_position = (int) $db_content->count("se_wishlist_items", [
        "wishlist_id" => $wishlist_id
    ]);

    $db_content->insert("se_wishlist_items", [
        "wishlist_id" => $wishlist_id,
        "product_id" => $product_id,
        "position" => $next_position,
        "item_product_href" => $product_href !== '' ? htmlspecialchars($product_href, ENT_QUOTES, 'UTF-8') : '#',
        "added_at" => time()
    ]);

    return (int) $db_content->id();
}

/**
 * remove one item (by item id) from a wishlist (ownership-checked)
 * @param int $item_id
 * @param int $user_id
 * @return bool
 */
function se_remove_wishlist_item(int $item_id, int $user_id): bool {

    global $db_content;

    if($item_id < 1) {
        return false;
    }

    $item = $db_content->get("se_wishlist_items", "*", [
        "id" => $item_id
    ]);

    if(!is_array($item) || se_get_wishlist_by_id((int) $item['wishlist_id'], $user_id) === null) {
        return false;
    }

    $db_content->delete("se_wishlist_items", [
        "id" => $item_id
    ]);

    return true;
}

/**
 * persist a new item order after a SortableJS drag (ownership-checked)
 * @param int $wishlist_id
 * @param int $user_id
 * @param int[] $ordered_item_ids the full new order of item ids for this list
 * @return bool
 */
function se_reorder_wishlist_items(int $wishlist_id, int $user_id, array $ordered_item_ids): bool {

    global $db_content;

    if(se_get_wishlist_by_id($wishlist_id, $user_id) === null) {
        return false;
    }

    foreach($ordered_item_ids as $position => $item_id) {
        $db_content->update("se_wishlist_items", [
            "position" => $position
        ], [
            "id" => (int) $item_id,
            "wishlist_id" => $wishlist_id
        ]);
    }

    return true;
}

/**
 * compute the price tag for a product row, mirroring the "cheapest price
 * wins" logic in app/handlers/products-list.php (lines ~604-634): shows
 * the lowest volume-discount/variant price with a "from" label if it
 * undercuts the base price, otherwise the base price - formatted the
 * same way (gross/net/both) as the shop listing, per posts_price_mode.
 * @param array $product a product row from se_get_product_data()
 * @return array ['price_tag' => string, 'price_tag_label_from' => string]
 */
function se_get_wishlist_item_price_tag(array $product): array {

    global $se_settings, $lang;

    $product_tax = $product['product_tax'] ?? '';
    if($product_tax == '1') {
        $tax = $se_settings['posts_products_default_tax'];
    } else if($product_tax == '2') {
        $tax = $se_settings['posts_products_tax_alt1'];
    } else {
        $tax = $se_settings['posts_products_tax_alt2'];
    }

    $product_price_net = $product['product_price_net'] ?? 0;
    $price_tag_label_from = '';

    $lowest_price = se_get_product_lowest_price((int) ($product['id'] ?? 0));
    if($lowest_price !== null) {
        if(se_commaToFloat($lowest_price) < se_commaToFloat($product_price_net)) {
            $price_tag_label_from = $lang['price_tag_label_from'];
        }
        $product_price_net = $lowest_price;
    }

    $post_prices = se_posts_calc_price($product_price_net, $tax);

    if($se_settings['posts_price_mode'] == 1) {
        $price_tag = $post_prices['gross'];
    } else if($se_settings['posts_price_mode'] == 2) {
        $price_tag = $post_prices['net'] . '/' . $post_prices['gross'];
    } else {
        $price_tag = $post_prices['net'];
    }

    return [
        'price_tag' => $price_tag,
        'price_tag_label_from' => $price_tag_label_from
    ];
}

/**
 * get all items of a wishlist, enriched with product data, ordered by position
 * @param int $wishlist_id
 * @return array
 */
function se_get_wishlist_items(int $wishlist_id): array {

    global $db_content;

    if($wishlist_id < 1) {
        return [];
    }

    $items = $db_content->select("se_wishlist_items", "*", [
        "wishlist_id" => $wishlist_id,
        "ORDER" => ["position" => "ASC"]
    ]);

    if(!is_array($items)) {
        return [];
    }

    foreach($items as $key => $item) {
        $product = se_get_product_data((int) $item['product_id']);
        if(is_array($product)) {
            $product['product_teaser'] = htmlspecialchars_decode($product['teaser'] ?? '');
            $post_images = explode("<->", $product['images'] ?? '');
            $product['product_img_src'] = ($post_images[1] ?? '') !== '' ? $post_images[1] : '';
            $product = array_merge($product, se_get_wishlist_item_price_tag($product));
        }
        $items[$key]['product'] = $product;
        $items[$key]['product_href'] = ($item['item_product_href'] ?? '') !== '' ? $item['item_product_href'] : '#';
    }

    return $items;
}

/**
 * check whether a product is already saved in any of the user's wishlists.
 * intended for the PDP only (single product) - do not call this per product
 * in a listing loop, it would add an N+1 query per product card.
 * @param int $user_id
 * @param int $product_id
 * @return bool
 */
function se_product_in_any_wishlist(int $user_id, int $product_id): bool {

    global $db_content;

    if($user_id < 1 || $product_id < 1) {
        return false;
    }

    $count = $db_content->count("se_wishlist_items", [
        "product_id" => $product_id,
        "wishlist_id" => $db_content->select("se_wishlists", "id", [
            "user_id" => $user_id
        ])
    ]);

    return $count > 0;
}

/**
 * cascade-cleanup: remove all wishlist items referencing a deleted product.
 * called from acp/core/shop/data-writer.php's delete_product branch.
 * @param int $product_id
 * @return void
 */
function se_delete_wishlist_items_by_product(int $product_id): void {

    global $db_content;

    if($product_id < 1) {
        return;
    }

    $db_content->delete("se_wishlist_items", [
        "product_id" => $product_id
    ]);
}

// ----- hook registration -----

/**
 * registers this feature's frontend hook callback(s). called once from
 * routing.php right after hooks-frontend.php is required - $se_settings is
 * fully populated by then, functions.php (where this file lives) is not
 * loaded yet at that point in the bootstrap, so hooks can't be registered
 * at top-level include time.
 * @return void
 */
function se_wishlist_register_hooks(): void {

    se_add_frontend_hook('product.display.actions', function($actions, $context) {

        global $se_settings, $lang;

        if(($se_settings['wishlist_enabled'] ?? 0) != 1) {
            return $actions;
        }

        $product_id = (int) ($context['product_id'] ?? 0);
        if($product_id < 1) {
            return $actions;
        }

        $logged_in = ($_SESSION['user_nick'] ?? '') !== '';

        if(!$logged_in) {
            $profile_page = se_get_type_of_use_pages('profile');
            $actions[] = [
                'type' => 'link',
                'label' => $lang['btn_login_to_save_wishlist'],
                'class' => 'btn btn-outline-secondary btn-sm',
                'href' => '/' . ($profile_page['page_permalink'] ?? '')
            ];
            return $actions;
        }

        $already_saved = se_product_in_any_wishlist((int) $_SESSION['user_id'], $product_id);

        $actions[] = [
            'type' => 'button_htmx',
            'label' => $lang['btn_add_to_wishlist'],
            'class' => 'btn btn-outline-danger btn-sm' . ($already_saved ? ' active' : ''),
            'hx_get' => '/xhr/se/wishlist/?form=picker&product_id=' . $product_id,
            'hx_target' => '#wishlist-picker-modal-body',
            'hx_swap' => 'innerHTML'
        ];

        return $actions;
    });
}
