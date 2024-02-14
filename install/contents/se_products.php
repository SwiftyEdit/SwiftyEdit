<?php

/**
 * type -> p = product v = variant
 * linked_ids -> ids from variant products
 * parent_id -> if it's a variant store here the parent id
 *
 * status -> 1 = public, 2 = draft
 * rss -> 1 = yes, 2 = no
 * fixed -> 1 = yes, 2 = no
 * votings -> 1 = no, 2 = yes for registered useres, 3 = yes for everybody
 * product_cart_mode -> empty || null || 1 = on, 2 = deactivated
 * product_pricetag_mode -> empty || null || 1 = on, 2 = deactivated
 *
 * product_price_net_purchasing -> purchasing price
 * product_price_addition -> how much would you like to add to the purchase price (in %)
 * product_price_volume_discount -> json for volume discounts
 *
 * product_options -> json for options
 * product_options_comment_label -> if we need customer instruction or feedback for this product
 *
 * file_attachment_user -> 1 || empty || null = no, 2 = yes
 *
 */

$database = "posts";
$table_name = "se_products";

$cols = array(
    "id" => 'INTEGER(50) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "type" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "linked_ids" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "parent_id"  => 'INTEGER(50)',
    "date"  => 'INTEGER(12)',
    "releasedate"  => 'INTEGER(12)',
    "lastedit"  => 'INTEGER(12)',
    "lastedit_from"  => "VARCHAR(50) NOT NULL DEFAULT ''",
    "link_name" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "link_classes" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "title" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "teaser" => "LONGTEXT NOT NULL DEFAULT ''",
    "text" => "LONGTEXT NOT NULL DEFAULT ''",
    "text_label" => "VARCHAR(255) NOT NULL DEFAULT ''",

    /* additional sections for content */
    "text_additional1" => "LONGTEXT NOT NULL DEFAULT ''",
    "text_additional1_label" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "text_additional2" => "LONGTEXT NOT NULL DEFAULT ''",
    "text_additional2_label" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "text_additional3" => "LONGTEXT NOT NULL DEFAULT ''",
    "text_additional3_label" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "text_additional4" => "LONGTEXT NOT NULL DEFAULT ''",
    "text_additional4_label" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "text_additional5" => "LONGTEXT NOT NULL DEFAULT ''",
    "text_additional5_label" => "VARCHAR(255) NOT NULL DEFAULT ''",

    "images" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "tags" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "categories" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "filter" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "author" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "status" => 'INTEGER(12)',
    "rss" => 'INTEGER(12)',
    "rss_url" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "product_lang" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "slug" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "translation_urls" => "LONGTEXT NOT NULL DEFAULT ''",
    "priority" => 'INTEGER(12)',
    "fixed" => 'INTEGER(12)',
    "hits" => 'INTEGER(12)',
    "votings" => 'INTEGER(12)',
    "labels" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "attachments" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "template_values" => "LONGTEXT NOT NULL DEFAULT ''",
    "notes" => "LONGTEXT NOT NULL DEFAULT ''",
    /* meta data */
    "meta_title" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "meta_description" => "VARCHAR(255) NOT NULL DEFAULT ''",

    /* product data */
    "product_number" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "product_manufacturer" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "product_supplier" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "product_tax" => 'INTEGER(12)',
    "product_price_net_purchasing" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "product_price_net" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "product_price_volume_discount" => "LONGTEXT NOT NULL DEFAULT ''",
    "product_price_group" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "product_features_label" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "product_features" => "LONGTEXT NOT NULL DEFAULT ''",
    "product_features_values" => "LONGTEXT NOT NULL DEFAULT ''",
    "product_options" => "LONGTEXT NOT NULL DEFAULT ''",
    "product_options_comment_label" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "product_variant_title" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "product_variant_description" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "product_shipping_mode" => 'INTEGER(12)',
    "product_shipping_cat" => 'INTEGER(12)',
    "product_cnt_sales" => 'INTEGER(12)',
    "product_nbr_stock" => 'INTEGER(12)',
    "product_stock_mode" => 'INTEGER(12)',
    "product_cart_mode" => 'INTEGER(12)',
    "product_pricetag_mode" => 'INTEGER(12)',
    "product_price_label" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "product_textlib_price" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "product_textlib_content" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "product_delivery_time" => 'INTEGER(12)',
    "product_currency" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "product_unit" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "product_amount" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "product_related_label" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "product_related" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "product_accessories_label" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "product_accessories" => "VARCHAR(255) NOT NULL DEFAULT ''",
    /* files */
    "file_attachment" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "file_attachment_hits" => 'INTEGER(12)',
    "file_attachment_external" => "VARCHAR(100) NOT NULL DEFAULT ''",
    /* customer can upload */
    "file_attachment_user" => 'INTEGER(12)',
    /* files after sale - only for users who have purchased the item */
    "file_attachment_as" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "file_attachment_as_hits" => 'INTEGER(12)'

);
