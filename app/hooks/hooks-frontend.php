<?php

// app/hooks-frontend.php

require_once __DIR__ . '/hooks.php';

/**
 * Register a frontend hook callback.
 */
function se_add_frontend_hook(string $hookName, callable $callback): void
{
    se_add_hook('frontend', $hookName, $callback);
}

/**
 * Execute a frontend action hook.
 */
function se_do_frontend_hook(string $hookName, array $context = []): void
{
    se_do_hook('frontend', $hookName, $context);
}

/**
 * Apply a frontend filter hook.
 */
function se_apply_frontend_filters(string $hookName, $value, array $context = [])
{
    return se_apply_filters('frontend', $hookName, $value, $context);
}
