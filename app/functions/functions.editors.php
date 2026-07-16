<?php

/**
 * Registry for pluggable content-format editors.
 *
 * This is a separate concept from the WYSIWYG/code editor-widget switcher in
 * acp/core/functions_addons.php (se_get_editor_addons()), which only decides
 * which JS widget wraps a <textarea> - content stored there is always raw
 * HTML. This registry decides what *format* is stored in a content field at
 * all: legacy raw HTML, or JSON tagged with an "editor" key
 * ({"editor":"blocks_v1","content":{...}}), delegated to whichever plugin
 * registered that key.
 */

$SE_CONTENT_EDITORS = [];

/**
 * Register a content-format editor.
 *
 * @param string $key Editor key, matched against the "editor" field of
 *                     tagged JSON content (e.g. "blocks_v1", "markdown_v1") and
 *                     against the registering plugin's own info.json
 *                     "editor.id" (which also supplies the display name shown
 *                     in the format switch - see se_get_editor_addons()).
 * @param array $handler ['render_frontend' => callable, 'render_backend' => callable]
 */
function se_register_editor(string $key, array $handler): void
{
    global $SE_CONTENT_EDITORS;
    $SE_CONTENT_EDITORS[$key] = $handler;
}

function se_get_registered_editor(string $key): ?array
{
    global $SE_CONTENT_EDITORS;
    return $SE_CONTENT_EDITORS[$key] ?? null;
}

function se_get_registered_editors(): array
{
    global $SE_CONTENT_EDITORS;
    return $SE_CONTENT_EDITORS;
}

/**
 * Loads every editor addon's global/index.php, which is where content-format
 * editor plugins call se_register_editor() (see plugins/*-editor/global/index.php).
 * The frontend already does this for every active plugin unconditionally via
 * app/routing.php; the backend has no equivalent generic per-plugin bootstrap
 * loop, so this exists specifically for editor addons and must be called from
 * every backend entry point that needs the content-format registry populated
 * before rendering or saving a page - currently acp/index.php (main ACP page
 * render) and acp/header.php (the /admin-xhr/ read/write entry point used by
 * data-reader.php/data-writer.php).
 *
 * @return array The $se_editor_addons list (se_get_editor_addons()), for
 *         callers that also need it (e.g. to build the format-switch dropdown).
 */
function se_bootstrap_editor_plugins(): array
{
    $se_editor_addons = se_get_editor_addons();
    foreach ($se_editor_addons as $editor_addon) {
        $editor_bootstrap = SE_ROOT . 'plugins/' . $editor_addon['dir'] . '/global/index.php';
        if (is_file($editor_bootstrap)) {
            require_once $editor_bootstrap;
        }
    }
    return $se_editor_addons;
}

/**
 * Decode a raw content-field string and check whether it is tagged JSON for
 * a registered editor.
 *
 * @param string|null $raw Raw content-field value (already stripslashes()'d).
 * @return array{editor: string, content: mixed}|null Null means: treat as
 *         legacy content (plain HTML, empty, malformed JSON, or an "editor"
 *         key that has no registered handler).
 */
function se_decode_editor_content(?string $raw): ?array
{
    if ($raw === null || $raw === '') {
        return null;
    }

    $decoded = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        return null;
    }

    if (empty($decoded['editor']) || !is_string($decoded['editor'])) {
        return null;
    }

    if (se_get_registered_editor($decoded['editor']) === null) {
        return null;
    }

    return [
        'editor' => $decoded['editor'],
        'content' => $decoded['content'] ?? null,
    ];
}

/**
 * Delegate rendering of editor content to its plugin's render_frontend().
 * Core does not interpret the content itself - the plugin returns finished
 * HTML.
 */
function se_render_editor_content_frontend(string $editorKey, mixed $content): string
{
    $handler = se_get_registered_editor($editorKey);
    if ($handler === null || empty($handler['render_frontend']) || !is_callable($handler['render_frontend'])) {
        return '';
    }
    return (string) call_user_func($handler['render_frontend'], $content);
}

/**
 * Delegate backend-editing payload preparation to its plugin's
 * render_backend(). The return shape is whatever the plugin's editor UI
 * needs to initialize (e.g. a decoded block tree, a raw markdown string).
 */
function se_render_editor_content_backend(string $editorKey, mixed $content): mixed
{
    $handler = se_get_registered_editor($editorKey);
    if ($handler === null || empty($handler['render_backend']) || !is_callable($handler['render_backend'])) {
        return null;
    }
    return call_user_func($handler['render_backend'], $content);
}

/**
 * Freezes a content-format editor's JSON envelope into plain HTML at save
 * time, splitting it across two fields: "<field>_source" keeps the raw
 * {"editor":...,"content":...} JSON (what the backend editor UI needs to
 * reload), "<field>" gets overwritten with the rendered HTML (what the
 * frontend needs to display). This means:
 * - Frontend requests read plain HTML directly, no per-request render call.
 * - If the editor plugin is later deactivated/removed, "<field>" already
 *   holds valid static HTML and keeps working/editable as an ordinary
 *   legacy-HTML field.
 * If $data[$field] is not tagged editor content (plain legacy HTML, empty,
 * ...), "<field>_source" is simply cleared and "<field>" is left untouched.
 *
 * Call this right after se_sanitize_page_inputs() in the relevant save
 * function (e.g. se_save_page()/se_update_page() in
 * app/functions/functions.pages.php), before the column-whitelist filtering.
 */
function se_freeze_editor_content(array $data, string $field = 'page_content'): array
{
    $editor_content = se_decode_editor_content($data[$field] ?? '');
    if ($editor_content !== null) {
        $data[$field . '_source'] = $data[$field];
        $data[$field] = se_render_editor_content_frontend($editor_content['editor'], $editor_content['content']);
    } else {
        $data[$field . '_source'] = '';
    }
    return $data;
}
