<?php

/**
 * must be a duplicate of se_pages.php
 * except $table_name and columns 'page_id_original', 'page_cache_type'
 */

$database = "content";
$table_name = "se_pages_cache";

$cols = array(
	"page_id" => 'INTEGER(50) NOT NULL PRIMARY KEY AUTO_INCREMENT',

	"page_id_original" => 'INTEGER(12)',
	"page_cache_type" => "VARCHAR(20) NOT NULL DEFAULT ''",

	"page_parent_id" => 'INTEGER(12)',
    "page_custom_id" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "page_custom_classes" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "page_hits" => 'INTEGER(12)',
	"page_priority" => 'INTEGER(12)',
	"page_language"  => "VARCHAR(20) NOT NULL DEFAULT ''",
	"page_linkname"  => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_permalink" => "VARCHAR(100) NOT NULL DEFAULT ''",
	"page_permalink_short" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_permalink_short_cnt" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "page_canonical_url" => "VARCHAR(500) NOT NULL DEFAULT ''",
    "page_translation_urls" => "LONGTEXT NOT NULL DEFAULT ''",
	"page_target" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_type_of_use" => "VARCHAR(255) NOT NULL DEFAULT ''",
	"page_redirect" => "VARCHAR(100) NOT NULL DEFAULT ''",
	"page_redirect_code" => "VARCHAR(20) NOT NULL DEFAULT ''",
	"page_funnel_uri" => "VARCHAR(500) NOT NULL DEFAULT ''",
	"page_classes" => "VARCHAR(100) NOT NULL DEFAULT ''",
	"page_hash" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_psw" => "VARCHAR(255) NOT NULL DEFAULT ''",
	"page_title" => "VARCHAR(255) NOT NULL DEFAULT ''",
	"page_status" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_usergroup" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_content" => "LONGTEXT NOT NULL DEFAULT ''",
	"page_sort" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_lastedit" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_lastedit_from" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_meta_author" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_meta_date" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_meta_keywords" => "VARCHAR(255) NOT NULL DEFAULT ''",
	"page_meta_description" => "VARCHAR(500) NOT NULL DEFAULT ''",
	"page_meta_robots" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_thumbnail" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_favicon" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_template" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_template_layout" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_template_stylesheet" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_template_values" => "LONGTEXT NOT NULL DEFAULT ''",
	"page_modul" => "VARCHAR(50) NOT NULL DEFAULT ''",
	"page_modul_query" => "VARCHAR(255) NOT NULL DEFAULT ''",
	"page_addon_string" => "VARCHAR(500) NOT NULL DEFAULT ''",
	"page_posts_categories" => "VARCHAR(255) NOT NULL DEFAULT ''",
	"page_posts_types" => "VARCHAR(255) NOT NULL DEFAULT ''",
	"page_authorized_users" => "VARCHAR(255) NOT NULL DEFAULT ''",
	"page_version" => 'INTEGER(50)',
	"page_version_date" => "VARCHAR(50) NOT NULL DEFAULT ''",
  "page_labels" => "VARCHAR(100) NOT NULL DEFAULT ''",
  "page_categories" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "page_categories_mode" => 'INTEGER(12)',
  "page_comments" => 'INTEGER(12)'
	
  );