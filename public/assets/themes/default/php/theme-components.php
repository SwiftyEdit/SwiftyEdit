<?php

/**
 * Auto-discovers this theme's optional components from dist/ instead of a
 * hardcoded list, so a new component (drop a file in src/js/components/,
 * rebuild) needs no changes anywhere else: every dist/<id>.css and/or
 * dist/<id>.js file is one selectable component named <id> (a component
 * doesn't need both - a CSS-only or JS-only component is fine), except the
 * reserved names below that are not optional components.
 *
 * Included directly (not autoloaded) by both php/page-values.php (ACP) and
 * php/options.php (frontend), since those run in separate contexts.
 */

const SE_THEME_RESERVED_DIST_NAMES = ['core', 'editor', 'tinyMCE_config'];

function se_theme_component_ids(string $theme_dist_dir): array {
    $files = array_merge(
        glob($theme_dist_dir.'/*.css') ?: [],
        glob($theme_dist_dir.'/*.js') ?: []
    );

    $ids = [];
    foreach ($files as $file) {
        $id = pathinfo($file, PATHINFO_FILENAME);
        if (in_array($id, SE_THEME_RESERVED_DIST_NAMES, true)) {
            continue;
        }
        $ids[$id] = true;
    }

    $ids = array_keys($ids);
    sort($ids);
    return $ids;
}

function se_theme_component_has_css(string $theme_dist_dir, string $id): bool {
    return is_file($theme_dist_dir.'/'.$id.'.css');
}

function se_theme_component_has_js(string $theme_dist_dir, string $id): bool {
    $js_file = $theme_dist_dir.'/'.$id.'.js';
    return is_file($js_file) && filesize($js_file) > 0;
}
