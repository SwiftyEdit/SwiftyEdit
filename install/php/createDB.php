<?php

/**
 * install SwiftyEdit
 * create the database
 */

if(!defined('INSTALLER')) {
	header("location:../login.php");
	die("PERMISSION DENIED!");
}

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

$page_lastedit = time();



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
$sql_carts_table = se_generate_sql_query("se_carts.php",$db_type);
$sql_price_groups = se_generate_sql_query("se_price_groups.php",$db_type);
$sql_filter_table = se_generate_sql_query("se_filter.php",$db_type);
$sql_events_table = se_generate_sql_query("se_events.php",$db_type);
$sql_log_table = se_generate_sql_query("se_log.php",$db_type);
$sql_mailbox_table = se_generate_sql_query("se_mailbox.php",$db_type);
$sql_orders_table = se_generate_sql_query("se_orders.php",$db_type);
$sql_delivery_areas_table = se_generate_sql_query("se_delivery_areas.php",$db_type);


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

$portal_content = file_get_contents(__DIR__."/../contents/text_welcome_en.txt");
$email_confirm_content = file_get_contents(__DIR__."/../contents/text_email_confirm_en.txt");
$agreement_content = file_get_contents(__DIR__."/../contents/text_agreement_en.txt");
$account_confirm_content = file_get_contents(__DIR__."/../contents/text_account_confirm_en.txt");
$no_access_content = file_get_contents(__DIR__."/../contents/text_no_access_en.txt");
if($_SESSION['lang'] == 'de') {
    $portal_content = file_get_contents(__DIR__."/../contents/text_welcome_de.txt");
    $email_confirm_content = file_get_contents(__DIR__."/../contents/text_email_confirm_de.txt");
    $agreement_content = file_get_contents(__DIR__."/../contents/text_agreement_de.txt");
    $account_confirm_content = file_get_contents(__DIR__."/../contents/text_account_confirm_de.txt");
    $no_access_content = file_get_contents(__DIR__."/../contents/text_no_access_de.txt");
}

$example_content = file_get_contents(__DIR__."/../contents/text_example.txt");
$footer_content = file_get_contents(__DIR__."/../contents/text_footer.txt");



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
$dbh_content->query($sql_carts_table);
$dbh_content->query($sql_filter_table);
$dbh_content->query($sql_delivery_areas_table);

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
    "page_template_stylesheet" => "../styles/default/css/default.css",
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

// insert preferences

$initSettings = [
    "prefs_pagename" => "SwiftyEdit",
    "prefs_pagetitle" => "SwiftyEdit CMS",
    "prefs_pagesubtitle" => "Content Management System",
    "prefs_template" => "default",
    "prefs_template_layout" => "layout_default.tpl",
    "prefs_template_stylesheet" => "../styles/default/css/default.css",
    "prefs_showloginform" => "yes",
    "prefs_rss_time_offset" => 86400,
    "prefs_cms_domain" => "$prefs_cms_domain",
    "prefs_cms_ssl_domain" => "$prefs_cms_ssl_domain",
    "prefs_cms_base" => "$prefs_cms_base",
    "prefs_default_language" => $_SESSION['lang'],
    "prefs_nbr_page_versions" => 25,
    "prefs_acp_session_lifetime" => 86400,
    "prefs_posts_entries_per_page" => 10,
    "prefs_products_per_page" => 10,
    "prefs_posts_event_time_offset" => 86400,
    "prefs_comments_mode" => 3,
    "prefs_comments_authorization" => 1,
    "prefs_comments_max_entries" => 100,
    "prefs_comments_autoclose" => 604800,
    "prefs_comments_max_level" => 3,
    "prefs_pagesort_minlength" => 3,
    "prefs_maximagewidth" => 1024,
    "prefs_maximageheight" => 1024,
    "prefs_maxtmbwidth" => 350,
    "prefs_maxtmbheight" => 350,
    "prefs_maxfilesize" => 2500,
    "prefs_publisher_mode" => "no",
    "prefs_timezone" => "",
    "prefs_dateformat" => "d.m.Y",
    "prefs_timeformat" => "H:i",
    "prefs_posts_url_pattern" => "by_filename",
    "prefs_posts_default_guestlist" => 1,
    "prefs_posts_default_votings" => 1,
    "prefs_posts_products_default_tax" => 19,
    "prefs_posts_products_tax_alt1" => 7,
    "prefs_posts_products_tax_alt2" => 0,
    "prefs_posts_products_default_currency" => "EUR",
    "prefs_userregistration" => "no",
    "prefs_usertemplate" => "off",
    "prefs_smarty_cache_lifetime" => 0,
    "prefs_smarty_cache" => 0,
    "prefs_smarty_compile_check" => 0,
    "prefs_posts_products_cart" => 1,
    "prefs_posts_order_mode" => 1,
    "prefs_products_cache" => 2,
    "prefs_posts_price_mode" => 1,
    "prefs_posts_price_visibility" => 1,
    "prefs_mailer_type" => "mail",
    "prefs_shipping_costs_mode" => 1
];

$insertData = array_map(function($key, $value) {
    return [
        "option_key" => $key,
        "option_value" => $value,
        "option_module" => "se"
    ];
}, array_keys($initSettings), $initSettings);

$dbh_content->insert("se_options", $insertData);





// insert snippets

$initSnippets = [
    "footer_text" => $footer_content,
    "agreement_text" => $agreement_content,
    "account_confirm" => $account_confirm_content,
    "account_confirm_mail" => $email_confirm_content,
    "no_access" => $no_access_content
];

$time = time();
$language = $_SESSION['lang'];

$insertSnippetData = array_map(function($name, $content) use ($language, $time) {
    return [
        "snippet_name" => $name,
        "snippet_content" => $content,
        "snippet_lang" => $language,
        "snippet_type" => "snippet_core",
        "snippet_lastedit" => $time
    ];
}, array_keys($initSnippets), $initSnippets);

$dbh_content->insert("se_snippets", $insertSnippetData);


/* posts table */

$dbh_posts->query($sql_posts_table);
$dbh_posts->query($sql_products_table);
$dbh_posts->query($sql_price_groups);
$dbh_posts->query($sql_events_table);
$dbh_posts->query($sql_mailbox_table);


echo '<div class="alert alert-success">'.$lang['installed'].' | Admin: '.$username.'</div>';
echo '<hr><a class="btn btn-success" href="/admin/">'.$lang['link_admin'].'</a><hr>';

