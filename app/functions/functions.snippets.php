<?php

/**
 * SwiftyEdit
 * global snippet functions
 *
 */

/**
 * @return array
 * get all keywords
 * key is the keyword, value the counter
 */
function se_get_snippet_keywords() {

    global $db_content;

    $get_keywords = $db_content->select("se_snippets", "snippet_keywords",[
        "snippet_keywords[!]" => ""
    ]);

    $get_keywords = array_filter( $get_keywords );

    foreach($get_keywords as $keys) {
        $keys_string .= $keys.',';
    }
    $keys_array = explode(",",$keys_string);
    $keys_array = array_filter( $keys_array );
    $count_keywords = array_count_values($keys_array);

    return $count_keywords;
}

/**
 * Register a placeholder that can be used inside snippet content, e.g. {sku}.
 * Only registered keys are replaced (whitelist), all other {...} stay untouched.
 * Values are escaped for HTML output.
 *
 * @param string $key name of the placeholder without braces
 * @param mixed $value
 * @return void
 */
function se_set_snippet_var(string $key, $value): void {
    global $se_snippet_vars;
    $se_snippet_vars['{'.$key.'}'] = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8', false);
}

/**
 * Replace all registered placeholders in a snippet's text
 *
 * @param mixed $text
 * @return mixed
 */
function se_replace_snippet_vars($text) {
    global $se_snippet_vars;

    if(!is_string($text) || empty($se_snippet_vars) || !str_contains($text, '{')) {
        return $text;
    }

    return strtr($text, $se_snippet_vars);
}