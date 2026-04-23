<?php
	
/**
 * database for options / settings
 * option_module (str) 'se' for global preferences or f.e. 'addon'
 * option_key (str)
 */
 
$database = 'content';
$table_name = 'se_options';

$cols = array(
  "option_id"  => 'INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT',
  "option_module"  => "VARCHAR(255) NOT NULL DEFAULT ''",
  "option_key"  => "VARCHAR(255) NOT NULL DEFAULT ''",
  "option_value" => "LONGTEXT NOT NULL DEFAULT ''"
  );

?>
