<?php

$database = "posts";
$table_name = "se_mailbox";

$cols = array(
    "id" => 'INTEGER(50) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "time_created"  => 'INTEGER(12)',
    "time_lastedit"  => 'INTEGER(12)',
    "time_send"  => 'INTEGER(12)',
    "autor" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "subject" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "content" => "LONGTEXT NOT NULL DEFAULT ''",
    "recipients" => "LONGTEXT NOT NULL DEFAULT ''",
    "log" => "LONGTEXT NOT NULL DEFAULT ''"
);