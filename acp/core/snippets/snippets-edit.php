<?php

/**
 * snippet form in /admin/snippets/edit/, /new/ and /duplicate/,
 * markup in acp/templates/snippets/edit-form.twig
 *
 * global variables
 * @var array $lang
 * @var array $lang_codes
 * @var string $languagePack
 * @var object $db_content
 * @var object $twig
 */

$writer_uri = '/admin-xhr/snippets/write/';

$get_id = null;
$snippet_id = null;  // the edited snippet, stays null for new and duplicated snippets
$submit_label = $lang['save'];

if(isset($_POST['snippet_id']) && is_numeric($_POST['snippet_id'])) {
    $get_id = (int) $_POST['snippet_id'];
    $snippet_id = $get_id;
    $submit_label = $lang['update'];
}

// open snippet from last part of the url
$q = pathinfo($_REQUEST['query'] ?? '');
if(is_numeric($q['filename'])) {
    $get_id = (int) $q['filename'];
    $snippet_id = $get_id;
    $submit_label = $lang['update'];
}

if(isset($_POST['duplicate_id']) && is_numeric($_POST['duplicate_id'])) {
    $get_id = (int) $_POST['duplicate_id'];
    $snippet_id = null;
    $submit_label = $lang['duplicate'];
}

$snippet = [];
if(is_int($get_id)) {
    $snippet = $db_content->get("se_snippets", "*", [
        "snippet_id" => $get_id
    ]) ?? [];
}

/*
 * the form helpers print the values as they are: the text fields are stored
 * html-encoded (sanitizeUserInputs) and are passed unchanged, the content is
 * stored as entered (html) and is escaped for the textarea
 */
$text_input = static fn(string $name, string $label) => se_print_form_input([
    "input_name" => $name,
    "input_value" => $snippet[$name] ?? '',
    "label" => $label,
    "type" => "text"
]);

$inputs = [
    'name' => $text_input('snippet_name', $lang['label_name'].' <sup>(a-z,0-9)</sup>'),
    'title' => $text_input('snippet_title', $lang['label_title']),
    'keywords' => $text_input('snippet_keywords', $lang['label_keywords']),
    'label' => $text_input('snippet_label', 'Label'),
    'classes' => $text_input('snippet_classes', $lang['label_classes']),
    'link' => $text_input('snippet_permalink', $lang['label_url']),
    'link_name' => $text_input('snippet_permalink_name', $lang['label_link_name']),
    'link_title' => $text_input('snippet_permalink_title', $lang['label_title']),
    'link_classes' => $text_input('snippet_permalink_classes', $lang['label_classes'])
];

$inputs['content'] = se_print_form_input([
    "input_name" => "snippet_content",
    "input_value" => htmlspecialchars((string) ($snippet['snippet_content'] ?? ''), ENT_QUOTES, 'UTF-8'),
    "label" => 'editor',
    "type" => "textarea",
    "mode" => "wysiwyg"
]);

$inputs['priority'] = se_print_form_input([
    "input_name" => "snippet_priority",
    "input_value" => (int) ($snippet['snippet_priority'] ?? 0),
    "label" => $lang['label_priority'],
    "type" => "text"
]);

// language select with icon and full names, see se_print_language_select() in acp/core/templates.php
$inputs['language'] = se_print_language_select('snippet_lang', (string) (($snippet['snippet_lang'] ?? '') ?: $languagePack));

/* image widget */
$images = [];
foreach(array_filter(explode("<->", (string) ($snippet['snippet_images'] ?? ''))) as $image) {
    $image_src = str_replace('../content/','/',$image); // old path from SwiftyEdit 1.x
    $image_src = str_replace('../images/','/images/',$image_src);
    $images[] = [
        'path' => $image,
        'src' => $image_src,
        'name' => basename($image)
    ];
}

// labels
$checked_labels = [];
if(($snippet['snippet_labels'] ?? '') != '') {
    $checked_labels = explode(",", $snippet['snippet_labels']);
}

// snippet templates of the themes (templates/snippet*.tpl)
$snippet_theme = $snippet['snippet_theme'] ?? '';
$snippet_template = $snippet['snippet_template'] ?? '';
$themes = [];
foreach(get_all_templates() as $theme) {
    if($theme == 'administration') {
        continue;
    }
    $templates = [];
    foreach(glob("../public/assets/themes/$theme/templates/snippet*.tpl") as $template_file) {
        $template_file = basename($template_file);
        $templates[] = [
            'file' => $template_file,
            'selected' => ($theme == $snippet_theme && $template_file == $snippet_template)
        ];
    }
    $themes[] = [
        'name' => $theme,
        'templates' => $templates
    ];
}

// header card above the form (components/edit-header.twig) - title falls back to the name
$decode = static fn($value): string => html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
$header = [
    'mode' => ($snippet_id !== null) ? 'edit' : (isset($_POST['duplicate_id']) ? 'duplicate' : 'new'),
    'title' => $decode(($snippet['snippet_title'] ?? '') ?: ($snippet['snippet_name'] ?? '')),
    'name' => $decode($snippet['snippet_name'] ?? ''),
    'language' => ($snippet['snippet_lang'] ?? '') ?: $languagePack
];

echo $twig->render('snippets/edit-form.twig', [
    'header' => $header,
    'snippet_id' => $snippet_id,
    'submit_label' => $submit_label,
    'writer_uri' => $writer_uri,
    'inputs' => $inputs,
    'images' => $images,
    'labels' => se_get_label_checklist($checked_labels),
    'themes' => $themes,
    'template_standard' => ($snippet_template == '')
]);
