<?php

/**
 * product feature form in /admin/shop/features/edit/ and /new/,
 * markup in acp/templates/shop/feature-form.twig
 *
 * global variables
 * @var array $lang
 * @var array $lang_codes
 * @var object $db_content
 * @var string $query
 * @var object $twig
 */

// the id comes from the url, after a new feature the writer redirects to it
$feature = [];
$segments = explode('/', rtrim((string) parse_url($query, PHP_URL_PATH), '/'));
$last_segment = end($segments);
if(is_numeric($last_segment)) {
    $feature = $db_content->get("se_snippets", "*", [
        "AND" => [
            "snippet_type" => "post_feature",
            "snippet_id" => (int) $last_segment
        ]
    ]) ?? [];
}

/*
 * the form helpers print the values as they are: the title is stored
 * html-encoded (se_return_clean_value) and is passed unchanged, the text is
 * stored as html and is escaped for the textarea
 */
$inputs = [
    'title' => se_print_form_input([
        "input_name" => "feature_title",
        "input_value" => $feature['snippet_title'] ?? '',
        "label" => $lang['label_title'],
        "type" => "text"
    ]),
    'priority' => se_print_form_input([
        "input_name" => "feature_priority",
        "input_value" => $feature['snippet_priority'] ?? '',
        "label" => $lang['label_priority'],
        "type" => "text"
    ]),
    'text' => se_print_form_input([
        "input_name" => "feature_text",
        "input_value" => htmlspecialchars((string) ($feature['snippet_content'] ?? ''), ENT_QUOTES, 'UTF-8'),
        "label" => ' ',
        "type" => "textarea",
        "mode" => "wysiwyg"
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
    "input_name" => "feature_lang",
    "input_value" => $feature['snippet_lang'] ?? '',
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
]);

echo $twig->render('shop/feature-form.twig', [
    'feature_id' => isset($feature['snippet_id']) ? (int) $feature['snippet_id'] : null,
    'writer_uri' => '/admin-xhr/shop/write/',
    'inputs' => $inputs
]);
