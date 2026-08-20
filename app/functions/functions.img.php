<?php

/**
 * Smarty {img} function - renders an <img> tag, optionally enhanced with
 * responsive width variants served in a smaller/modern format.
 *
 * Core owns the attribute contract below, not any plugin. Without an active
 * image-processing plugin this degrades gracefully to a plain
 * <img src="..."> using only $src and the passthrough attributes, so themes
 * can adopt {img} today without depending on a plugin being installed, and
 * without needing to change anything later if one is added.
 *
 * Resizing/format-negotiation itself never happens in core: {img} applies
 * the 'image.variants' frontend filter hook (see app/hooks/hooks-map.php)
 * and lets an active plugin (e.g. an image-resizer addon) fill in the
 * variant 'src'/'srcset'. If no plugin is listening, the filter returns the
 * value unchanged (null) and {img} falls back to the plain tag.
 *
 * Template usage:
 *   {img src="/images/foo.jpg" widths="400,800,1200" ratio="4:3" fit="cover"
 *        sizes="(max-width: 600px) 100vw, 800px" alt="..." class="..." loading="lazy"}
 *
 * @param array $params Smarty tag attributes
 * @param object $template Smarty template instance (unused, required by Smarty's function plugin signature)
 * @return string
 */
function se_smarty_function_img($params, $template)
{
    $src = $params['src'] ?? '';
    if ($src === '') {
        return '';
    }

    // Resizing-relevant attributes - the only ones the filter hook ever sees.
    // sizes/alt/class/... below are pure presentation and never reach it.
    $variants = se_apply_frontend_filters('image.variants', null, [
        'src'    => $src,
        'widths' => $params['widths'] ?? '',
        'ratio'  => $params['ratio'] ?? '',
        'fit'    => $params['fit'] ?? 'cover',
    ]);

    // Effective src/srcset - either from an active plugin, or the plain fallback.
    $out_src = $src;
    $out_srcset = '';

    if (is_array($variants) && !empty($variants['src'])) {
        $out_src = $variants['src'];
        $out_srcset = $variants['srcset'] ?? '';
    }

    $attrs = ['src' => $out_src];

    if ($out_srcset !== '') {
        $attrs['srcset'] = $out_srcset;
    }
    if (!empty($params['sizes'])) {
        $attrs['sizes'] = $params['sizes'];
    }

    // Plain HTML passthrough attributes - never touched by the filter above.
    // isset() only, deliberately NOT "&& !== ''": a template that writes
    // alt="{$maybe_empty_var}" means alt="" whenever that var is empty, not
    // "no alt attribute at all" - those are not the same thing for
    // accessibility (alt="" marks the image as decorative for screen
    // readers; a missing alt attribute does not, and can fall back to
    // announcing the filename instead).
    foreach (['alt', 'class', 'id', 'loading', 'width', 'height', 'title'] as $passthrough) {
        if (isset($params[$passthrough])) {
            $attrs[$passthrough] = $params[$passthrough];
        }
    }

    // Auto-fill width/height when the template didn't set both explicitly,
    // so the browser can reserve the correct box before the image loads
    // (avoids layout shift - CLS). Only ever fills in a pair that's
    // guaranteed to match what's actually served, never a guess: derived
    // from "ratio" when a crop was requested (that's what will actually be
    // served), or read from the original file when it wasn't (the served
    // image then preserves the original's own proportions). If neither is
    // possible to determine safely, width/height are simply omitted rather
    // than risking a mismatched aspect ratio, which would visually distort
    // the image (default object-fit is "fill", not "contain"/"cover").
    if (!isset($attrs['width']) || !isset($attrs['height'])) {
        $dims = se_img_natural_dimensions($src, $params['widths'] ?? '', $params['ratio'] ?? '');
        if ($dims !== null) {
            $attrs['width'] = $attrs['width'] ?? $dims[0];
            $attrs['height'] = $attrs['height'] ?? $dims[1];
        }
    }

    $html = '<img';
    foreach ($attrs as $name => $value) {
        $html .= ' ' . $name . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '"';
    }
    $html .= '>';

    return $html;
}

/**
 * Compute a [width, height] pair for the <img> width/height attributes that
 * is guaranteed to reflect the aspect ratio actually served, or null if
 * that can't be determined safely. The absolute numbers only matter for
 * their ratio (CSS height:auto scales the real rendered size) - this
 * deliberately never returns a guess that risks a mismatched, visually
 * distorting aspect ratio.
 */
function se_img_natural_dimensions($src, $widths, $ratio)
{
    $maxWidth = 0;
    if ($widths !== '') {
        $parsed = array_filter(array_map('intval', explode(',', $widths)), fn($w) => $w > 0);
        if (!empty($parsed)) {
            $maxWidth = max($parsed);
        }
    }

    if (preg_match('/^(\d+(?:\.\d+)?):(\d+(?:\.\d+)?)$/', (string) $ratio, $m) && (float) $m[2] > 0) {
        // A crop ratio was requested - that's what will actually be served,
        // regardless of the original file's own proportions.
        $ratioValue = (float) $m[1] / (float) $m[2];
        $width = $maxWidth > 0 ? $maxWidth : 1600;
        return [$width, (int) round($width / $ratioValue)];
    }

    // No crop ratio - the served image preserves the original file's own
    // proportions, so read those directly. Only works for local uploads
    // (/images/...), the same convention {img} assumes elsewhere.
    if (!str_starts_with($src, '/images/')) {
        return null;
    }
    $relative = substr($src, strlen('/images/'));
    $resolved = se_resolve_within(SE_PUBLIC . '/assets/images', $relative);
    if ($resolved === false || !is_file($resolved)) {
        return null;
    }
    $size = @getimagesize($resolved);
    if ($size === false || $size[0] <= 0 || $size[1] <= 0) {
        return null;
    }

    if ($maxWidth > 0) {
        // Scale the real file's ratio to the largest requested srcset
        // width, so the declared box matches the largest served variant.
        return [$maxWidth, (int) round($maxWidth * $size[1] / $size[0])];
    }

    return [$size[0], $size[1]];
}
