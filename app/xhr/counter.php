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
// Deprecated: the default theme reads the counts from $item.count
// (see se_get_product_filter_counts()), which also respects the active
// filters. Kept for custom themes that still lazy-load the counters from
// here - to be removed in a later version.
if(isset($_GET['filter']) && is_numeric($_GET['filter'])) {

    $get_filter = (int) $_GET['filter'];

    // same rules as the product list, without the active filters
    $str_status = '1';
    if(isset($_SESSION['user_class']) && $_SESSION['user_class'] == 'administrator') {
        $str_status = '1-2';
    }

    $where = se_build_products_where([
        'status' => $str_status,
        'categories' => (string) ($_GET['categories'] ?? ''),
        'custom_filter_groups' => [[$get_filter]]
    ]);

    // variants count towards their parent product, each product only once
    $sql = "SELECT count(DISTINCT CASE WHEN type LIKE '%v%' THEN parent_id ELSE id END) AS cnt
            FROM se_products {$where['sql']}";
    $cnt_products = $db_posts->query($sql, $where['map'])->fetchColumn();

    echo (int) $cnt_products;
    exit;
}

// count items in the shopping cart
if(isset($_GET['sc_items'])) {
    echo se_return_cart_amount();
    exit;
}