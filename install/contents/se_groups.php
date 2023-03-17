<?php

/**
 * group_type -> p = user can decide for himself if he wants to belong to this group
 * group_type -> null or h = hidden group, only administartors can add users to this group
 */

$database = "user";
$table_name = "se_groups";

$cols = array(
    "group_id" => 'INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "group_type" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "group_name" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "group_description" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "group_user" => "VARCHAR(255) NOT NULL DEFAULT ''"
);