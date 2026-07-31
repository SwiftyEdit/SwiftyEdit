<?php

/**
 * When you edit a page where this theme is activated,
 * this file will be included in acp/core/pages/pages-edit.php
 */

global $get_page;

require_once __DIR__.'/theme-components.php';

$theme_lang = se_return_addon_translations('default', 'theme');

echo '<h5>'.$theme_lang['title_theme_values'].' <small>(Theme: default)</small></h5>';

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

/**
 * Which optional theme components (CSS+JS) get loaded on this page, on top
 * of the core bundle (layout, header/footer, dark mode, ...) that is always
 * loaded regardless of this setting. Pages saved before this option existed
 * have no "components_mode" key yet, so default to "all" - the same
 * behaviour as the old single-bundle build.
 */
$components_mode = $page_template_data['components_mode'] ?? 'all';

$input_select_components_mode = [
    "input_name" => "theme_values[components_mode]",
    "input_value" => $components_mode,
    "label" => $theme_lang['label_include_style_files'],
    "options" => [
        $theme_lang['option_load_all_components'] => 'all',
        $theme_lang['option_load_selected_components'] => 'custom',
    ],
    "type" => "select",
];

echo se_print_form_input($input_select_components_mode);

$component_ids = se_theme_component_ids(__DIR__.'/../dist');

$picker_classes = 'mb-3' . ($components_mode === 'custom' ? '' : ' d-none');

echo '<div id="theme_components_picker" class="'.$picker_classes.'">';
echo '<p class="form-text mt-0">'.$theme_lang['help_components_picker'].'</p>';
foreach ($component_ids as $id) {
    $key = 'comp_'.$id;
    $checked = ($page_template_data[$key] ?? '') == '1' ? 'checked' : '';
    $input_checkbox_component = [
        "input_name" => "theme_values[$key]",
        "input_value" => "1",
        "label" => $theme_lang['label_component_'.$id] ?? ucfirst($id),
        "status" => $checked,
        "type" => "checkbox",
    ];
    echo se_print_form_input($input_checkbox_component);
}
echo '</div>';

echo '<script>';
echo 'document.querySelector(\'select[name="theme_values[components_mode]"]\').addEventListener("change", function(e) {';
echo 'document.getElementById("theme_components_picker").classList.toggle("d-none", e.target.value !== "custom");';
echo '});';
echo '</script>';