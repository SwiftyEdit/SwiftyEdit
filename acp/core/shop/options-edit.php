<?php

/**
 * product option form in /admin/shop/options/edit/ and /new/,
 * markup in acp/templates/shop/option-form.twig
 *
 * global variables
 * @var array $lang
 * @var array $lang_codes
 * @var object $db_content
 * @var string $query
 * @var object $twig
 */

// the id comes from the url, after saving the writer redirects to it
$option = [];
$segments = explode('/', rtrim((string) parse_url($query, PHP_URL_PATH), '/'));
$last_segment = end($segments);
if(is_numeric($last_segment)) {
    $option = $db_content->get("se_snippets", "*", [
        "AND" => [
            "snippet_type" => "post_option",
            "snippet_id" => (int) $last_segment
        ]
    ]) ?? [];
}

/*
 * the form helpers print the values as they are: the title is stored
 * html-encoded (se_return_clean_value) and is passed unchanged,
 * the values are stored as entered (json) and are escaped by Twig
 */
$inputs = [
    'title' => se_print_form_input([
        "input_name" => "option_title",
        "input_value" => $option['snippet_title'] ?? '',
        "label" => $lang['label_title'],
        "type" => "text"
    ]),
    'priority' => se_print_form_input([
        "input_name" => "option_priority",
        "input_value" => $option['snippet_priority'] ?? '',
        "label" => $lang['label_priority'],
        "type" => "text"
    ]),
    // empty field to add a value, the stored values are listed below it
    'new_value' => se_print_form_input([
        "input_name" => "option_text[]",
        "input_value" => '',
        "label" => $lang['label_value'],
        "type" => "text"
    ])
];

$lang_options = [];
foreach(get_all_languages() as $langs) {
    if(!in_array($langs['lang_folder'],$lang_codes)) {
        continue;
    }
    $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
}

$inputs['language'] = se_print_form_input([
    "input_name" => "option_lang",
    "input_value" => $option['snippet_lang'] ?? '',
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
]);

$values = json_decode((string) ($option['snippet_content'] ?? ''), true);

echo $twig->render('shop/option-form.twig', [
    'option_id' => isset($option['snippet_id']) ? (int) $option['snippet_id'] : null,
    'writer_uri' => '/admin-xhr/shop/write/',
    'inputs' => $inputs,
    'values' => is_array($values) ? array_values($values) : []
]);
