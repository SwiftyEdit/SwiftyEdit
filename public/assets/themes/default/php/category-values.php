<?php

global $get_category;
$category_template_data = json_decode($get_category['cat_template_values'],true);
echo '<h5>Theme Values <small>(Theme: default)</small></h5>';

$category_template_teaser = html_entity_decode($category_template_data['teaser_text'], ENT_QUOTES | ENT_XML1, 'UTF-8');

$input_wysiwyg_page_teaser = [
    "input_name" => "theme_values[teaser_text]",
    "input_value" => $category_template_teaser,
    "label" => ' ',
    "type" => "textarea",
    "mode" => "wysiwyg"
];

echo se_print_form_input($input_wysiwyg_page_teaser);