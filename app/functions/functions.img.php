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
    foreach (['alt', 'class', 'id', 'loading', 'width', 'height', 'title'] as $passthrough) {
        if (isset($params[$passthrough]) && $params[$passthrough] !== '') {
            $attrs[$passthrough] = $params[$passthrough];
        }
    }

    $html = '<img';
    foreach ($attrs as $name => $value) {
        $html .= ' ' . $name . '="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '"';
    }
    $html .= '>';

    return $html;
}
