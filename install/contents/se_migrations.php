<?php

/**
 * Tracks which /install/migrations/ files have already run, so the runner
 * (se_run_pending_migrations() in install/php/functions.php) never re-applies
 * a migration. Created automatically like any other table by the additive
 * schema-sync (update_database() / install/inc.update.php) - on a fresh
 * install it's simply created empty, since createDB.php builds tables in
 * their current shape directly and never needs a migration to run.
 */

$database = "content";
$table_name = "se_migrations";

$cols = array(
    "id" => 'INTEGER(50) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "migration" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "applied_at" => "VARCHAR(50) NOT NULL DEFAULT ''"
);
