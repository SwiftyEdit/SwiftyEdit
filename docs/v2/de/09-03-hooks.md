---
title: Hooks
description: Hooks
btn: Hooks
group: developer
priority: 200
---

# Inhalte mit Hooks manipulieren

Es gibt zwei Arten von Hooks.
Einmal für das Backend. Diese Hooks sind immer verfügbar, 
sobald ein Plugin mit entsprechenden Funktionen installiert wurde.
Hooks für das Frontend sind nur dann verfügbar, wenn das Plugin 
auch aktiviert wurde.

In den Plugins werden hier zwei Verzeichnisse benötigt:

`plugins/{plugin}/hooks-backend/` für das Backend und
`plugins/{plugin}/hooks-frontend/` für das Frontend.

## Beispiel Backend-Hook

Die Hooks des Plugins werden in der Datei meta.php definiert.
Dies ist nötig, damit man für die einzelnen Aktionen Beschreibungen angeben kann.
Die Hooks werden in den jeweiligen Tabs angezeigt und können nach Bedarf aktiviert werden.

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
Die Hooks selbst werden in einer eigenen Datei abgelegt:

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

Alle verfügbaren Backend/Frontend-Hooks findest du in der Datei
`app/hooks/hooks-map.php`

## Beispiel Frontend

```php
// plugins/{plugin}/hooks-frontend/product-display.php
se_add_frontend_hook('product.display.before', function (array $product, array $context): array {
    $product['title'] = strtoupper($product['title']);
    return $product;
});
```

