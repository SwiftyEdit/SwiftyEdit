---
title: Hooks
description: Hooks
btn: Hooks
group: developer
priority: 200
---

# Inhalte mit Hooks manipulieren

Es gibt drei Arten von Hooks.
Einmal für das Backend. Diese Hooks sind immer verfügbar, 
sobald ein Plugin mit entsprechenden Funktionen installiert wurde.
Hooks für das Frontend sind nur dann verfügbar, wenn das Plugin 
auch aktiviert wurde.
Globale Hooks feuern unabhängig davon, ob die auslösende Aktion im Backend
oder im Frontend passiert ist - etwa wenn eine Bestellung sowohl manuell
im ACP als auch automatisch durch ein Zahlungsplugin als bezahlt markiert
werden kann. Da ACP-Requests und Frontend-Requests vollständig getrennte
Ablaufumgebungen mit eigenem Bootstrap sind, werden globale Hook-Callbacks
in beiden geladen, damit ein Plugin nicht zweimal (einmal pro Kontext)
registriert werden muss.

In den Plugins werden hier drei Verzeichnisse benötigt:

`plugins/{plugin}/hooks-backend/` für das Backend,
`plugins/{plugin}/hooks-frontend/` für das Frontend und
`plugins/{plugin}/hooks-global/` für Hooks, die sowohl vom Backend als
auch vom Frontend ausgelöst werden können.

## Verfügbare Hooks

Das ist die vollständige Liste der Hooks, die SwiftyEdit aktuell feuert, wie in
`app/hooks/hooks-map.php` definiert - diese Datei bleibt die maßgebliche Quelle, falls sie und
diese Liste jemals auseinanderlaufen sollten, zum Zeitpunkt dieses Texts stimmen sie überein.

### Backend-Hooks

Alle Backend-Hooks sind `action`-Hooks (registriert über `se_add_backend_hook`, siehe unten) -
sie führen Code aus, verändern aber keinen Wert.

| Hook               | Context                                        | Feuert, wenn...                          |
|---------------------|---------------------------------------------------|------------------------------------------------|
| `page.updated`       | `page_id`, `data`, `changes`, `user_id`             | Eine Seite im ACP gespeichert wurde.            |
| `product.updated`    | `product_id`, `data`, `changes`, `user_id`          | Ein Produkt im ACP gespeichert wurde.           |
| `product.deleted`    | `product_id`, `user_id`                              | Ein Produkt im ACP gelöscht wurde.               |
| `user.created`       | `user_id`, `data`, `created_by`                      | Ein Benutzerkonto angelegt wurde.                |
| `user.updated`       | `user_id`, `data`, `changes`, `updated_by`           | Ein Benutzerkonto gespeichert wurde.             |
| `user.deleted`       | `user_id`, `deleted_by`                              | Ein Benutzerkonto gelöscht wurde.                |

### Frontend-Hooks

Die Frontend-Hooks (`product.display.before`, `product.display.actions`, `product.display.after`,
`page.display.after`) sind zusammen mit den Template-Variablen und Rendering-Konventionen, die
sie betreffen, dokumentiert - siehe [Hooks](09-01-00-themes.md#hooks) im Themes-Kapitel.

### Globale Hooks

Globale Hooks sind ebenfalls `action`-Hooks (registriert über `se_add_global_hook`, siehe unten).
Anders als bei Backend-Hooks gibt es hier keine `meta.php` und keine Checkbox-Auswahl im ACP -
jeder registrierte Callback wird bei jedem Feuern des Hooks immer ausgeführt, ganz wie bei
Frontend-Hooks.

| Hook         | Context                                | Feuert, wenn...                                    |
|--------------|-----------------------------------------|-----------------------------------------------------|
| `order.paid` | `order_id`, `order`, `triggered_by`     | Eine Bestellung als bezahlt markiert wurde - manuell im ACP (`triggered_by` = `admin`) oder automatisch durch ein Zahlungsplugin (`triggered_by` = z.B. `se_paypal-pay`). |

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
Die `meta.php` muss das Array per `return [...]` zurückgeben. SwiftyEdit lädt die Datei
und übergibt sie an `se_register_backend_hook_meta($plugin, $hooks)`.

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

__Vorsicht bei der Reihenfolge:__ Wenn ein Administrator einzelne Hook-Aktionen in einem Tab
aktiviert, merkt sich SwiftyEdit nicht, *welche* Aktion namentlich angehakt wurde, sondern nur
deren __Position__ in der Liste (`se_do_backend_hook_selected()` gleicht aktivierte Checkboxen
anhand des Array-Index mit den registrierten Callbacks ab, nicht anhand der Bezeichnung). Das
bedeutet: Die `se_add_backend_hook(...)`-Aufrufe für einen Hook-Namen müssen in exakt derselben
Reihenfolge registriert werden wie die zugehörigen Einträge im Array dieses Hooks in `meta.php`
- vertauschst du nur eine der beiden Reihenfolgen, greifen die bestehenden Checkbox-Einstellungen
eines Admins nach einem Plugin-Update still und leise bei der falschen Aktion.

## Beispiel Frontend

```php
// plugins/{plugin}/hooks-frontend/product-display.php
se_add_frontend_hook('product.display.before', function (array $product, array $context): array {
    $product['title'] = strtoupper($product['title']);
    return $product;
});
```

## Beispiel globaler Hook

Globale Hooks benötigen keine `meta.php` und keine Checkbox-Auswahl - jeder registrierte Callback
läuft bei jedem Feuern des Hooks, unabhängig davon, ob der Auslöser im Backend oder im Frontend
lag.

```php
// plugins/{plugin}/hooks-global/order-paid.php
se_add_global_hook('order.paid', function (array $context): void {
    // run hook code here
    // $context['order_id'], $context['order'], $context['triggered_by']
});
```

