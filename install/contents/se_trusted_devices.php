<?php

/**
 * Devices on which a backend user chose "trust this device" after the
 * second factor (see app/functions/functions.twofactor.php). Only a hash of
 * the cookie token is stored.
 */

$database = "user";
$table_name = "se_trusted_devices";

$cols = array(
  "device_id"  => 'INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT',
  "user_id"  => 'INTEGER(12) NOT NULL DEFAULT 0',
  "token_hash"  => "VARCHAR(64) NOT NULL DEFAULT ''",
  "description" => "VARCHAR(100) NOT NULL DEFAULT ''",
  "created" => 'INTEGER NOT NULL DEFAULT 0',
  "expires" => 'INTEGER NOT NULL DEFAULT 0',
  "last_used" => 'INTEGER NOT NULL DEFAULT 0'
  );
