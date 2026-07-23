<?php

/**
 * database for wishlists
 *
 * one row per named list, owned by a logged-in user (no guest ownership)
 * slug is a random uuid, generated once, independent of is_public
 */

$database = "content";
$table_name = "se_wishlists";

$cols = array(
    "id" => 'INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "user_id" => 'INTEGER(12) NOT NULL',
    "name" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "description" => "TEXT NOT NULL DEFAULT ''",
    "is_public" => "BOOLEAN NOT NULL DEFAULT 0",
    "slug" => "VARCHAR(36) NOT NULL DEFAULT ''",
    "created_at" => 'INTEGER(12) NOT NULL DEFAULT 0'
);
