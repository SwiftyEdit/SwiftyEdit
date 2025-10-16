<?php
/**
 * URL Routing Handler
 * SwiftyEdit CMS
 */

/**
 * reserved $_GET['p'] parameters
 */
$a_allowed_p = [
    'account',
    'checkout',
    'display_event',
    'display_post',
    'display_product',
    'logout',
    'orders',
    'password',
    'profile',
    'register',
    'search',
    'sitemap',
    'tagged',
    'unlock'
];

/*
 * mod_rewrite
 * $query defined by the .htaccess file
 * RewriteRule ^(.*)$ index.php?query=$1 [L,QSA]
 */
if(isset($_GET['query'])) {
    $query = se_clean_query($_GET['query']);
}

if(!isset($query)) {
    $query = '/';
}

/**
 * query.controller.php
 * This is a very old include function.
 * However, we are leaving it in place for reasons of compatibility
 */
if(is_file(SE_CONTENT.'/includes/query.controller.php')) {
    include SE_CONTENT.'/includes/query.controller.php';
}

// Handle logout
if($query == 'logout' OR (isset($_GET['goto']) && ($_GET['goto'] == 'logout'))) {
    $user_logout = se_end_user_session();
    $query = '/';
}


// Remove embedded query strings from the path
if (str_contains($query, '?')) {
    list($clean_path, $embedded_params) = explode('?', $query, 2);
    parse_str($embedded_params, $extra_params);

    // Merge embedded params into $_GET (without overwriting existing)
    foreach ($extra_params as $key => $value) {
        if (!isset($_GET[$key])) {
            $_GET[$key] = $value;
        }
    }

    $query = $clean_path;
    $_GET['query'] = $clean_path; // Update for consistency
}

// Ensure query is set
if (empty($query)) {
    $query = '/';
}

$swifty_slug = $query;
$requestPathParts = explode('/', trim($swifty_slug, '/'));

$active_mods = se_get_active_mods();
$cnt_active_mods = is_array($active_mods) ? count($active_mods) : 0;

// get existing urls from the cache file
$cache_file_active_urls = SE_CONTENT . '/cache/active_urls.json';
if(file_exists($cache_file_active_urls)) {
    $cached_url_data = json_decode(file_get_contents($cache_file_active_urls), true);
}

$query_is_cached = false;
foreach($cached_url_data as $cached_url) {
    if ($cached_url['page_permalink'] === $query) {
        $query_is_cached = true;
        break;
    }
}

// loop through installed modules
for($i=0;$i<$cnt_active_mods;$i++) {
    $mod_permalink = $active_mods[$i]['page_permalink'];
    $mod_name = $active_mods[$i]['page_modul'];
    $active_plugins[] = $active_mods[$i]['page_modul'];
    $permalink_length = strlen($mod_permalink);

    if(!empty($mod_permalink) && str_contains("$query", "$mod_permalink")) {
        if(strncmp($mod_permalink, $query, $permalink_length) == 0) {
            $mod_slug = substr($query, $permalink_length);
            $swifty_slug = substr("$query",0,$permalink_length);
            if($query_is_cached) {
                $swifty_slug = $query;
            }
        }
    }
}

// Load page content based on routing
if($swifty_slug == '/' OR $swifty_slug == '') {
    list($page_contents,$se_nav) = se_get_content('portal','page_sort');
} else {
    list($page_contents,$se_nav) = se_get_content($swifty_slug,'permalink');
}

// include (once) modules global/index.php if exists
foreach($active_mods as $mods) {
    $clean_mods[] = $mods['page_modul'];
}

if(is_array($clean_mods)) {
    $clean_mods = array_unique($clean_mods);

    foreach ($clean_mods as $mod_dir) {
        if (is_file(SE_ROOT . '/plugins/' . $mod_dir . '/global/index.php')) {
            include_once SE_ROOT . '/plugins/' . $mod_dir . '/global/index.php';
        }
    }
}