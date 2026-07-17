---
title: Hooks
description: Hooks
btn: Hooks
group: developer
priority: 200
---

# Manipulate contents with hooks

There are two types of hooks.
One is for the backend. These hooks are always available
once a plugin with the corresponding functions has been installed.
Hooks for the frontend are only available if the plugin
has also been activated.

Two directories are required in the plugins:

`plugins/{plugin}/hooks-backend/` for the backend and
`plugins/{plugin}/hooks-frontend/` for the frontend.

## Available hooks

This is the full list of hooks SwiftyEdit currently fires, as defined in
`app/hooks/hooks-map.php` - that file remains the definitive source if it and this list ever
drift apart, but as of this writing they match.

### Backend hooks

All backend hooks are `action` hooks (registered via `se_add_backend_hook`, see below) - they
run code but don't transform a value.

| Hook              | Context                                    | Fires when...                    |
|--------------------|----------------------------------------------|--------------------------------------|
| `page.updated`      | `page_id`, `data`, `changes`, `user_id`       | A page has been saved in the ACP.    |
| `product.updated`   | `product_id`, `data`, `changes`, `user_id`    | A product has been saved in the ACP. |
| `product.deleted`   | `product_id`, `user_id`                        | A product has been deleted in the ACP. |
| `user.created`      | `user_id`, `data`, `created_by`                | A user account has been created.      |
| `user.updated`      | `user_id`, `data`, `changes`, `updated_by`     | A user account has been saved.        |
| `user.deleted`      | `user_id`, `deleted_by`                        | A user account has been deleted.      |

### Frontend hooks

The frontend hooks (`product.display.before`, `product.display.actions`, `product.display.after`,
`page.display.after`) are documented together with the template variables and rendering
conventions they affect - see [Hooks](09-01-00-themes.md#hooks) in the Themes chapter.

## Example backend hook

The plugin's hooks are defined in the meta.php file.
This is necessary so that descriptions can be specified for the individual actions.
The hooks are displayed in the respective tabs and can be activated as needed.

```php
// plugins/{plugin}/hooks-backend/meta.php
return [
    'page.updated' => [
        [
            'label'       => 'Replace umlauts in page title',
            'description' => 'Replace all umlauts in page title.',
            'category'    => 'Filter',
        ]
    ],
    'product.updated' => [
        [
            'label'       => 'Replace umlauts in product title',
            'description' => 'Replace all umlauts in page title.',
            'category'    => 'Filter',
        ],
    ]
];
```
The `meta.php` must return the array via `return [...]`. SwiftyEdit loads the file
and passes it to `se_register_backend_hook_meta($plugin, $hooks)`.

The hooks themselves are stored in a separate file:

```php
// plugins/{plugin}/hooks-backend/page-updated.php
se_add_backend_hook('page.updated', function (array $context): void {
    // run hook code here
    // $context['data']['page_title']
});
```

```php
// plugins/{plugin}/hooks-backend/product-updated.php
se_add_backend_hook('product.updated', function (array $context): void {
    // run hook code here
});
```

__Careful with the order:__ when an admin enables individual hook actions in a tab, SwiftyEdit
doesn't remember *which* action was checked by name - it remembers its __position__ in the list
(`se_do_backend_hook_selected()` matches enabled checkboxes against registered callbacks by
array index, not by label). This means the `se_add_backend_hook(...)` calls for a given hook
name must be registered in the exact same order as their entries appear in that hook's array in
`meta.php` - if you reorder one without the other, an admin's existing checkbox choices will
silently apply to the wrong action after your plugin updates.

## Frontend example

```php
// plugins/{plugin}/hooks-frontend/product-display.php
se_add_frontend_hook('product.display.before', function (array $product, array $context): array {
    $product['title'] = strtoupper($product['title']);
    return $product;
});
```

