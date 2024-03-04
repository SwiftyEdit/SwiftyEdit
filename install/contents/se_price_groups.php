<?php

/**
 * database for price groups
 */

$database = "posts";
$table_name = "se_prices";

$cols = array(
    "id" => 'INTEGER(50) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "hash" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "title" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "unit" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "amount" => 'INTEGER(100)',
    "tax" => 'INTEGER(12)',
    "price_net" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "price_volume_discount" => "LONGTEXT NOT NULL DEFAULT ''"
);