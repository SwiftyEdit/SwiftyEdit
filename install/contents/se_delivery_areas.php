<?php

/**
 * database used for delivery areas
 * status - 1 = active 2 = inactive
 * tax - 1 = active 2 = inactive
 */

$database = "content";
$table_name = "se_delivery_areas";

$cols = [
    "id" => 'INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "status"  => "BOOLEAN NOT NULL DEFAULT 1",
    "name" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "tax"  => "BOOLEAN NOT NULL DEFAULT 1"
];