<?php

/**
 * Shared markup for the branding upload fields (page logo, page thumbnail,
 * favicon) shown on the general settings page. The actual upload UI is an
 * Uppy Dashboard (same library/pattern as the global ACP upload modal, see
 * public/assets/themes/administration/src/js/backend.js) mounted client-side
 * into the ".branding-dropzone" element - this file only renders the static
 * markup and the data-* attributes that JS reads to configure Uppy.
 *
 * Included from three different places:
 * - acp/core/settings/general.php (initial page render, full ACP bootstrap)
 * - acp/core/widgets/upload.php (preview fragment after a successful upload, bare bootstrap)
 * - acp/core/settings/data-writer.php (preview fragment after removal, full ACP bootstrap)
 *
 * Because acp/core/widgets/upload.php does not load $lang/$icon/Twig, these
 * functions intentionally do not depend on them - all text passed in is
 * plain English, matching the existing hardcoded labels ("Page Logo",
 * "Favicon", ...) already used on this settings page. Translated button/
 * confirm text is instead threaded through as upload meta (see
 * se_render_branding_field()) so upload.php can echo it back untranslated-free.
 */

/**
 * Renders just the preview + remove button for one branding slot. This is
 * the piece that gets swapped out (by HTMX on remove, by plain JS after an
 * Uppy upload finishes) - the surrounding dropzone is never replaced, so the
 * Uppy instance mounted into it stays alive across repeated uploads.
 *
 * Uses the same fixed-size "show-thumb" + hover-popover pattern as the
 * product/post/event list thumbnails, so the three fields stay the same
 * height regardless of the uploaded images' aspect ratios.
 *
 * @param string $target             one of 'logo', 'thumbnail', 'favicon'
 * @param string $current_filename   filename currently stored in settings, or '' / 'null' if none
 * @param string $branding_web_path  web-relative path to the branding folder (e.g. "assets/branding")
 * @param string $delete_uri         XHR endpoint the remove button posts to
 * @param string $remove_label       translated "Remove"/"Löschen" label (falls back to English)
 * @param string $confirm_text       translated remove-confirmation text (falls back to English)
 */
function se_render_branding_preview(
    string $target,
    string $current_filename,
    string $branding_web_path,
    string $delete_uri,
    string $remove_label = 'Remove',
    string $confirm_text = 'Are you sure you want to delete this file?'
): string {

    $preview_id = 'branding-preview-' . $target;
    $has_file = ($current_filename !== '' && $current_filename !== 'null');

    if ($has_file) {
        // cache-bust so a re-upload with the same extension shows immediately
        $src = '/' . trim($branding_web_path, '/') . '/' . rawurlencode($current_filename) . '?v=' . time();
        $preview = '<div data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true"'
            . ' style="display:inline-block;"'
            . ' data-bs-content="<img src=\'' . $src . '\' style=\'max-width:300px;max-height:300px;\'>">'
            . '<div class="show-thumb" style="background-image: url(' . $src . ');"></div>'
            . '</div>';
    } else {
        $preview = '<div class="show-thumb" style="background-image: url(/themes/administration/images/no-image.png);"></div>';
    }

    $remove_button = '';
    if ($has_file) {
        $remove_vals = htmlspecialchars(json_encode(['delete_branding' => $target]), ENT_QUOTES);
        $remove_button = '<button type="button" class="btn btn-outline-danger btn-sm"'
            . ' hx-post="' . htmlspecialchars($delete_uri, ENT_QUOTES) . '"'
            . ' hx-vals="' . $remove_vals . '"'
            . ' hx-include="[name=\'csrf_token\']"'
            . ' hx-target="#' . $preview_id . '"'
            . ' hx-swap="outerHTML"'
            . ' hx-confirm="' . htmlspecialchars($confirm_text, ENT_QUOTES) . '">'
            . '<i class="bi bi-trash"></i> ' . htmlspecialchars($remove_label, ENT_QUOTES) . '</button>';
    }

    return '<div id="' . $preview_id . '" class="mb-2 d-flex align-items-center justify-content-between">' . $preview . $remove_button . '</div>';
}

/**
 * Renders the full field: label, preview (via se_render_branding_preview),
 * and the persistent Uppy dropzone mount point.
 *
 * @param string $target             one of 'logo', 'thumbnail', 'favicon'
 * @param string $label              field label
 * @param string $current_filename   filename currently stored in settings, or '' / 'null' if none
 * @param string $branding_web_path  web-relative path to the branding folder (e.g. "assets/branding")
 * @param string $upload_uri         XHR endpoint Uppy uploads to
 * @param string $delete_uri         XHR endpoint the remove button posts to
 * @param string $csrf_token         current session CSRF token, sent as upload meta
 * @param string $remove_label       translated "Remove" label, threaded through as upload meta
 * @param string $confirm_text       translated remove-confirmation text, threaded through as upload meta
 * @param int    $max_w              max width passed through to the upload endpoint (0 = no resize, e.g. favicon)
 * @param int    $max_h              max height passed through to the upload endpoint
 * @param array  $extra_meta         extra name => value pairs sent along as upload meta (e.g. manifest data)
 * @param string $hint               optional help text shown below the field
 * @param string $unchanged          'yes' to skip resizing (mirrors the "leave uploads unchanged" setting), '' otherwise
 */
function se_render_branding_field(
    string $target,
    string $label,
    string $current_filename,
    string $branding_web_path,
    string $upload_uri,
    string $delete_uri,
    string $csrf_token,
    string $remove_label,
    string $confirm_text,
    int $max_w = 0,
    int $max_h = 0,
    array $extra_meta = [],
    string $hint = '',
    string $unchanged = ''
): string {

    $dropzone_id = 'branding-dropzone-' . $target;

    $meta = array_merge([
        'branding_target' => $target,
        'w' => (string) $max_w,
        'h' => (string) $max_h,
        'csrf_token' => $csrf_token,
        'unchanged' => $unchanged,
        'remove_label' => $remove_label,
        'confirm_text' => $confirm_text
    ], $extra_meta);

    $hint_html = '';
    if ($hint !== '') {
        $hint_html = '<div class="form-text">' . htmlspecialchars($hint, ENT_QUOTES) . '</div>';
    }

    return '<div id="branding-field-' . $target . '" class="mb-3">'
        . '<label class="form-label">' . htmlspecialchars($label, ENT_QUOTES) . '</label>'
        . se_render_branding_preview($target, $current_filename, $branding_web_path, $delete_uri, $remove_label, $confirm_text)
        . '<div id="' . $dropzone_id . '" class="branding-dropzone"'
        . ' data-upload-uri="' . htmlspecialchars($upload_uri, ENT_QUOTES) . '"'
        . ' data-preview-target="branding-preview-' . $target . '"'
        . ' data-meta="' . htmlspecialchars(json_encode($meta), ENT_QUOTES) . '"></div>'
        . $hint_html
        . '</div>';
}
