<?php

/**
 * Shared helpers for the pages-edit form. Function library only (not
 * routed) - required by both pages-edit.php (initial page load) and
 * data-reader.php (HTMX partial swap when the content-format dropdown
 * changes), so both build the exact same field markup.
 *
 * @var array $lang
 * @var array $se_editor_addons
 */

/**
 * Builds the se_print_form_input()-ready array for the page_content field,
 * either as a "content_editor" (a registered content-format editor is
 * active) or a plain "textarea" (legacy HTML).
 *
 * @param array $get_page Raw se_pages row (or [] for a not-yet-saved page).
 * @param string|null $format_override "Switch format" request - not
 *        persisted until Save. See app/functions/functions.editors.php's
 *        se_freeze_editor_content() for why "legacy" needs no special
 *        handling here: page_content already holds the last-saved rendered
 *        HTML at all times.
 */
function se_build_page_content_field(array $get_page, ?string $format_override): array {

    global $lang, $se_editor_addons;

    $page_content_editor = se_decode_editor_content($get_page['page_content_source'] ?? '');

    if ($format_override !== null) {
        if ($format_override === 'legacy') {
            $page_content_editor = null;
        } elseif (se_get_registered_editor($format_override) !== null) {
            $page_content_editor = ['editor' => $format_override, 'content' => null];
        }
    }

    /*
     * Display names come from each plugin's info.json "editor.label" (the
     * plugin's own display name, e.g. "Sloth Editor"), not from the runtime
     * registry - that keeps a single, easily-editable source for what shows
     * up here instead of duplicating the label in PHP code (see
     * docs/v2/{de,en}/09-02-plugins.md, "editor" info.json field).
     */
    $content_format_options = ['legacy' => $lang['label_content_format_legacy']];
    foreach (se_get_registered_editors() as $format_key => $format_handler) {
        $content_format_options[$format_key] = $format_key;
        foreach ($se_editor_addons as $editor_addon) {
            if (($editor_addon['id'] ?? '') === $format_key) {
                $content_format_options[$format_key] = $editor_addon['label'] ?? $format_key;
                break;
            }
        }
    }

    $page_id = $get_page['page_id'] ?? 'new';

    if ($page_content_editor !== null) {
        return [
            "input_name" => "page_content",
            "label" => ' ',
            "type" => "content_editor",
            "editor" => $page_content_editor['editor'],
            "backend_payload" => se_render_editor_content_backend($page_content_editor['editor'], $page_content_editor['content']),
            "content_format_options" => $content_format_options,
            "content_format_value" => $page_content_editor['editor'],
            "page_id" => $page_id,
        ];
    }

    return [
        "input_name" => "page_content",
        "input_value" => htmlentities(stripslashes($get_page['page_content'] ?? ''), ENT_QUOTES, "UTF-8"),
        "label" => ' ',
        "type" => "textarea",
        "mode" => "wysiwyg",
        "content_format_options" => $content_format_options,
        "content_format_value" => "legacy",
        "page_id" => $page_id,
    ];
}
