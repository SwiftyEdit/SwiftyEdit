---
title: Themes - Templates
description: The template system
btn: Templates
group: developer
priority: 200
---

# Die Templates (.tpl Dateien)

Einige Templates werden direkt aus SwiftyEdit inkludiert.
Diese Templates sollten sich also im Ordner `public/assets/themes/{theme}/templates/` befinden.

## Vererbung vom Default-Theme {#vererbung-vom-default-theme}

Das verdient einen eigenen Abschnitt, denn es ist eines der nützlichsten Dinge am
Template-System: __ein Theme muss nur die `.tpl`-Dateien enthalten, die es tatsächlich ändern
will.__ Smartys Template-Verzeichnis wird auf `[Dein-Theme/templates/, default/templates/]`
gesetzt, in dieser Reihenfolge (`app/smarty.php`), und jeder `$smarty->fetch(...)`-Aufruf im
gesamten Code übergibt einen nackten Dateinamen, nie einen theme-spezifischen Pfad - die Suche
über diese beiden Verzeichnisse läuft in Smarty selbst ab
(`vendor/smarty/smarty/src/Resource/FilePlugin.php`), für jedes Template auf dieselbe Weise. Ein
"Teil-Theme", das z. B. nur `products-list.tpl` und `products-display.tpl` mitbringt, um dem Shop
ein anderes Layout zu geben, ist also ein vollwertig unterstützter Ansatz und kein Workaround:
jede andere Seite (Konto, Blog, Events, Checkout, Listen, ...) wird weiterhin korrekt gerendert,
unverändert vom `default`-Theme geerbt.

Ein paar ACP-Editor-Assets fallen auf dieselbe Weise zurück, mit leicht abweichenden Regeln
(`acp/core/editors.php`):

* `dist/editor.css` fällt auf die Kopie von `default` zurück, falls Dein Theme keine mitbringt.
* `php/tinymce-images.php` / `php/tinymce-links.php` fallen genauso auf `default` zurück.
* `dist/tinyMCE_config.js` fällt __nicht__ zurück - bringt Dein Theme keine mit, verwendet
  tinyMCE einfach seine eigene eingebaute Konfiguration statt der von `default`.

Was gar nicht zurückfällt: Deine wichtigsten Frontend-Assets. `head.tpl` verlinkt
`dist/core.css` und `dist/core.js` mit `{$se_template}` fest im Pfad, ohne
Existenzprüfung - fehlen diese beiden Dateien in Deinem Theme, sind die Links schlicht kaputt,
sie werden nicht stillschweigend durch die Versionen von `default` ersetzt. Jedes Theme braucht
also zwingend sein eigenes `dist/core.css` und `dist/core.js`, auch wenn eine der beiden
Dateien trivial ausfällt (z. B. nur ein `@import` des `default`-Stylesheets). Die optionalen
Komponenten-Bundles von `default` (`shop.css` / `shop.js`, `events.css`, `comments.css`,
`posts.css`) werden nur bedingt verlinkt und sind eine Besonderheit dieses Themes - Dein eigenes
Theme braucht keine Entsprechungen dafür, siehe
[Theme-Assets bauen](09-01-00-themes.md#theme-assets-bauen) und
[Optionale Theme-Komponenten](09-01-00-themes.md#optionale-theme-komponenten).

## Übersicht der Template-Dateien

Diese Liste enthält alle `.tpl`-Dateien, die das `default`-Theme mitbringt, gruppiert nach
Bereich, mit einer kurzen Beschreibung, wer sie rendert und (wo hilfreich) welche Variablen
verwendet werden. Für die vollständige, maßgebliche Liste der Variablen eines Templates siehe
[Template-Variablen](#template-variablen) weiter unten.

### Layout & global

| Datei                     | Zweck                                                                                    |
|----------------------------|----------------------------------------------------------------------------------------------|
| `index.tpl`                  | Das erste Template, das für jede Anfrage geladen wird; bindet `head.tpl` und über das Layout alles Weitere ein. |
| `layout_default.tpl`         | Das Standard-Seitenlayout (Header, Inhaltsspalte, Sidebar, Footer). Pro Seite über `page_template_layout` wählbar. |
| `header.tpl`                  | Seiten-Header: Logo, Theme-Switcher, obere Aktionen.                                          |
| `navigation.tpl`              | Das Hauptnavigationsmenü.                                                                     |
| `content.tpl`                 | Rendert je nachdem `$msg_content`, `$products_content` oder `$page_content`, plus Kommentare. |
| `sidebar.tpl`                 | Bindet `sidebar-categories.tpl`, `sidebar-filter.tpl` und `sidebar-toc.tpl` sowie das Sidebar-Snippet ein. |
| `sidebar-categories.tpl`      | Kategorie-Navigation in der Sidebar.                                                          |
| `sidebar-filter.tpl`          | Shop-Filterformular in der Sidebar (Preisspanne, Optionen, Tags, ...).                        |
| `sidebar-toc.tpl`             | Inhaltsverzeichnis-Block für Seiten mit Unterseiten (`$arr_submenue`).                         |
| `footer.tpl`                  | Seiten-Footer, inkl. Breadcrumb-Navigation.                                                    |
| `head.tpl`                    | `<title>`, Meta-Tags, Canonical-URL, `<base href>` - eingebunden im `<head>` von `index.tpl`. |
| `socialmedia.tpl`             | Rendert die konfigurierten Social-Media-Links.                                                |
| `status_message.tpl`          | System-/Statusmeldungen (z. B. "erfolgreich gespeichert").                                    |
| `statusbox.tpl`                | Links zum ACP (Administratoren) bzw. zum Profil (angemeldete Benutzer).                       |
| `admin_helpers.tpl`            | Schnellzugriffs-Links für angemeldete Administratoren (diese Seite bearbeiten usw.).           |
| `maintenance.tpl`              | Wird seitenweit angezeigt, solange der Wartungsmodus aktiv ist.                               |
| `sitemap.tpl`                   | Die XML-/HTML-Sitemap-Ausgabe.                                                                |
| `page_psw_input.tpl`           | Passwortabfrage für passwortgeschützte Seiten.                                                |

### Konto & Anmeldung

| Datei                            | Zweck                                              |
|------------------------------------|-------------------------------------------------------|
| `loginbox.tpl`                       | Anmeldeformular (kann im ACP deaktiviert werden).    |
| `registerform.tpl`                   | Registrierungsformular für neue Benutzer.            |
| `password.tpl`                       | Formular "Passwort zurücksetzen".                    |
| `profile_main.tpl`                   | Haupt-Profilseite (Kontaktdaten, bindet die folgenden Dateien ein). |
| `profile/address.tpl`                | Formular für die Lieferadresse.                      |
| `profile/address-ba.tpl`             | Formular für die Rechnungsadresse.                   |
| `profile/address-sa.tpl`             | Formular für die Versandadresse.                     |
| `profile/address-mail.tpl`           | E-Mail-Adresse des Kontos ändern/bestätigen.         |
| `profile/change-mail.tpl`            | Bestätigungsablauf für die E-Mail-Änderung.          |
| `profile/change-password.tpl`        | Formular zum Ändern des Passworts.                   |
| `profile/avatar.tpl`                  | Avatar-Anzeige.                                       |
| `profile/avatar-form.tpl`             | Formular zum Hochladen des Avatars.                   |

### Suche

| Datei                | Zweck                             |
|-----------------------|--------------------------------------|
| `search.tpl`            | Das Sucheingabeformular.            |
| `searchresults.tpl`     | Auflistung der Suchergebnisse.      |

### Blog (Posts)

| Datei                   | Zweck                                                    |
|---------------------------|-------------------------------------------------------------|
| `posts-list.tpl`            | Beitragsliste (alle Beitragstypen: Nachricht, Bild, Galerie, Video, Link, Download). |
| `posts-display.tpl`         | Einzelansicht eines Beitrags.                              |

### Shop

| Datei                        | Zweck                                                             |
|--------------------------------|-----------------------------------------------------------------------|
| `products-list.tpl`             | Produktliste/Katalogseite.                                            |
| `products-display.tpl`          | Einzelansicht eines Produkts (rendert auch `$product_plugin_actions`, siehe [Hooks](09-01-00-themes.md#hooks)). |
| `shopping_cart.tpl`              | Warenkorb-Seite (Wrapper).                                             |
| `shopping_cart_form.tpl`         | Das Checkout-/Anfrageformular des Warenkorbs.                          |
| `shopping_cart_table.tpl`        | Die Positionstabelle innerhalb des Warenkorbs.                        |
| `orders.tpl`                      | Übersicht "Meine Bestellungen".                                        |
| `orders-list.tpl`                 | Die paginierte Bestellliste (HTMX-Partial).                            |
| `order-item.tpl`                  | Einzelansicht einer Bestellung (Modal).                                |
| `order-withdrawal.tpl`            | Das Formular zum Bestellwiderruf (EU-Widerrufsrecht).                  |

### Listen (Wishlist)

Siehe [Listen](05-00-shop.md#listen) für die kundenseitige Funktion.

| Datei                             | Zweck                                                             |
|--------------------------------------|-------------------------------------------------------------------------|
| `wishlist_overview.tpl`               | Übersicht "Meine Listen" für einen angemeldeten Kunden.                |
| `wishlist_detail.tpl`                 | Inhalt einer einzelnen Liste (Eigentümer-Ansicht), inkl. Drag-Reorder. |
| `wishlist_public.tpl`                 | Öffentliche, schreibgeschützte Ansicht einer geteilten Liste (mit "In den Warenkorb"). |
| `wishlist_picker.tpl`                  | Das "Zur Liste hinzufügen"-Auswahl-Modal, geöffnet von einer Produktseite. |
| `wishlist_list_column.tpl`             | Eine Listenzeile innerhalb des Pickers/der Übersicht.                  |

### Events

| Datei                  | Zweck                          |
|--------------------------|------------------------------------|
| `events-list.tpl`          | Event-Liste.                      |
| `events-display.tpl`       | Einzelansicht eines Events.       |

### Kommentare

| Datei                  | Zweck                                        |
|--------------------------|---------------------------------------------------|
| `comment_entry.tpl`        | Ein einzelner gerenderter Kommentar (inkl. Antworten). |
| `comment_form.tpl`         | Das Formular zum Absenden eines Kommentars.        |

### Snippets

| Datei                    | Zweck                                                            |
|----------------------------|-----------------------------------------------------------------------|
| `snippet.tpl`                | Ausgabe eines reinen Text-Snippets.                                   |
| `snippet_card.tpl`           | Snippet als Karte gerendert (Titel + Text).                           |
| `snippet_card_img.tpl`       | Snippet als Karte mit Bild und optionalem Link-Button gerendert.      |

### Sonstiges / Hilfsdateien

| Datei                         | Zweck                                          |
|----------------------------------|-----------------------------------------------------|
| `404.tpl`                        | Wird beim HTTP-Statuscode 404 angezeigt (Seite nicht gefunden). |
| `download.tpl`                    | Detailseite für Download-/Anhang-Beiträge.          |
| `image.tpl`                        | Detailseite für einen einzelnen Bild-Beitrag.        |
| `alert/alert-danger.tpl`           | Bootstrap-"danger"-Hinweisbox.                        |
| `alert/alert-info.tpl`              | Bootstrap-"info"-Hinweisbox.                          |
| `alert/alert-success.tpl`           | Bootstrap-"success"-Hinweisbox.                       |
| `alert/alert-warning.tpl`           | Bootstrap-"warning"-Hinweisbox.                       |

## Mail-Templates {#mail-templates}

`templates-mail/` ist ein eigener, einfacherer Template-Mechanismus für ausgehende E-Mails
(Bestellbestätigungen, Bestellwiderruf-Anfragen, Passwort-Reset, ...). Er basiert auf reinem
PHP `str_replace()` in `se_build_html_file()` (`app/functions/functions.php`) - __nicht__ auf
Smarty. Verwende in diesen Dateien keine Smarty-Syntax (`{$var}`, `{if}`, `{foreach}`, ...),
sondern nur die unten aufgeführten wörtlichen Platzhalter.

* `mail.tpl` - der äußere HTML-Wrapper (`<html>`, Inline-Styles, Header/Footer), der für jede
  Mail verwendet wird, sofern kein anderes `tpl` angefordert wird. Platzhalter: `{styles}`
  (Inhalt von `styles.css`), `{mail_title}`, `{mail_preheader}`, `{mail_salutation}`,
  `{mail_body}`, `{mail_subject}`, `{mail_footer}`.
* `styles.css` - wird in `{styles}` innerhalb von `mail.tpl` eingefügt.
* Body-Templates - der eigentliche Inhalt einer bestimmten Mail, wird in den Platzhalter
  `{mail_body}` von `mail.tpl` eingesetzt. Jede Datei definiert ihre eigenen zusätzlichen
  Platzhalter, die vom aufrufenden Code befüllt werden (z. B. verwendet
  `order-withdrawal-request.tpl` `{lang_label_order_nbr}`, `{order_nbr}`, `{order_mail}`,
  `{order_reason}`, ...):
    * `order-withdrawal-request.tpl`
    * `send-order-request.tpl`
    * `send-order-status.tpl`

Wenn Du aus PHP heraus eine neue Transaktions-Mail hinzufügst, übergibst Du Deinen eigenen
Body-Template-Dateinamen und die Platzhalterwerte über das `$data`-Array an
`se_build_html_file()` - die vollständige Parameterliste steht im Docblock in
`app/functions/functions.php`.

## Template-Variablen {#template-variablen}

Es gibt keine einzelne, globale Referenz "aller Variablen in allen Templates" - jedes Template
erhält nur das, was der für diese Seite zuständige PHP-Handler unmittelbar vor dem Rendern über
`$smarty->assign(...)` übergibt. Dafür musst Du aber nicht selbst suchen - Smarty bringt genau
dafür eine Debug-Konsole mit.

Der schnellste Weg: Setze `{debug}` irgendwo in eine `.tpl`-Datei (nur vorübergehend, zum
Nachschauen) und lade die Seite. Es öffnet sich eine Liste aller Variablen, die im Scope des
aktuellen Templates zugewiesen sind - ganz ohne PHP anzufassen. Für eine seitenweite Übersicht
statt nur eines einzelnen Templates kannst Du Smartys Debug-Konsole für den gesamten Request
aktivieren, indem Du in `app/smarty.php` `$smarty->debugging = true;` setzt - oder, ohne eine
Core-Datei dauerhaft zu verändern, dort einmalig `$smarty->debugging_ctrl = 'URL';` ergänzt.
Damit lässt sie sich anschließend pro Aufruf über `?SMARTY_DEBUG` in der URL aktivieren. Entferne
bzw. mache diese Einstellungen vor einem Deployment wieder rückgängig - sie sind nur für die
lokale Entwicklung gedacht.

Dafür gibt es bewusst keine Einstellung im ACP: Die Debug-Konsole gibt jede zugewiesene Variable
unverändert aus, darunter können Session- oder Datenbankdaten sein - das darf also nicht
versehentlich auf einer Live-Seite aktivierbar sein. Die direkte Änderung in `app/smarty.php`
hält es bewusst als lokale Einzelfall-Entscheidung.

Wenn Du lieber direkt im Quellcode nachsiehst (z. B. weil Du ändern willst, was zugewiesen wird,
nicht nur nachschauen willst), durchsuche den zuständigen Handler nach `smarty->assign`-Aufrufen.
Ein paar Startpunkte:

| Template(s)                                        | Zugewiesen von                          |
|-------------------------------------------------------|---------------------------------------------|
| `products-list.tpl`                                     | `app/handlers/products-list.php`            |
| `products-display.tpl`                                  | `app/handlers/products-display.php`         |
| `posts-list.tpl`                                          | `app/handlers/posts-list.php`               |
| `posts-display.tpl`                                       | `app/handlers/posts-display.php`            |
| `events-list.tpl`                                          | `app/handlers/events-list.php`              |
| `events-display.tpl`                                        | `app/handlers/events-display.php`           |
| `wishlist_overview.tpl` / `wishlist_public.tpl`          | `app/handlers/wishlist.php`                 |
| `orders.tpl` / `orders-list.tpl`                          | `app/handlers/orders.php`                   |
| alles Weitere (Seiteninhalt, Layout-Variablen)            | `app/template-setup.php`                     |

Variablen, die mit dem dritten `$smarty->assign()`-Argument `true` (nocache) zugewiesen werden,
lassen sich auch dann sicher verwenden, wenn für diese Seite Smarty-Caching aktiv ist.

Eine kuratierte Referenz, welche Variablen die einzelnen Templates tatsächlich bekommen -
gruppiert nach Bereich, mit der Bedeutung jedes Werts - gibt es unter
[Template-Variablen](09-01-02-template-variables.md). Das ersetzt die Debug-Konsole oben nicht
(die Referenz deckt nur die am häufigsten angepassten Templates ab und kann veralten), erspart
aber im Regelfall den Umweg über den Handler-Quellcode.
