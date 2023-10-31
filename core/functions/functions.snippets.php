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