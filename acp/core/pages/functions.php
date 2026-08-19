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

/**
 * Build <option> markup for the "parent page" dropdown on the Position tab -
 * every page of $language, as a tree (indented by depth), with $exclude_page_id
 * and its whole subtree left out so a page can never become its own
 * descendant's child. See app/functions/functions.pages.php's
 * se_index_pages_by_parent() / se_flatten_page_tree().
 *
 * @param array $pages_in_language flat se_pages rows for one language,
 *   needs at least page_id, page_parent_id, position, page_linkname, page_title
 * @param int|null $portal_id the language's portal page_id - top-level pages
 *   are its children (see install/contents/se_pages.php's "position" doc)
 * @param int|null $exclude_page_id the page being edited, if it already exists
 * @param int|null $selected_parent_id
 */
function se_build_parent_options(array $pages_in_language, ?int $portal_id, ?int $exclude_page_id, ?int $selected_parent_id): string {

    global $lang;

    // "top level" really means "parent = the portal page" - a top-level
    // page's page_parent_id is the portal's page_id, never NULL (only
    // single/portal pages themselves have NULL parent_id). A brand new page
    // (no parent chosen yet) has $selected_parent_id === null too, and
    // defaults to top level here as well.
    $is_top_level_selected = $selected_parent_id === null || $selected_parent_id === $portal_id;

    $options = '<option value=""' . ($is_top_level_selected ? ' selected' : '') . '>'
        . htmlspecialchars($lang['label_pages_parent_page_top_level'] ?? '— top level —', ENT_QUOTES) . '</option>';

    if ($portal_id === null) {
        return $options;
    }

    $index = se_index_pages_by_parent($pages_in_language);
    $flat = se_flatten_page_tree($index, $portal_id, 0, $exclude_page_id);

    foreach ($flat as $page) {
        $prefix = str_repeat('— ', $page['tree_depth'] + 1);
        // page_linkname/page_title are stored already HTML-entity-encoded
        // (se_return_clean_value() at save time) - safe to echo as-is, same
        // as se_list_pages() does in data-reader.php
        $title = $page['page_linkname'] !== '' ? $page['page_linkname'] : $page['page_title'];
        $selected = ((int) $page['page_id'] === (int) $selected_parent_id) ? ' selected' : '';
        $options .= '<option value="' . (int) $page['page_id'] . '"' . $selected . '>'
            . $prefix . $title . '</option>';
    }

    return $options;
}

/**
 * Build the "insert after which sibling" radio list shown below the parent
 * dropdown on the Position tab. Used both for the initial page render and
 * the HTMX partial swap when the parent dropdown changes (data-reader.php's
 * "page_siblings" action) - both must produce identical markup, hence the
 * shared function (same pattern as se_build_page_content_field() above).
 *
 * @param array $siblings current children of the chosen parent (excluding
 *   the page being edited), sorted by position ASC - each needs page_id,
 *   page_linkname, page_title, page_permalink
 * @param int|null $selected_after_page_id null = "at the beginning"
 */
function se_build_sibling_picker(array $siblings, ?int $selected_after_page_id): string {

    global $lang, $icon;

    // toggle buttons (like the page_role group in pages-edit.php), one per
    // row - makes what's picked obvious at a glance, unlike a small radio
    // dot next to a bare page title. The trailing arrow-bar-down icon marks
    // "inserted after this page" (see the explanatory text above the list
    // in pages-edit.php); purely decorative, so aria-hidden.
    $after_icon = '<span class="ms-auto ps-2" aria-hidden="true">' . ($icon['arrow_bar_down'] ?? '') . '</span>';

    $html = '<div class="d-grid gap-2">';

    $checked = $selected_after_page_id === null ? ' checked' : '';
    $html .= '<input type="radio" class="btn-check" name="insert_after_page_id" id="insert_after_start" value=""' . $checked . '>'
        . '<label class="btn btn-outline-primary text-start" for="insert_after_start">'
        . htmlspecialchars($lang['label_pages_insert_at_start'] ?? 'at the beginning', ENT_QUOTES)
        . '</label>';

    foreach ($siblings as $sibling) {
        // already HTML-entity-encoded in storage, see se_build_parent_options()
        $title = $sibling['page_linkname'] !== '' ? $sibling['page_linkname'] : $sibling['page_title'];
        $page_id = (int) $sibling['page_id'];
        $checked = ($page_id === (int) $selected_after_page_id) ? ' checked' : '';

        // the permalink disambiguates same-titled pages (e.g. one "Software"
        // page per product) that a bare title can't - shown smaller/muted
        // underneath, same idea as se_list_pages()'s page listing
        $permalink_line = '';
        if ($sibling['page_permalink'] !== '') {
            $permalink_line = '<span class="d-block text-truncate small opacity-75">/' . $sibling['page_permalink'] . '</span>';
        }

        $html .= '<input type="radio" class="btn-check" name="insert_after_page_id" id="insert_after_' . $page_id . '" value="' . $page_id . '"' . $checked . '>'
            . '<label class="btn btn-outline-primary text-start d-flex align-items-center" for="insert_after_' . $page_id . '">'
            . '<span class="flex-grow-1 overflow-hidden">'
            . '<span class="d-block text-truncate">' . $title . '</span>'
            . $permalink_line
            . '</span>'
            . $after_icon
            . '</label>';
    }

    $html .= '</div>';

    return $html;
}
