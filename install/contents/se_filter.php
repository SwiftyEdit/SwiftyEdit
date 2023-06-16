<?php

/**
 * filter for products
 * for example COLOR -> RED, GREEN, BLUE
 * filter_type - 1 -> group 2 -> value
 * filter_parent_id int if it's a value of a group
 * filter_input_type - 1 -> radio 2 -> checkbox
 * filter_categories -> csv string of categories f.e. 1,4,8,19 ...
 */

$database = "content";
$table_name = "se_filter";

$cols = array(
    "filter_id" => 'INTEGER(50) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "filter_parent_id" => "INTEGER(12)",
    "filter_lang"  => "VARCHAR(255) NOT NULL DEFAULT ''",
    "filter_title" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "filter_description" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "filter_type" => "INTEGER(12)",
    "filter_input_type" => "INTEGER(12)",
    "filter_input" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "filter_priority" => "INTEGER(12)",
    "filter_categories" => "VARCHAR(255) NOT NULL DEFAULT ''"
);