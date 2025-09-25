<?php
/**
 * XHR and API Routes Handler
 * SwiftyEdit CMS
 */

// xhr routes for core /xhr/se/
// and plugins /xhr/plugins/{plugin}/
// and themes /xhr/themes/{theme}/

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