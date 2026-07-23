<?php

// app/hooks-global.php

// Global hooks fire the same event regardless of whether it was triggered
// from a frontend or a backend request (e.g. an order can be marked as paid
// either by an admin in the ACP or by a payment plugin from the frontend).
// Callbacks live in plugins/<name>/hooks-global/ and are loaded on both
// request lifecycles, so registration doesn't depend on where the event
// happens to fire from.

require_once __DIR__ . '/hooks.php';

/**
 * Register a global hook callback.
 */
function se_add_global_hook(string $hookName, callable $callback): void
{
    se_add_hook('global', $hookName, $callback);
}

/**
 * Execute a global action hook.
 */
function se_do_global_hook(string $hookName, array $context = []): void
{
    se_do_hook('global', $hookName, $context);
}

/**
 * Apply a global filter hook.
 */
function se_apply_global_filters(string $hookName, $value, array $context = [])
{
    return se_apply_filters('global', $hookName, $value, $context);
}
