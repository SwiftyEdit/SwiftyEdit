<?php

/**
 * install SwiftyEdit
 * create the database
 */

if(!defined('INSTALLER')) {
	header("location:../login.php");
	die("PERMISSION DENIED!");
}

require '../lib/Medoo.php';
use Medoo\Medoo;

$username = $_SESSION['temp_username'];
$mail = $_SESSION['temp_usermail'];
$psw = $_SESSION['temp_userpsw'];

$user_psw_hash = password_hash($psw, PASSWORD_DEFAULT);
$drm_string = "drm_acp_pages|drm_acp_files|drm_acp_user|drm_acp_system|drm_acp_editpages|drm_acp_editownpages|drm_moderator|drm_can_publish|drm_acp_sensitive_files";
$user_verified = "verified";
$user_registerdate = time();

$prefs_cms_domain = $_SESSION['temp_prefs_cms_domain'];
$prefs_cms_ssl_domain = $_SESSION['temp_prefs_cms_ssl_domain'];
$prefs_cms_base = $_SESSION['temp_prefs_cms_base'];



if(isset($_POST['install_mysql'])) {
	/* we use MySQL */
	$db_type = 'mysql';
	
	if ($prefs_database_name == '' || $prefs_database_username == '' || $prefs_database_psw == '') {
		echo '<p><a href="javascript:history.back()" class="btn btn-default">'.$lang['pagination_backward'].'</a></p>';
		die('MISSING MYSQL INFORMATION');		
	}
	
	
	
	try {
		$database = new Medoo([
	
			'type' => 'mysql',
			'database' => "$prefs_database_name",
			'host' => "$prefs_database_host",
			'username' => "$prefs_database_username",
			'password' => "$prefs_database_psw",
		 
			'charset' => 'utf8',
			'port' => $prefs_database_port,
		 
			'prefix' => "$prefs_database_prefix"
		]);
	
	} catch (Exception $e) {
		echo '<p><a href="javascript:history.back()" class="btn btn-default">'.$lang['pagination_backward'].'</a></p>';
		die('CONNECTION ERROR');
	}  

	
  $config_db_content = "<?php\n";
  $config_db_content .= "$"."database_host = "."\"".$prefs_database_host."\";\n";
  $config_db_content .= "$"."database_user = "."\"".$prefs_database_username."\";\n";
  $config_db_content .= "$"."database_psw = "."\"".$prefs_database_psw."\";\n";
  $config_db_content .= "$"."database_name = "."\"".$prefs_database_name."\";\n";
  $config_db_content .= "$"."database_port = "."\"".$prefs_database_port."\";\n";
  $config_db_content .= "define("."\""."DB_PREFIX"."\"".", "."\"".$prefs_database_prefix."\");\n";
  $config_db_content .= "?>";
	
	$config_db_file = "../config_database.php";
	file_put_contents($config_db_file, $config_db_content);
	
	define("se_PREFIX","$prefs_database_prefix");

	
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

}

define("INDEX_DB", "$se_db_index");
$db_index = new Medoo([
	'type' => 'sqlite',
	'database' => INDEX_DB
]);


echo $db_type. ' Database<hr>';


/* Queries for new tables */

$sql_user_table = se_generate_sql_query("se_user.php",$db_type);
$sql_groups_table = se_generate_sql_query("se_groups.php",$db_type);
$sql_tokens_table = se_generate_sql_query("se_tokens.php",$db_type);

$sql_feeds_table = se_generate_sql_query("se_feeds.php",$db_type);
$sql_pages_table = se_generate_sql_query("se_pages.php",$db_type);
$sql_pages_cache_table = se_generate_sql_query("se_pages_cache.php",$db_type);
$sql_options_table = se_generate_sql_query("se_options.php",$db_type);
$sql_themes_table = se_generate_sql_query("se_themes.php",$db_type);
$sql_snippets_table = se_generate_sql_query("se_snippets.php",$db_type);
$sql_comments_table = se_generate_sql_query("se_comments.php",$db_type);
$sql_media_table = se_generate_sql_query("se_media.php",$db_type);
$sql_labels_table = se_generate_sql_query("se_labels.php",$db_type);
$sql_categories_table = se_generate_sql_query("se_categories.php",$db_type);
$sql_addons_table = se_generate_sql_query("se_addons.php",$db_type);
$sql_posts_table = se_generate_sql_query("se_posts.php",$db_type);
$sql_products_table = se_generate_sql_query("se_products.php",$db_type);
$sql_events_table = se_generate_sql_query("se_events.php",$db_type);
$sql_log_table = se_generate_sql_query("se_log.php",$db_type);
$sql_mailbox_table = se_generate_sql_query("se_mailbox.php",$db_type);
$sql_orders_table = se_generate_sql_query("se_orders.php",$db_type);

$sql_index_excludes_table = se_generate_sql_query("se_index_excludes.php",'sqlite');
$sql_index_items_table = se_generate_sql_query("se_index_items.php",'sqlite');


if($db_type == 'mysql') {
	
	$dbh_user = $database;
	$dbh_content = $database;
	$dbh_posts = $database;
	
} else {
	
	$dbh_user = $db_user;
	$dbh_content = $db_content;
	$dbh_posts = $db_posts;
	
}

$dbh_user->query($sql_user_table);
$dbh_user->query($sql_tokens_table);
$dbh_user->query($sql_groups_table);

$dbh_user->insert("se_user", [
	"user_class" => "administrator",
	"user_nick" => "$username",
	"user_verified" => "verified",
	"user_registerdate" => "$user_registerdate",
	"user_drm" => "$drm_string",
	"user_mail" => "$mail",
	"user_psw_hash" => "$user_psw_hash"
]);



/**
 * get basic contents
 */


$portal_content = file_get_contents("contents/text_welcome_en.txt");
if($_SESSION['lang'] == 'de') {
    $portal_content = file_get_contents("contents/text_welcome_de.txt");
}

$example_content = file_get_contents("contents/text_example.txt");
$footer_content = file_get_contents("contents/text_footer.txt");
$agreement_content = file_get_contents("contents/text_agreement.txt");
$email_confirm_content = file_get_contents("contents/text_email_confirm.txt");
$time = time();


$dbh_content->query($sql_pages_table);
$dbh_content->query($sql_pages_cache_table);
$dbh_content->query($sql_options_table);
$dbh_content->query($sql_themes_table);
$dbh_content->query($sql_snippets_table);
$dbh_content->query($sql_comments_table);
$dbh_content->query($sql_media_table);
$dbh_content->query($sql_feeds_table);
$dbh_content->query($sql_labels_table);
$dbh_content->query($sql_categories_table);
$dbh_content->query($sql_addons_table);
$dbh_content->query($sql_log_table);
$dbh_content->query($sql_orders_table);

/* insert two example pages */

$dbh_content->insert("se_pages", [
	"page_language" => $_SESSION['lang'],
	"page_linkname" => "Home",
    "page_type_of_use" => "normal",
	"page_title" => "Homepage",
	"page_status" => "public",
	"page_content" => "$portal_content",
	"page_lastedit" => "$time",
	"page_lastedit_from" => "$username",
	"page_template" => "default",
	"page_template_layout" => "layout_default.tpl",
    "page_template_stylesheet" => "../styles/default/css/theme_light.css",
	"page_sort" => "portal",
	"page_meta_author" => "$username",
	"page_meta_date" => "$page_lastedit",
	"page_meta_keywords" => "example,test,portal",
	"page_meta_description" => "Example Meta Description for the portal page",
	"page_meta_robots" => "all"
]);

$dbh_content->insert("se_pages", [
	"page_language" => $_SESSION['lang'],
	"page_linkname" => "Testseite",
    "page_type_of_use" => "normal",
	"page_permalink" => "test/",
	"page_title" => "Testseite",
	"page_status" => "public",
	"page_content" => "$example_content",
	"page_lastedit" => "$time",
	"page_lastedit_from" => "$username",
	"page_template" => "use_standard",
	"page_template_layout" => "use_standard",
	"page_sort" => "100",
	"page_meta_author" => "$username",
	"page_meta_date" => "$page_lastedit",
	"page_meta_keywords" => "example,test",
	"page_meta_description" => "Example Meta Description for a example page",
	"page_meta_robots" => "all"
]);

/* insert preferences */

$dbh_content->insert("se_options", [
	[
		"option_key" => "prefs_pagename",
		"option_value" => "SwiftyEdit",
		"option_module" => "se"
	], [
		"option_key" => "prefs_pagetitle",
		"option_value" => "SwiftyEdit CMS",
		"option_module" => "se"
	], [
		"option_key" => "prefs_pagesubtitle",
		"option_value" => "Content Management System",
		"option_module" => "se"
	], [
		"option_key" => "prefs_template",
		"option_value" => "default",
		"option_module" => "se"
	], [
		"option_key" => "prefs_template_layout",
		"option_value" => "layout_default.tpl",
		"option_module" => "se"
	], [
		"option_key" => "prefs_showloginform",
		"option_value" => "yes",
		"option_module" => "se"
	], [
		"option_key" => "prefs_template_stylesheet",
		"option_value" => "../styles/default/css/theme_light.css",
		"option_module" => "se"
	], [
		"option_key" => "prefs_rss_time_offset",
		"option_value" => 86400,
		"option_module" => "se"
	], [
		"option_key" => "prefs_cms_domain",
		"option_value" => "$prefs_cms_domain",
		"option_module" => "se"
	], [
		"option_key" => "prefs_cms_ssl_domain",
		"option_value" => "$prefs_cms_ssl_domain",
		"option_module" => "se"
	], [
		"option_key" => "prefs_cms_base",
		"option_value" => "$prefs_cms_base",
		"option_module" => "se"
	], [
		"option_key" => "prefs_default_language",
		"option_value" => $_SESSION['lang'],
		"option_module" => "se"
	], [
		"option_key" => "prefs_nbr_page_versions",
		"option_value" => 25,
		"option_module" => "se"
	], [
		"option_key" => "prefs_acp_session_lifetime",
		"option_value" => 86400,
		"option_module" => "se"
	], [
		"option_key" => "prefs_posts_entries_per_page",
		"option_value" => 10,
		"option_module" => "se"
	], [
		"option_key" => "prefs_posts_event_time_offset",
		"option_value" => 86400,
		"option_module" => "se"
	], [
		"option_key" => "prefs_comments_mode",
		"option_value" => 3,
		"option_module" => "se"
	], [
		"option_key" => "prefs_comments_authorization",
		"option_value" => 1,
		"option_module" => "se"
	], [
		"option_key" => "prefs_comments_max_entries",
		"option_value" => 100,
		"option_module" => "se"
	], [
		"option_key" => "prefs_comments_max_level",
		"option_value" => 3,
		"option_module" => "se"
	], [
		"option_key" => "prefs_pagesort_minlength",
		"option_value" => 3,
		"option_module" => "se"
	], [
		"option_key" => "prefs_maximagewidth",
		"option_value" => 1024,
		"option_module" => "se"
	], [
		"option_key" => "prefs_maximageheight",
		"option_value" => 1024,
		"option_module" => "se"
	], [
		"option_key" => "prefs_maxtmbwidth",
		"option_value" => 350,
		"option_module" => "se"
	], [
		"option_key" => "prefs_maxtmbheight",
		"option_value" => 350,
		"option_module" => "se"
	], [
		"option_key" => "prefs_maxfilesize",
		"option_value" => 2500,
		"option_module" => "se"
	], [
        "option_key" => "prefs_publisher_mode",
        "option_value" => "no",
        "option_module" => "se"
    ], [
        "option_key" => "prefs_timezone",
        "option_value" => "",
        "option_module" => "se"
    ], [
        "option_key" => "prefs_dateformat",
        "option_value" => "d.m.Y",
        "option_module" => "se"
    ], [
        "option_key" => "prefs_timeformat",
        "option_value" => "H:i",
        "option_module" => "se"
    ], [
        "option_key" => "prefs_posts_url_pattern",
        "option_value" => "by_filename",
        "option_module" => "se"
    ], [
        "option_key" => "prefs_posts_default_guestlist",
        "option_value" => 1,
        "option_module" => "se"
    ],[
        "option_key" => "prefs_posts_default_votings",
        "option_value" => 1,
        "option_module" => "se"
    ],[
        "option_key" => "prefs_posts_products_default_tax",
        "option_value" => 19,
        "option_module" => "se"
    ],[
        "option_key" => "prefs_posts_products_tax_alt1",
        "option_value" => 7,
        "option_module" => "se"
    ],[
        "option_key" => "prefs_posts_products_tax_alt2",
        "option_value" => 0,
        "option_module" => "se"
    ],[
        "option_key" => "prefs_posts_products_default_currency",
        "option_value" => "EUR",
        "option_module" => "se"
    ],[
        "option_key" => "prefs_userregistration",
        "option_value" => "no",
        "option_module" => "se"
    ],[
        "option_key" => "prefs_usertemplate",
        "option_value" => "off",
        "option_module" => "se"
    ],[
        "option_key" => "prefs_smarty_cache_lifetime",
        "option_value" => 0,
        "option_module" => "se"
    ],[
        "option_key" => "prefs_smarty_cache",
        "option_value" => 0,
        "option_module" => "se"
    ],[
        "option_key" => "prefs_smarty_compile_check",
        "option_value" => 0,
        "option_module" => "se"
    ]
]);





/* insert snippets */

$dbh_content->insert("se_snippets", [
	[
		"snippet_name" => "footer_text",
		"snippet_content" => "$footer_content",
		"snippet_lang" => $_SESSION['lang'],
		"snippet_type" => "snippet_core",
        "snippet_lastedit" => $time
	],[
		"snippet_name" => "agreement_text",
		"snippet_content" => "$agreement_content",
		"snippet_lang" => $_SESSION['lang'],
		"snippet_type" => "snippet_core",
        "snippet_lastedit" => $time
	],[
		"snippet_name" => "account_confirm",
		"snippet_content" => "<p>Dein Account wurde erfolgreich freigeschaltet.</p>",
		"snippet_lang" => $_SESSION['lang'],
		"snippet_type" => "snippet_core",
        "snippet_lastedit" => $time
	],[
		"snippet_name" => "account_confirm_mail",
		"snippet_content" => "$email_confirm_content",
		"snippet_lang" => $_SESSION['lang'],
		"snippet_type" => "snippet_core",
        "snippet_lastedit" => $time
	],[
		"snippet_name" => "no_access",
		"snippet_content" => "Zugriff verweigert...",
		"snippet_lang" => $_SESSION['lang'],
		"snippet_type" => "snippet_core",
        "snippet_lastedit" => $time
	]
]);



/* posts table */

$dbh_posts->query($sql_posts_table);
$dbh_posts->query($sql_products_table);
$dbh_posts->query($sql_events_table);
$dbh_posts->query($sql_mailbox_table);

/**
 * DATABASE INDEX
 */

$db_index->query($sql_index_excludes_table);
$db_index->query($sql_index_items_table);


echo '<div class="alert alert-success">'.$lang['installed'].' | Admin: '.$username.'</div>';
echo '<hr><a class="btn btn-success" href="../acp/index.php">'.$lang['link_admin'].'</a><hr>';

