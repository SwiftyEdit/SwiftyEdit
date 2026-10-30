<?php

/**
 * category form in /admin/categories/edit/ and /new/,
 * markup in acp/templates/categories/edit-form.twig
 *
 * global variables
 * @var array $lang
 * @var array $lang_codes
 * @var object $db_content
 * @var array $se_settings
 * @var object $twig
 */

$writer_uri = '/admin-xhr/categories/write/';

$get_id = null;
$category_id = null; // the edited category, stays null for new categories

if(isset($_POST['category_id']) && is_numeric($_POST['category_id'])) {
    $get_id = (int) $_POST['category_id'];
}

// open category from last part of the url
$q = pathinfo($_REQUEST['query'] ?? '');
if(is_numeric($q['filename'])) {
    $get_id = (int) $q['filename'];
}

/*
 * $get_category stays a global variable,
 * the themes read it in php/category-values.php (see the theme tab below)
 */
$get_category = [];
if(is_int($get_id)) {
    $get_category = $db_content->get("se_categories", "*", [
        "cat_id" => $get_id
    ]) ?? [];
}
if(!empty($get_category)) {
    $category_id = (int) $get_category['cat_id'];
}

/*
 * the form helpers print the values as they are: the text fields are stored
 * html-encoded (sanitizeUserInputs) and are passed unchanged, teaser and text
 * are stored as html (se_purify_html) and are escaped for the textarea
 */
$text_input = static fn(string $name, string $label, string $classes = 'form-control') => se_print_form_input([
    "input_name" => $name,
    "input_value" => $get_category[$name] ?? '',
    "input_classes" => $classes,
    "label" => $label,
    "type" => "text"
]);

$editor_input = static fn(string $name, string $label) => se_print_form_input([
    "input_name" => $name,
    "input_value" => htmlspecialchars((string) ($get_category[$name] ?? ''), ENT_QUOTES, 'UTF-8'),
    "label" => $label,
    "type" => "textarea",
    "mode" => "wysiwyg"
]);

$inputs = [
    'name' => $text_input('cat_name', $lang['label_link_name']),
    'title' => $text_input('cat_title', $lang['label_title']),
    'keywords' => $text_input('cat_keywords', $lang['label_keywords'], 'form-control tags'),
    'slug' => $text_input('cat_name_clean', $lang['label_slug']),
    'teaser' => $editor_input('cat_teaser', $lang['label_teaser']),
    'text' => $editor_input('cat_text', ' ')
];

$inputs['description'] = se_print_form_input([
    "input_name" => "cat_description",
    "input_value" => $get_category['cat_description'] ?? '',
    "input_classes" => "form-control count-chars",
    "label" => $lang['label_description'],
    "type" => "textarea"
]);

$inputs['priority'] = se_print_form_input([
    "input_name" => "cat_sort",
    "input_value" => (int) ($get_category['cat_sort'] ?? 0),
    "label" => $lang['label_priority'],
    "type" => "text"
]);

$lang_options = [];
foreach(get_all_languages() as $langs) {
    if(!in_array($langs['lang_folder'],$lang_codes)) {
        continue;
    }
    $lang_options[$langs['lang_desc']] = $langs['lang_folder'];
}

$inputs['language'] = se_print_form_input([
    "input_name" => "cat_lang",
    "input_value" => ($get_category['cat_lang'] ?? '') ?: $se_settings['default_language'],
    "label" => $lang['label_language'],
    "options" => $lang_options,
    "type" => "select"
]);

/* image widget - "null" is an empty value of older versions */
$images = [];
foreach(explode("<->", (string) ($get_category['cat_thumbnail'] ?? '')) as $image) {
    if($image == '' || $image == 'null') {
        continue;
    }
    $image_src = str_replace('../content/','/',$image); // old path from SwiftyEdit 1.x
    $image_src = str_replace('../images/','/images/',$image_src);
    $images[] = [
        'path' => $image,
        'src' => $image_src,
        'name' => basename($image)
    ];
}

// themes, an empty value is the same as "use_standard"
$cat_template = (string) ($get_category['cat_template'] ?? '');
if($cat_template == '') {
    $cat_template = 'use_standard';
}
$themes = [];
foreach(get_all_templates() as $theme) {
    if($theme == 'administration') {
        continue;
    }
    $themes[] = [
        'name' => $theme,
        'selected' => ($theme == $cat_template)
    ];
}

/*
 * theme values: the theme of the category (or the default theme) can add
 * its own fields via php/category-values.php, they are posted as theme_values[...]
 */
$theme_base = SE_ROOT.'public/assets/themes/'.($cat_template == 'use_standard' ? $se_settings['template'] : $cat_template);
$theme_values = null;
foreach(['category-values.php', 'category_values.php'] as $file) {
    $path = $theme_base.'/php/'.$file;
    if(is_file($path)) {
        $include_theme_values = static function(string $file) {
            ob_start();
            include $file;
            return ob_get_clean();
        };
        $theme_values = $include_theme_values($path);
        break;
    }
}

echo $twig->render('categories/edit-form.twig', [
    'category_id' => $category_id,
    'submit_label' => ($category_id ? $lang['update'] : $lang['save']),
    'writer_uri' => $writer_uri,
    'inputs' => $inputs,
    'images' => $images,
    'themes' => $themes,
    'template_standard' => ($cat_template == 'use_standard'),
    'theme_values' => $theme_values
]);
