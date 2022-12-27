<?php

/**
 * votings -> 1 = no, 2 = yes for registered useres, 3 = yes for everybody
 *
 * event_guestlist -> 1 = deactivated, 2 = for registered users, 3 = everybody can confirm
 * event_guestlist_public_nbr -> 1 = hide, 2 = show
 * event_guestlist_limit -> null = no limit, number = limit of guests
 *
 */

$database = "posts";
$table_name = "se_events";

$cols = array(
    "id" => 'INTEGER(50) NOT NULL PRIMARY KEY AUTO_INCREMENT',
    "date"  => 'INTEGER(12)',
    "releasedate"  => 'INTEGER(12)',
    "lastedit"  => 'INTEGER(12)',
    "lastedit_from"  => "VARCHAR(50) NOT NULL DEFAULT ''",
    "title" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "teaser" => "LONGTEXT NOT NULL DEFAULT ''",
    "text" => "LONGTEXT NOT NULL DEFAULT ''",
    "images" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "tags" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "categories" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "comments" => 'INTEGER(12)',
    "author" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "status" => 'INTEGER(12)',
    "rss" => 'INTEGER(12)',
    "rss_url" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "event_lang" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "slug" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "priority" => 'INTEGER(12)',
    "fixed" => 'INTEGER(12)',
    "hits" => 'INTEGER(12)',
    "votings" => 'INTEGER(12)',
    "labels" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "template_values" => "LONGTEXT NOT NULL DEFAULT ''",
    /* meta data */
    "meta_title" => "VARCHAR(255) NOT NULL DEFAULT ''",
    "meta_description" => "VARCHAR(255) NOT NULL DEFAULT ''",
    /* events */
    "event_startdate"  => 'INTEGER(12)',
    "event_enddate" => 'INTEGER(12)',
    "event_zip" => "VARCHAR(50) NOT NULL DEFAULT ''",
    "event_city" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "event_street" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "event_street_nbr" => "VARCHAR(100) NOT NULL DEFAULT ''",
    "event_price_note" => "LONGTEXT NOT NULL DEFAULT ''",
    "event_guestlist" => 'INTEGER(12)',
    "event_guestlist_public_nbr" => 'INTEGER(12)',
    "event_guestlist_limit" => "VARCHAR(50) NOT NULL DEFAULT ''"

);
