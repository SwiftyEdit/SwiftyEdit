<?php

$database = "content";
$table_name = "se_logs";

$cols = array(
    "id" => "INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT",
    "time" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "source" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "entry" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "priority" => "INTEGER(12)"
);