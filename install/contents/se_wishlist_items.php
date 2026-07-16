<?php

/**
 * database for wishlist items (child rows of se_wishlists)
 *
 * normalized rather than a JSON blob so a single product delete is a
 * plain DELETE WHERE product_id=X, and item ordering can use a
 * SortableJS-driven position column
 */

$database = "content";
$table_name = "se_wishlist_items";

$cols = array(
    "id" => 'INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "wishlist_id" => 'INTEGER(12) NOT NULL',
    "product_id" => 'INTEGER(12) NOT NULL',
    "position" => 'INTEGER(12) NOT NULL DEFAULT 0',
    "item_product_href" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "added_at" => 'INTEGER(12) NOT NULL DEFAULT 0'
);
