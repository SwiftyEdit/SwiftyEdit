<?php

/**
 * list all pages
 * used in tinyMCE's links popup
 *
 *	{title: 'My page', value: '/my_page/'}, ...
 *
 * @var object $db_content from app/database.php
 */
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');
require_once __DIR__.'/../../../../../app/session.php';
se_session_start();

if(($_SESSION['user_class'] ?? '') !== 'administrator'){
    die();
}

require __DIR__.'/../../../../../vendor/autoload.php';
require __DIR__.'/../../../../../config.php';

// same database setup as acp/header.php - the session checks below need $db_user
const SE_SECTION = "backend";
require SE_ROOT.'/app/database.php';

// absolute lifetime, account fingerprint and ACP idle timeout - this file
// is reached directly, without acp/header.php
se_acp_guard();

$counter = 0;

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
