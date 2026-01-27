<?php

/**
 * returns all files and directories
 * return array()
 */

function se_scandir_recursive($dir): array {
    $result = [];
    $root = scandir($dir);
    foreach($root as $value) {
        if($value === '.' || $value === '..'|| $value === '.DS_Store') {continue;}
        $result[]="$dir/$value";
        if(is_dir("$dir/$value")) {
            foreach(se_scandir_recursive("$dir/$value") as $value) {
                $result[]=$value;
            }
        }
    }
    if(is_array($result)) {
        $result = array_filter($result);
    }

    return $result;
}

function se_covert_big_int(int $number): string {

    if ($number < 1000) {
        return sprintf('%d', $number);
    }

    if ($number < 1000000) {
        return sprintf('%d%s', floor($number / 1000), 'K+');
    }

    if ($number >= 1000000) {
        return sprintf('%d%s', floor($number / 1000000), 'M+');
    }

    return $number;
}

/**
 * format time and date
 * formatting is set in preferences
 * @param integer $timestring
 * @return string
 */

function se_format_datetime($timestring): string {

    global $lang;
    global $se_prefs;

    $timestring = (int) $timestring;

    $date = date($se_prefs['prefs_dateformat'],$timestring);

    if($date == date($se_prefs['prefs_dateformat'], time())) {
        $str_date = $lang['today'];
    } else if($date == date($se_prefs['prefs_dateformat'], time() - (24 * 60 * 60))) {
        $str_date = $lang['yesterday'];
    } else {
        $str_date = $date;
    }

    $time = date($se_prefs['prefs_timeformat'],$timestring);

    return $str_date. ' ' .$time;
}


/**
 * convert comma separated number to float
 * @param string $number
 * @return float
 */
function se_commaToFloat($number) {
    return floatval(str_replace(',', '.', $number));
}


/**
 * Ensure .htaccess exists by copying from template.
 *
 * @param string $publicPath Path to the public directory
 * @param string $templatePath Path to the htaccess template file
 * @return bool Returns true if .htaccess exists or was created, false otherwise
 */
function se_ensure_htaccess_exists(string $publicPath, string $templatePath): bool
{
    $htaccessFile = rtrim($publicPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.htaccess';

    // Already exists → nothing to do
    if (file_exists($htaccessFile)) {
        return true;
    }

    // No template → fail
    if (!file_exists($templatePath)) {
        error_log("[SwiftyEdit] Missing htaccess template at: $templatePath");
        return false;
    }

    // Try to copy
    if (@copy($templatePath, $htaccessFile)) {
        return true;
    } else {
        error_log("[SwiftyEdit] Could not copy htaccess template. Check file permissions.");
        return false;
    }
}

function se_isAjaxRequest() {
    // HTMX
    if (isset($_SERVER['HTTP_HX_REQUEST']) && $_SERVER['HTTP_HX_REQUEST'] === 'true') {
        return true;
    }

    // XMLHttpRequest
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return true;
    }

    // Fetch API with JSON Accept
    if (isset($_SERVER['HTTP_ACCEPT']) &&
        strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        return true;
    }

    return false;
}

/**
 * Get last slug from url
 * @param string $input
 * @return string
 */
function se_getLastSlug(string $input): string {
    $path = parse_url($input, PHP_URL_PATH) ?? $input;
    return pathinfo(rtrim($path, '/'), PATHINFO_BASENAME) . '/';
}