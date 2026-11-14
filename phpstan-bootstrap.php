<?php

/**
 * Constants for PHPStan that only exist at runtime.
 *
 * SE_INCLUDE_PATH is defined in config.php from $_SERVER['SCRIPT_NAME'],
 * DB_PREFIX in the generated config_database.php (MySQL installs).
 * The values are placeholders - phpstan.neon lists both names under
 * dynamicConstantNames, so only their type (string) is used.
 */

define('SE_INCLUDE_PATH', '');
define('DB_PREFIX', '');
