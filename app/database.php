<?php

if(!defined('SE_SECTION')) {
	die("PERMISSION DENIED!");
}

use Medoo\Medoo;

if(is_file(SE_ROOT.'/config_database.php')) {
	include SE_ROOT.'/config_database.php';
	
	$db_type = 'mysql';
	 
	$database = new Medoo([
		'type' => 'mysql',
		'database' => "$database_name",
		'host' => "$database_host",
		'username' => "$database_user",
		'password' => "$database_psw",
		'charset' => 'utf8',
		'port' => $database_port,
		'prefix' => DB_PREFIX
	]);
	
	$db_content = $database;
	$db_user = $database;
	$db_posts = $database;
	
} else {
	
	$db_type = 'sqlite';
	
	define("CONTENT_DB", "$se_db_content");
	define("USER_DB", "$se_db_user");
	define("POSTS_DB", "$se_db_posts");
	
	$db_content = new Medoo([
		'type' => 'sqlite',
		'database' => CONTENT_DB
	]);
	
	$db_user = new Medoo([
		'type' => 'sqlite',
		'database' => USER_DB
	]);

	$db_posts = new Medoo([
		'type' => 'sqlite',
		'database' => POSTS_DB
	]);

	// Per-connection tuning. journal_mode itself is persisted in the database
	// file (toggled from ACP settings > Database) and doesn't need to be set
	// here - but synchronous/busy_timeout reset with every new connection, so
	// they need to be applied on every request.
	foreach ([$db_content, $db_user, $db_posts] as $se_db_conn) {
		// avoid "database is locked" errors under concurrent access instead
		// of failing immediately
		$se_db_conn->pdo->exec('PRAGMA busy_timeout = 5000');

		// NORMAL is the recommended, safe synchronous level when journal_mode
		// is WAL (unlike with the default DELETE/rollback journal, where it
		// would be a durability trade-off) - only apply it when WAL is
		// actually active
		if (strtolower((string) $se_db_conn->pdo->query('PRAGMA journal_mode')->fetchColumn()) === 'wal') {
			$se_db_conn->pdo->exec('PRAGMA synchronous = NORMAL');
		}
	}
	unset($se_db_conn);

}

require_once SE_ROOT . '/app/functions.php';
require_once SE_ROOT . '/app/functions/functions.php';