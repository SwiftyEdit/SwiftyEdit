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

if($_POST['helper_update_table'] == 'se_users_country') {
    echo '<p>Migrating country fields to ISO 3166-1 alpha-2 codes ...</p>';

    $migration_map = se_get_country_migration_map();
    $users = $db_user->select("se_user", ["user_id", "ba_country", "sa_country"]);

    $updated = 0;
    $skipped = 0;

    foreach ($users as $user) {
        $ba_country = $migration_map[$user['ba_country']] ?? $user['ba_country'];
        $sa_country = $migration_map[$user['sa_country']] ?? $user['sa_country'];

        if ($ba_country !== $user['ba_country'] || $sa_country !== $user['sa_country']) {
            $db_user->update("se_user", [
                "ba_country" => $ba_country,
                "sa_country" => $sa_country,
            ], [
                "user_id" => $user['user_id']
            ]);
            $updated++;
        } else {
            $skipped++;
        }
    }

    echo '<p class="text-success">Updated <code>' . $updated . '</code> users, skipped <code>' . $skipped . '</code></p>';
    exit;
}

if($_POST['helper_update_table'] == 'se_delivery_areas_country') {
    echo '<p>Migrating delivery areas to ISO 3166-1 alpha-2 codes ...</p>';

    $migration_map = se_get_country_migration_map();
    $areas = $db_content->select("se_delivery_areas", ["id", "name", "code"]);

    $updated = 0;
    $skipped = 0;

    foreach ($areas as $area) {
        $code = $migration_map[$area['name']] ?? null;

        if ($code && $code !== $area['code']) {
            $db_content->update("se_delivery_areas", [
                "code" => $code,
            ], [
                "id" => $area['id']
            ]);
            $updated++;
        } else {
            $skipped++;
        }
    }

    echo '<p class="text-success">Updated <code>' . $updated . '</code> delivery areas, skipped <code>' . $skipped . '</code></p>';
    exit;
}