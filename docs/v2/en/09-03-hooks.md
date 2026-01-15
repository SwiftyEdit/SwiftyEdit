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

All available backend/frontend hooks can be found in the file
`app/hooks/hooks-map.php`

## Frontend example

```php
// plugins/{plugin}/hooks-frontend/product-display.php
se_add_frontend_hook('product.display.before', function (array $product, array $context): array {
    $product['title'] = strtoupper($product['title']);
    return $product;
});
```

