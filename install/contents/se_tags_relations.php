<?php

$database = "content";
$table_name = "se_tags_relations";

$cols = array(
    "id" => 'INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "tag_id" => 'INTEGER(12) NOT NULL',
    // content_type: 'page' | 'post' | 'product' | 'event'
    "content_type" => "VARCHAR(20) NOT NULL DEFAULT ''",
    "content_id" => 'INTEGER(12) NOT NULL'
);
