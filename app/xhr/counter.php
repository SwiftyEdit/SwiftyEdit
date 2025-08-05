<?php

/**
 * @var object $db_posts
 */

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
            "type" => "p",
            "categories" => $cat_array,
        ]
    ]);

    echo $cnt_products;
    exit;
}

if(isset($_GET['sc_items'])) {
    echo se_return_cart_amount();
    exit;
}