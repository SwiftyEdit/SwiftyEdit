<?php
	
/**
 * database for options
 * these data will replace the se_preferences in the future
 * option_module (str) 'fc' for global preferences or f.e. 'addon.mod'
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
