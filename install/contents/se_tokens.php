<?php

$database = "user";
$table_name = "se_tokens";

$cols = array(
  "token_id"  => 'INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT',
  "user_id"  => 'INTEGER(12)',
  "identifier"  => "VARCHAR(255) NOT NULL DEFAULT ''",
  "securitytoken" => "VARCHAR(255) NOT NULL DEFAULT ''",
  "time" => "VARCHAR(50) NOT NULL DEFAULT ''"
  );

?>