<?php

$database = "content";
$table_name = "se_logs";

$cols = array(
    "id" => "INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT",
    "time" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "source" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "entry" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "priority" => "INTEGER(12)",
    // filled by se_log_event() / se_log_content(), NULL for plain record_log()
    // entries. Without NOT NULL on purpose: a fresh SQLite install ignores
    // DEFAULT, an update via ALTER TABLE doesn't - this way it is NULL everywhere
    "action" => "VARCHAR(20)",
    "object_type" => "VARCHAR(20)",
    "object_id" => "INTEGER(12)",
    "object_key" => "VARCHAR(255)",
    "object_label" => "VARCHAR(255)"
);