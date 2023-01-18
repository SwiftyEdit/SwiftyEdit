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
	define("INDEX_DB", "$se_db_index");
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
	
}

require_once SE_ROOT . '/core/functions.php';
require_once SE_ROOT . '/global/functions.php';