<?php

// app/hooks-map-helper.php

/**
 * Load the global hooks map from app/hooks-map.php.
 */
function se_get_hooks_map(): array
{
    static $map = null;

    if ($map === null) {
        $file = SE_ROOT . 'app/hooks-map.php';
        $map = is_file($file) ? include $file : [];
    }

    return $map;
}

/**
 * Get backend hooks, optionally filtered by name prefix.
 *
 * Examples:
 *   se_get_backend_hooks();          // all backend hooks
 *   se_get_backend_hooks('page.');   // all hooks starting with "page."
 *   se_get_backend_hooks('product.');// all product-related hooks
 */
function se_get_backend_hooks(string $prefix = ''): array
{
    $map = se_get_hooks_map();
    $backend = $map['backend'] ?? [];

    if ($prefix === '') {
        return $backend;
    }

    $filtered = [];
    foreach ($backend as $name => $info) {
        if (str_starts_with($name, $prefix)) {
            $filtered[$name] = $info;
        }
    }

    return $filtered;
}
