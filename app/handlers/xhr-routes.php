<?php
/**
 * XHR Routes Handler (public API: see api-routes.php)
 * SwiftyEdit CMS
 */

// xhr routes for core /xhr/se/
// and plugins /xhr/plugins/{plugin}/
// and themes /xhr/themes/{theme}/

// xhr urls don't belong to a page, so $languagePack is still the default language -
// use the language of the page the request was sent from (HTMX header, fetch() Referer)
$xhr_source_url = $_SERVER['HTTP_HX_CURRENT_URL'] ?? $_SERVER['HTTP_REFERER'] ?? '';
if($xhr_source_url != '') {
    $xhr_page_language = se_get_page_language_by_url($xhr_source_url);
    if($xhr_page_language !== null && $xhr_page_language != $languagePack && is_dir(SE_ROOT.'languages/'.basename($xhr_page_language))) {
        $languagePack = basename($xhr_page_language);
        require SE_ROOT.'languages/index.php';
    }
}

if ($requestPathParts[1] === 'se') {
    // route for SwiftyEdit
    include SE_ROOT.'/app/xhr/route.php';
    exit;
} elseif ($requestPathParts[1] === 'plugins' && isset($requestPathParts[2])) {
    // route for (activated) plugins
    $plugin_name = basename($requestPathParts[2]);
    if(in_array($plugin_name, $active_plugins)) {
        $plugin_xhr = SE_ROOT.'/plugins/'.$plugin_name.'/global/xhr.php';
        if(is_file($plugin_xhr)) {
            include $plugin_xhr;
        }
        exit;
    } else {
        exit;
    }
} elseif ($requestPathParts[1] === 'themes' && isset($requestPathParts[2])) {
    // route for themes
    $theme_name = basename($requestPathParts[2]);
    $theme_xhr = SE_PUBLIC.'/assets/themes/'.$theme_name.'/php/xhr.php';
    if(is_file($theme_xhr)) {
        include $theme_xhr;
    }
    exit;
} else {
    http_response_code(404);
    exit;
}