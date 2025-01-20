<?php

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
