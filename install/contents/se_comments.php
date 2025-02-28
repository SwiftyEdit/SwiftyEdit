<?php

/**
 * database used for
 *
 * comment_type - p -> comments on pages
 *              - b -> comments on blog posts
 *				- upv -> upvote on blog posts
 *				- dnv -> downvote on blog posts
 *				- evc -> Event confirmation
 *
 * comment_status - 1 -> public
 *                - 2 -> wait for approval
 *
 * comment_relation_type - b -> blog
 *                       - e -> event
 *                       - p -> product
 *                       - p -> page
 * comment_relation_id -> id of the blog post
 * comment_parent_id -> if it's an answer
 *
 */

$database = "content";
$table_name = "se_comments";

$cols = array(
  "comment_id"  => 'INTEGER(50) NOT NULL PRIMARY KEY AUTO_INCREMENT',
  "comment_parent_id"  => "INTEGER(12)",
  "comment_relation_type"  => "VARCHAR(20) NOT NULL DEFAULT ''",
  "comment_relation_id"  => "INTEGER(12)",
  "comment_type"  => "VARCHAR(20) NOT NULL DEFAULT ''",
  "comment_status"  => "INTEGER(12)",
  "comment_time"  => "VARCHAR(20) NOT NULL DEFAULT ''",
  "comment_author"  => "VARCHAR(100) NOT NULL DEFAULT ''",
  "comment_author_mail"  => "VARCHAR(100) NOT NULL DEFAULT ''",
  "comment_author_id"  => "INTEGER(12)",
  "comment_text" => "LONGTEXT NOT NULL DEFAULT ''",
  "comment_lastedit"  => "VARCHAR(20) NOT NULL DEFAULT ''",
  "comment_lastedit_from"  => "VARCHAR(50) NOT NULL DEFAULT ''"
  );
