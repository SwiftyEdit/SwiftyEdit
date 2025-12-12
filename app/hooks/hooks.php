<?php

// app/hooks.php

// Global storage for all hooks
$SE_HOOKS = [
    'frontend' => [],
    'backend' => [],
];

/**
 * Register a callback for a given hook and scope.
 * $scope: 'frontend' or 'backend'
 */
function se_add_hook(string $scope, string $hookName, callable $callback): void
{
    // Ensure global storage exists
    global $SE_HOOKS;

    if (!isset($SE_HOOKS[$scope])) {
        $SE_HOOKS[$scope] = [];
    }

    // Append callback to hook list
    $SE_HOOKS[$scope][$hookName][] = $callback;
}

/**
 * Execute all callbacks for a given hook and scope (action).
 */
function se_do_hook(string $scope, string $hookName, array $context = []): void
{
    global $SE_HOOKS;

    if (empty($SE_HOOKS[$scope][$hookName])) {
        return;
    }

    foreach ($SE_HOOKS[$scope][$hookName] as $callback) {
        $callback($context);
    }
}

/**
 * Apply all filter callbacks for a given hook and scope.
 * $value is passed through all callbacks.
 */
function se_apply_filters(string $scope, string $hookName, $value, array $context = [])
{
    global $SE_HOOKS;

    if (empty($SE_HOOKS[$scope][$hookName])) {
        return $value;
    }

    foreach ($SE_HOOKS[$scope][$hookName] as $callback) {
        $value = $callback($value, $context);
    }

    return $value;
}
