<?php
// app/hooks-meta.php

$SE_HOOK_META = [
    'backend' => [],
    'frontend'=> [],
];

/**
 * Register metadata for backend hooks of a plugin.
 *
 * Expected $hooks format:
 * [
 *   'page.updated' => [
 *       [
 *           'label'       => '...',
 *           'description' => '...',
 *           'category'    => '...',
 *       ],
 *       [
 *           'label'       => '...',
 *           'description' => '...',
 *           'category'    => '...',
 *       ],
 *   ],
 *   'page.before_update' => [
 *       [
 *           'label'       => '...',
 *           'description' => '...',
 *           'category'    => '...',
 *       ],
 *   ],
 * ]
 */
function se_register_backend_hook_meta(string $pluginName, array $hooks): void
{
    global $SE_HOOK_META;

    foreach ($hooks as $hookName => $entries) {
        // Normalize single entry to array of entries
        if (isset($entries['label'])) {
            $entries = [$entries];
        }

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $SE_HOOK_META['backend'][$hookName][] = array_merge($entry, [
                'plugin' => $pluginName,
            ]);
        }
    }
}

/**
 * Get metadata for backend hooks, optionally filtered by hook name prefix.
 */
function se_get_backend_hook_meta(string $prefix = ''): array
{
    global $SE_HOOK_META;

    // All backend hook meta entries
    $all = $SE_HOOK_META['backend'] ?? [];

    // No filter → return everything
    if ($prefix === '') {
        return $all;
    }

    $filtered = [];

    foreach ($all as $hookName => $entries) {
        // Skip invalid structures
        if (!is_array($entries)) {
            continue;
        }

        // Exact match or prefix match
        if ($hookName === $prefix || strpos($hookName, $prefix) === 0) {
            $filtered[$hookName] = $entries;
        }
    }

    return $filtered;
}

