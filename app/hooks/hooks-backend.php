<?php

// Backend hook wrappers

require_once __DIR__ . '/hooks.php';

/**
 * Register a backend hook callback.
 */
function se_add_backend_hook(string $hookName, callable $callback): void
{
    global $SE_BACKEND_HOOKS;

    if (!isset($SE_BACKEND_HOOKS[$hookName])) {
        $SE_BACKEND_HOOKS[$hookName] = [];
    }

    $SE_BACKEND_HOOKS[$hookName][] = $callback;
}

/**
 * Get registered backend hook callbacks (optionally filtered by prefix).
 */
function se_get_backend_hook_callbacks(string $prefix = ''): array
{
    global $SE_BACKEND_HOOKS;

    $all = $SE_BACKEND_HOOKS ?? [];

    if ($prefix === '') {
        return $all;
    }

    $filtered = [];
    foreach ($all as $hookName => $callbacks) {
        if (str_starts_with($hookName, $prefix)) {
            $filtered[$hookName] = $callbacks;
        }
    }

    return $filtered;
}


/**
 * Execute only selected callbacks for a backend action hook.
 *
 * @param string $hookName   The backend hook name, e.g. 'page.updated'
 * @param array  $context    Context passed to each callback
 */
function se_do_backend_hook_selected(string $hookName, array $selected, array $context): void
{
    // Get all registered callbacks for this hook
    $hooks = se_get_backend_hook_callbacks($hookName);

    // Debug
    // error_log('Callbacks for ' . $hookName . ': ' . print_r($hooks, true));

    if (empty($hooks[$hookName]) || !is_array($hooks[$hookName])) {
        return;
    }

    foreach ($hooks[$hookName] as $index => $callback) {
        if (empty($selected[$index])) {
            continue;
        }

        call_user_func($callback, $context);
    }
}


