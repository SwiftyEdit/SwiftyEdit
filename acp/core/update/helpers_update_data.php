<?php

/**
 * global variables
 * @var object $db_content
 * @var object $db_posts
 * @var object $db_user
 */

if($_POST['helper_update_table'] == 'se_content') {
    echo '<p>searching for replace in contents ...</p>';

    // replace relative path first
    $search = '../content/images/';
    $replace = '/images/';

    $db_content->replace("se_pages", ["page_content" => [ "$search" => "$replace" ]]);
    $db_content->replace("se_pages", ["page_template_values" => [ "$search" => "$replace" ]]);
    $db_content->replace("se_pages", ["page_thumbnail" => ["$search" => "$replace"]]);
    $db_content->replace("se_snippets", ["snippet_content" => ["$search" => "$replace"]]);
    $db_content->replace("se_snippets", ["snippet_images" => ["$search" => "$replace"]]);
    $db_content->replace("se_categories", ["cat_thumbnail" => ["$search" => "$replace"]]);
    echo '<p class="text-success">Replaced <code>'. $search .'</code>  with <code>'. $replace .'</code></p>';

    // images in se_media
    $search = '../content/images/';
    $replace = '../images/';
    $db_content->replace("se_media", ["media_file" => [ "$search" => "$replace" ]]);

    // files in se_media
    $search = '../content/files/';
    $replace = '../files/';
    $db_content->replace("se_media", ["media_file" => [ "$search" => "$replace" ]]);
    echo '<p class="text-success">Replaced <code>'. $search .'</code>  with <code>'. $replace .'</code></p>';

    // thumbnails in se_media
    $search = '../content/images_tmb/';
    $replace = '/images_tmb/';
    $db_content->replace("se_media", ["media_thumb" => [ "$search" => "$replace" ]]);
    echo '<p class="text-success">Replaced <code>'. $search .'</code>  with <code>'. $replace .'</code></p>';

    // replace absolute path
    $search = '/content/images/';
    $replace = '/images/';

    $db_content->replace("se_pages", ["page_content" => [ "$search" => "$replace" ]]);
    $db_content->replace("se_pages", ["page_template_values" => [ "$search" => "$replace" ]]);
    $db_content->replace("se_pages", ["page_thumbnail" => ["$search" => "$replace"]]);
    $db_content->replace("se_snippets", ["snippet_content" => ["$search" => "$replace"]]);
    $db_content->replace("se_snippets", ["snippet_images" => ["$search" => "$replace"]]);
    $db_content->replace("se_categories", ["cat_thumbnail" => ["$search" => "$replace"]]);
    echo '<p class="text-success">Replaced <code>'. $search .'</code>  with <code>'. $replace .'</code></p>';
    exit;
}

if($_POST['helper_update_table'] == 'se_posts') {
    echo '<p>searching for replace in posts ...</p>';
    // replace relative path first
    $search = '../content/images/';
    $replace = '/images/';
    $db_posts->replace("se_posts", ["post_teaser" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_posts", ["post_text" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_posts", ["post_images" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_products", ["teaser" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_products", ["text" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_products", ["images" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_events", ["teaser" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_events", ["text" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_events", ["images" => [ "$search" => "$replace" ]]);
    echo '<p class="text-success">Replaced <code>'. $search .'</code>  with <code>'. $replace .'</code></p>';

    // replace absolute path
    $search = '/content/images/';
    $replace = '/images/';
    $db_posts->replace("se_posts", ["post_teaser" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_posts", ["post_text" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_posts", ["post_images" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_products", ["teaser" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_products", ["text" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_products", ["images" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_events", ["teaser" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_events", ["text" => [ "$search" => "$replace" ]]);
    $db_posts->replace("se_events", ["images" => [ "$search" => "$replace" ]]);
    echo '<p class="text-success">Replaced <code>'. $search .'</code>  with <code>'. $replace .'</code></p>';
    exit;
}

// The former se_users_country / se_delivery_areas_country / se_users_uuid /
// se_orders_uuid / se_products_uuid buttons now run automatically as
// migrations (see install/migrations/) instead of needing a manual click
// here. The former page_content_source_column button is gone entirely -
// that column has been part of install/contents/se_pages.php and
// se_pages_cache.php for a while, so the regular additive schema-sync
// (update_database()) already adds it on every update; the manual button
// was redundant.
