<?php

/**
 * @var object $db_posts
 */

// Read-only w.r.t. $_SESSION - safe to release the session lock immediately.
// This endpoint fires alongside other hx-trigger="load" widgets on the same
// page, all sharing one PHPSESSID; without an early close here, the
// default file session handler would force them to queue up and run one
// at a time instead of concurrently. Do not add $_SESSION writes below
// without removing this first.
session_write_close();

// count product filters
if(isset($_GET['filter']) && is_numeric($_GET['filter'])) {

    $get_filter = (int) $_GET['filter'];
    $get_categories = $_GET['categories'];
    $cat_array = explode(',', $get_categories);
    // count products with this filter
    // filters are stored in JSON, so we search like :"id"
    $cnt_products = $db_posts->count("se_products", [
        "AND" => [
            "filter[~]" => ':"' . $get_filter . '"',
            "status" => "1",
            "type" => ["p","v"],
            "categories[~]" => $cat_array
        ]
    ]);

    echo $cnt_products;
    exit;
}

// count items in the shopping cart
if(isset($_GET['sc_items'])) {
    echo se_return_cart_amount();
    exit;
}