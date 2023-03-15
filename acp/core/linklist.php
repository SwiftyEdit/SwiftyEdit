<?php

/**
 * list all pages
 * used in tinyMCE's links popup
 *
 *	{title: 'My page', value: '/my_page/'}, ...
 */
error_reporting(0);
session_start();

require '../../core/vendor/autoload.php';
use Medoo\Medoo;

include '../../config.php';
include 'functions.php';

$counter = 0;

if(is_file('../../config_database.php')) {
	include '../../config_database.php';
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
	
} else {
	$db_type = 'sqlite';
	$db_content = new Medoo([
		'type' => 'sqlite',
		'database' => $se_db_content
	]);	
}

$page_data = $db_content->select("se_pages", [
	"page_permalink",
	"page_title"
]);

$pages = array();

foreach($page_data as $page) {
	$pages[$counter]['title'] = html_entity_decode($page['page_title']). '-> /'.$page['page_permalink'];
	$pages[$counter]['value'] = '/'.$page['page_permalink'];
	$counter++;
}

header('Content-type: text/javascript');
header('pragma: no-cache');
header('expires: 0');
echo json_encode($pages);