<?php

/**
 * When you edit a page where this theme is activated,
 * this file will be included in acp/core/pages/pages-edit.php
 */

global $get_page;

echo '<h5>Theme Values <small>(Theme: default)</small></h5>';

$page_template_data = json_decode($get_page['page_template_values'],true);

$page_template_teaser = html_entity_decode($page_template_data['teaser_text'], ENT_QUOTES | ENT_XML1, 'UTF-8');

$input_wysiwyg_page_teaser = [
    "input_name" => "theme_values[teaser_text]",
    "input_value" => $page_template_teaser,
    "label" => ' ',
    "type" => "textarea",
    "mode" => "wysiwyg"
];

echo se_print_form_input($input_wysiwyg_page_teaser);