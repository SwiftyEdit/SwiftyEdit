<?php

if(!defined('INSTALLER')) {
	header("location:../login.php");
	die("PERMISSION DENIED!");
}

include_once SE_ROOT.'/acp/core/functions.php';

// se_get_country_migration_map() / se_generate_uuid() - needed by migrations
// in install/migrations/. This file is pure function definitions (no
// top-level side effects), safe to load here even though most of it is
// normally reached only through the frontend app/ layer.
require_once SE_ROOT.'app/functions/functions.helpers.php';

/* returns all cols of a existung database/table */
function get_columns($database, $table_name) {
	
	global $db_content;
	global $db_user;
	global $db_statistics;
	global $db_posts;
	global $db_type;
	global $database_name;

	if($db_type == "mysql") {
		$query = "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$database_name' AND TABLE_NAME = '$table_name'";
	} else {
		$query = "PRAGMA table_info(" . $table_name . ")";
	}	
	
	if($database == "content") {
		$data = $db_content->query($query)->fetchAll(PDO::FETCH_ASSOC);
	} else if($database == "user") {
		$data = $db_user->query($query)->fetchAll(PDO::FETCH_ASSOC);
	} else if($database == "tracker") {
		$data = $db_statistics->query($query)->fetchAll(PDO::FETCH_ASSOC);
	} else if($database == "posts") {
		$data = $db_posts->query($query)->fetchAll(PDO::FETCH_ASSOC);
	}

	
	$meta = array();
	foreach ($data as $row) {
		$meta[$row['COLUMN_NAME']] = $row['DATA_TYPE']; /* mysql schema */
		$meta[$row['name']] = $row['type']; /* sqlite schema */
	}


	return $meta;
}



/*  check if table exists - returns the number of existing tables */
function table_exists($database,$table_name) {
	
	global $db_content;
	global $db_user;
	global $db_statistics;
	global $db_posts;
	global $db_type;
	global $database_name;
	global $db_type;
	
	if($db_type == "mysql") {
		$query = "SELECT count(*) FROM information_schema.TABLES WHERE (TABLE_SCHEMA = '$database_name') AND (TABLE_NAME = '$table_name')";
	} else {
		$query = "SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='$table_name'";
	}
	
	if($database == "content") {
		$cnt_tables = $db_content->query($query)->fetch();
	} else if($database == "user") {
		$cnt_tables = $db_user->query($query)->fetch();
	} else if($database == "tracker") {
		$cnt_tables = $db_statistics->query($query)->fetch();
	} else if($database == "posts") {
		$cnt_tables = $db_posts->query($query)->fetch();
	}
	
	$cnt_tables = $cnt_tables[0];
  return $cnt_tables;
}






/**
 * generate an sql query from templates (php files)
 * distinction is made between SQLite and MySQL
 * $db_type = 'sqlite' or 'mysql'
 * Note:	in  SQLite we have only NULL, INTEGER, REAL, TEXT and BLOB
 * 				we only need INTEGER and TEXT
 */

function se_generate_sql_query($file,$db_type='sqlite') {
	
	include __DIR__."/../contents/$file";
	$string = '';
	
	if($db_type == 'sqlite') {
		/* generate sqlite query */

		foreach ($cols as $k => $v) {
			
			if(strpos($v,'INTEGER') !== false) {
				$str = 'INTEGER';
			} else if(strpos($v,'VARCHAR') !== false) {
				$str = 'VARCHAR';
			} else {
				$str = 'TEXT';
			}
			
			if(strpos($v,'PRIMARY') !== false) {
				$str .= ' NOT NULL PRIMARY KEY';
			}
			
    	$string .= "$k $str,\r";
		}
		
		$string = substr(trim("$string"), 0,-1); // cut last commata and returns
		
		if($table_type == 'virtual') {
			
			$sql_string = "CREATE VIRTUAL TABLE $table_name USING fts3($string,tokenize=porter)";
			
		} else {
			$sql_string = "
				CREATE TABLE $table_name (
				$string
				)
			";		
		}
		
	} else {
		/* generate mysql query */

		foreach ($cols as $k => $v) {
    	$string .= "$k $v,\r"; 
		}
		
		$string = substr(trim("$string"), 0,-1); // cut last commata and returns
		$table = se_PREFIX.$table_name;
		$sql_string = "
		    CREATE TABLE $table (
		    $string
	        ) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_unicode_ci;
	    ";
		
	}

  return $sql_string;
}





function update_table($col_name,$type,$table_name,$database) {
	
	
	global $db_content;
	global $db_user;
	global $db_statistics;
	global $db_posts;
	global $db_type;
	global $database_name;
	
		
	$sql = "ALTER TABLE $table_name ADD $col_name $type";
	
	if($database == "content") {
		$db_content->query($sql);
	} else if($database == "user") {
		$db_user->query($sql);
	} else if($database == "tracker") {
		$db_statistics->query($sql);
	} else if($database == "posts") {
		$db_posts->query($sql);
	}
}

/*

CREATE TABLE table_name
(
column_name1 data_type,
column_name2 data_type,
column_name3 data_type,
....
)

*/

function add_table($database,$table_name,$cols) {

	global $db_content;
	global $db_user;
	global $db_statistics;
	global $db_posts;
	global $db_type;
	global $database_name;


	if($db_type == 'sqlite') {

		foreach ($cols as $k => $v) {
			if(strpos($v,'INTEGER') !== false) {
				$str = 'INTEGER';
			} else if(strpos($v,'VARCHAR') !== false) {
				$str = 'VARCHAR';
			} else {
				$str = 'TEXT';
			}
			
			if(strpos($v,'PRIMARY') !== false) {
				$str .= ' NOT NULL PRIMARY KEY';
			}
			
    	$string .= "$k $str,\r";			
		}
		
		$cols_string = substr(trim("$string"), 0,-1); // cut last commata and returns
	
	} else {

		foreach ($cols as $k => $v) {
			$cols_string .= "$k $cols[$k],\r";
		}
		$cols_string = substr(trim("$cols_string"), 0,-1);
	}
		
	$sql = "CREATE TABLE $table_name ( $cols_string )";

	if($database == "content") {
		$db_content->query($sql);
	} else if($database == "user") {
		$db_user->query($sql);
	} else if($database == "tracker") {
		$db_statistics->query($sql);
	} else if($database == "posts") {
		$db_posts->query($sql);
	}

}

/**
 * Set a UUID on every row of $table that doesn't have one yet. Used by
 * migrations that backfill UUID columns added after rows already existed.
 * Already-filled rows are left untouched, so this is safe to call again.
 *
 * @return int number of rows updated
 */
function se_helper_fill_uuids($db, string $table, string $id_col, string $uuid_col): int {
    $rows = $db->select($table, [$id_col, $uuid_col]);
    $updated = 0;

    foreach ($rows as $row) {
        if ($row[$uuid_col] == '') {
            $db->update($table, [
                $uuid_col => se_generate_uuid()
            ], [
                $id_col => $row[$id_col]
            ]);
            $updated++;
        }
    }

    return $updated;
}

/**
 * Run any migrations from /install/migrations/ that haven't been applied yet.
 *
 * Called right after the additive schema-sync (update_database() /
 * install/inc.update.php's inline equivalent), on both update paths - never
 * on a fresh install, since createDB.php builds tables in their current
 * shape directly. Applied migrations are tracked in se_migrations so each
 * one runs at most once, ever.
 *
 * Every migration file returns a closure `function($db_content, $db_user,
 * $db_posts) { ... }`. Its writes and the se_migrations tracking row are
 * committed together in a transaction per involved database connection
 * (deduplicated - on MySQL $db_content/$db_user/$db_posts are the very same
 * connection, on SQLite they're three separate files/connections and can
 * only be committed independently). If a migration throws, everything it did
 * is rolled back, the run stops there, and later migrations are left for the
 * next attempt - migrations 1..n-1 already committed stay applied.
 *
 * @return array{applied: string[], error: string|null}
 */
function se_run_pending_migrations(): array {

    global $db_content, $db_user, $db_posts;

    $result = ['applied' => [], 'error' => null];

    $files = glob(SE_ROOT.'install/migrations/*.php');
    if (!$files) {
        return $result;
    }
    sort($files); // filenames start with a sortable timestamp

    $already_applied = array_column($db_content->select('se_migrations', ['migration']), 'migration');

    // dedupe connections: MySQL uses one shared Medoo instance for all three,
    // SQLite has three independent ones - either way each unique PDO
    // connection may only have beginTransaction() called on it once.
    $connections = [];
    foreach ([$db_content, $db_user, $db_posts] as $conn) {
        $connections[spl_object_id($conn->pdo)] = $conn;
    }

    foreach ($files as $file) {
        $name = basename($file, '.php');

        if (in_array($name, $already_applied, true)) {
            continue;
        }

        $migration = include $file;

        if (!is_callable($migration)) {
            $result['error'] = "Migration $name does not return a callable.";
            break;
        }

        foreach ($connections as $conn) {
            $conn->pdo->beginTransaction();
        }

        try {
            $migration($db_content, $db_user, $db_posts);

            $db_content->insert('se_migrations', [
                'migration' => $name,
                'applied_at' => time(),
            ]);

            foreach ($connections as $conn) {
                $conn->pdo->commit();
            }

            $result['applied'][] = $name;
        } catch (\Throwable $e) {
            foreach ($connections as $conn) {
                if ($conn->pdo->inTransaction()) {
                    $conn->pdo->rollBack();
                }
            }

            $result['error'] = "Migration $name failed: " . $e->getMessage();
            break;
        }
    }

    return $result;
}