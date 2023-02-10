<?php

/**
 * snippet_type    -> snippet_core, snippet or shortcode
 *                    -> product_feature
 */

$database = "content";
$table_name = "se_snippets";

$cols = array(
    "snippet_id" => 'INTEGER(12) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "snippet_type" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "snippet_shortcode" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "snippet_name" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "snippet_title" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_content" => "LONGTEXT NOT NULL DEFAULT ''",
    "snippet_teaser" => "LONGTEXT NOT NULL DEFAULT ''",
    "snippet_keywords" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_classes" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_permalink" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_permalink_name" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_permalink_title" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_permalink_classes" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_images" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_groups" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_label" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_labels" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_template" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_theme" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_notes" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_lastedit" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_lastedit_from" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_lang" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_status" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "snippet_priority" => 'INTEGER(12)'
);