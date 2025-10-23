<?php

/**
 * @var array $se_settings
 * @var array $lang
 */

if (isset($_GET['calc']) && is_numeric($_GET['product_id'])) {

    // recalculate product price

    $get_product_id = (int)$_GET['product_id'];
    $get_amount = (int)$_GET['amount'];

    $product_data = se_get_product_data($get_product_id);

    // get price from price groups or from products data
    if ($product_data['product_price_group'] != '' and $product_data['product_price_group'] != 'null') {
        $price_data = se_get_price_group_data($product_data['product_price_group']);
        $product_tax = $price_data['tax'];
        $product_price_net = $price_data['price_net'];
        $product_volume_discounts_json = $price_data['price_volume_discount'];
    } else {
        $product_tax = $product_data['product_tax'];
        $product_price_net = $product_data['product_price_net'];
        $product_volume_discounts_json = $product_data['product_price_volume_discount'];
    }
    if ($product_tax == '1') {
        $tax = $se_settings['posts_products_default_tax'];
    } else if ($product_tax == '2') {
        $tax = $se_settings['posts_products_tax_alt1'];
    } else {
        $tax = $se_settings['posts_products_tax_alt2'];
    }

    $post_prices = se_posts_calc_price($product_price_net, $tax);
    $post_price_net = $post_prices['net'];
    $post_price_gross = $post_prices['gross'];


    if ($product_volume_discounts_json != '') {
        $product_volume_discounts = json_decode($product_volume_discounts_json, true);

        foreach ($product_volume_discounts as $k => $v) {
            if ($get_amount >= $v['amount']) {
                $vd_price = se_posts_calc_price($v['price'], $tax);
                $post_price_net = $vd_price['net'];
                $post_price_gross = $vd_price['gross'];
            }
        }
    }

    if ($se_settings['posts_price_mode'] == 1) {
        // gross prices
        $product_price_tag = $post_price_gross;
        $product_tax_label = $lang['price_tag_label_gross'];
    } else if ($se_settings['prefs_posts_price_mode'] == 2) {
        // gross and net prices
        $product_price_tag = $post_price_net . '/' . $post_price_gross;
        $product_tax_label = $lang['label_net'] . ' / ' . $lang['label_net'];
    } else {
        // net only (b2b mode)
        $product_price_tag = $post_price_net;
        $product_tax_label = $lang['price_tag_label_net'];
    }

    echo $product_price_tag;

}
