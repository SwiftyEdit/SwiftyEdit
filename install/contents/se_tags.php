<?php

$database = "content";
$table_name = "se_tags";

$cols = array(
    "tag_id" => 'INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    // tag_name: display value, exactly as typed by the editor.
    "tag_name" => "VARCHAR(100) NOT NULL DEFAULT ''",
    // tag_name_clean: lowercased/trimmed form, used for case-insensitive
    // dedup on save and as the filter value in tag links/URLs.
    "tag_name_clean" => "VARCHAR(100) NOT NULL DEFAULT ''"
);
