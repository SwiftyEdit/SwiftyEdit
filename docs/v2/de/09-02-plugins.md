---
title: Plugins
description: Plugins erstellen oder installieren
btn: Plugins
group: developer
priority: 200
---

Plugins werden im Verzeichnis `/plugins/` gespeichert.

## Aufbau eines Plugins

Anders als in SwiftyEdit Version 1 müssen alle Plugins einer bestimmten Ordnerstruktur folgen:

- Plugin [d]
  - global [d] (optional)
    - index.php (wird geladen, sobald das Plugin aktiv ist)
    - xhr.php (verarbeitet XHR-Anfragen unter `/xhr/plugins/{plugin}/`)
  - backend [d] (optional)
    - page-values.php / product-values.php / post-values.php (optional, ergänzt Felder
      im "Addons"-Tab des Seiten-/Produkt-/Post-Editors, siehe unten)
  - frontend [d] (optional)
    - index.php (nur für Seiten-Modul-Plugins, ersetzt den Seiteninhalt)
  - hooks-backend [d] (optional)
  - hooks-frontend [d] (optional)
  - lang [d] (optional)
  - data [d] (optional, wird bei Updates nie überschrieben)
  - aftersale.php
  - info.json
  - poster.png (optional)
  - readme.md

### Tipps

- Wenn der Name deines Plugins auf `-pay` endet (z. B. `my-gateway-pay`), wird es automatisch
  als Zahlungs-Plugin erkannt. Dadurch wird die Datei `aftersale.php` zur Pflicht.
- XHR-Anfragen werden über die Datei `global/xhr.php` verarbeitet, die unter
  `/xhr/plugins/{plugin}/` erreichbar ist.

### Aktivierte Plugins

Plugins gelten als aktiv, wenn sie in eine Seite eingebunden oder im Backend
manuell aktiviert wurden.

Ein Plugin muss aktiviert sein, damit es

- XHR-Anfragen im Frontend verarbeiten kann
- Hooks im Frontend ausgeführt werden können
- eigene Felder im "Addons"-Tab des Seiten-/Produkt-/Post-Editors anzeigen kann
  (`{page|product|post}-values.php`, siehe unten)

### Wann werden welche Plugin-Dateien geladen?

Folgende Includes sind möglich:

1. `/plugins/{plugin}/index.php`
2. `/plugins/{plugin}/frontend/index.php`
3. `/plugins/{plugin}/global/index.php`
4. `/plugins/{plugin}/global/xhr.php`
5. `/plugins/{plugin}/backend/{page|product|post}-values.php`
6. `/plugins/{plugin}/endpoint.php`

---

1. Wenn ein Plugin über einen Shortcode eingebunden wird: `[plugin={plugin}]foo=bar[/plugin]`
2. Wenn ein Plugin innerhalb einer Seite eingebunden wird, also im Plugin-Tab aktiviert ist,
   wird der Seiteninhalt im Frontend durch das Plugin ersetzt.
3. Wenn ein Plugin aktiv ist. Das bedeutet, es wurde entweder über den Plugin-Tab
   in eine Seite eingebunden oder manuell aktiviert.
4. Wenn ein Plugin XHR-Anfragen im Frontend verarbeiten soll.
   Das Plugin muss dafür aktiviert sein. Die korrekte Route ist `/xhr/plugins/{plugin}/`
5. Wenn der Seiten-, Produkt- oder Post-Editor seinen "Addons"-Tab rendert - einmal für
   jedes Plugin, das im Backend aktiviert ist (siehe unten).
6. Wenn eine Anfrage `/dispatch.php?p={plugin}&...` trifft (`public/dispatch.php`) - die eine
   Ausnahme von "jede Plugin-Datei läuft innerhalb des vollständigen App-Bootstraps". Dieser
   Einstiegspunkt überspringt `app/app.php` komplett (kein DB-Connect, keine Session, kein
   Smarty/Twig), damit ein Plugin so schnell wie ein statisches Asset antworten kann - nützlich
   für alles, was viele Male pro Seitenaufruf angefragt wird, wie die `srcset`-Varianten des
   mitgelieferten `image-resizer`-Plugins. `mods_check_in()` (`acp/core/functions_addons.php`)
   schreibt `data/cache/bootstrap_endpoints.json`, sobald ein Plugin aktiviert/deaktiviert oder
   eine Seite gespeichert wird - dort stehen alle aktuell aktivierten Plugins (direkt aus
   `se_addons`), die eine `endpoint.php` mitbringen. `dispatch.php` löst einen Plugin-Namen
   ausschließlich gegen diese bereits freigegebene Liste auf, nie gegen die Anfrage selbst - ein
   inaktives oder `endpoint.php`-loses Plugin kann darüber also nie erreichbar werden. Da ein
   aktiviertes Plugin über die obigen Einstiegspunkte ohnehin schon volles
   Code-Ausführungs-Vertrauen bei jeder normalen Anfrage hat, verleiht eine mitgelieferte
   `endpoint.php` nichts Neues - nur einen weiteren, bootstrap-freien Einstiegspunkt in dasselbe
   Vertrauen. Innerhalb von `endpoint.php` stehen nur die `SE_*`-Pfad-Konstanten aus `config.php`
   zur Verfügung - `$db_content`/`$se_settings`/Session/etc. dürfen nicht vorausgesetzt werden.

### Addon-Felder im Seiten-/Produkt-/Post-Editor

Ein Plugin kann eigene Eingabefelder im "Addons"-Tab des Seiten-, Produkt- und
Post-Editors ergänzen, indem es eine dieser Dateien bereitstellt:

- `/plugins/{plugin}/backend/page-values.php`
- `/plugins/{plugin}/backend/product-values.php`
- `/plugins/{plugin}/backend/post-values.php`

Diese Dateien werden nur für Plugins geladen, die **im Backend aktiviert** sind - die
Einbindung in eine Seite allein reicht nicht aus.

Jede Datei erhält ein `$record_data`-Array (die Datenbankzeile des aktuellen Datensatzes)
und muss einen String `$plugin_form_tpl` mit dem HTML für die eigenen Felder setzen:

```php
<?php
// plugins/mein-plugin/backend/page-values.php

$values = json_decode($record_data['addon_string'], true) ?: [];

$plugin_form_tpl = '<div class="mb-1">';
$plugin_form_tpl .= '<label>Mein Feld</label>';
$plugin_form_tpl .= '<input type="text" class="form-control" name="addon_values[mein_feld]" value="'
    . htmlspecialchars($values['mein_feld'] ?? '') . '">';
$plugin_form_tpl .= '</div>';
```

- Benenne deine Eingabefelder `addon_values[key]` für einzelne Werte, oder
  `addon_values[key][]` für Mehrfachauswahl-Felder (Checkboxen, Multi-Select).
- SwiftyEdit präfixt jeden Feldnamen automatisch mit dem Ordnernamen deines Plugins,
  bevor gespeichert wird, und entfernt das Präfix wieder, bevor `$record_data['addon_string']`
  an deine Datei übergeben wird - du musst dich also nicht um Namenskollisionen mit
  anderen Plugins kümmern, die ebenfalls Addon-Felder zum selben Datensatz ergänzen.
- Die übermittelten Werte werden als JSON in der Spalte `addon_string` des Datensatzes
  gespeichert (`se_pages`, `se_products` bzw. `se_posts`).

## Die info.json Datei {#die-infojson-datei}

Jedes Plugin muss eine `info.json` Datei in seinem Stammverzeichnis haben. Diese Datei
enthält Metadaten über das Plugin und wird von SwiftyEdit verwendet, um Plugin-Informationen
im Backend anzuzeigen und Updates zu verwalten.

### Grundstruktur
```json
{
  "addon": {
    "id": "mein-plugin",
    "type": "plugin",
    "name": "Mein Plugin",
    "version": "1.0",
    "build": 1,
    "author": "Dein Name",
    "description": "Eine kurze Beschreibung des Plugins",
    "update_url": "https://dein-server.de/plugins/mein-plugin/info.json"
  },
  "versions": [
    {
      "version": "1.0",
      "build": 1,
      "requires_build": "25-145",
      "download_url": "https://dein-server.de/plugins/mein-plugin/mein-plugin-1.zip"
    }
  ],
  "navigation": [
    {
      "text": "nav_overview",
      "file": "start"
    }
  ]
}
```

> **Hinweis:** Die meisten Felder sind optional. In der Praxis kommen die mitgelieferten
> Plugins mit einem minimalen Satz aus: `name`, `version`, `author`, `description` und
> `navigation`. Die Felder `id`, `type`, `build`, `versions[]`, `update_url` und
> `requires_build` werden nur für die automatische Update-Prüfung benötigt.

### Felder

#### addon

| Feld | Pflicht | Beschreibung |
|---|---|---|
| `id` | empfohlen | Eindeutiger Bezeichner des Plugins. Wird als Ordnername verwendet. Wenn nicht angegeben, wird er aus der URL abgeleitet. |
| `type` | ja | Muss `plugin` sein (oder `editor`, siehe unten - `theme` für die [info.json eines Themes](09-01-00-themes.md#die-infojson-datei)) |
| `name` | ja | Anzeigename des Plugins |
| `version` | ja | Aktuelle Version, menschenlesbar (z.B. `1.0`) |
| `build` | ja | Aktuelle Build-Nummer. Wird von SwiftyEdit zur Update-Erkennung verwendet. |
| `author` | ja | Name des Autors |
| `description` | ja | Kurze Beschreibung des Plugins |
| `update_url` | optional | URL zur externen `info.json`. Erforderlich für automatische Update-Prüfungen. |

#### versions

Eine Liste aller verfügbaren Versionen, sortiert von neu nach alt. SwiftyEdit wählt
automatisch die neueste Version aus, die mit dem installierten SwiftyEdit-Build kompatibel ist.

| Feld | Pflicht | Beschreibung |
|---|---|---|
| `version` | ja | Versionsnummer, menschenlesbar |
| `build` | ja | Build-Nummer dieser Version |
| `requires_build` | ja | Mindest-SwiftyEdit-Build (z.B. `25-145`) |
| `download_url` | ja | URL zur ZIP-Datei dieser Version |

#### navigation

Definiert die Navigationspunkte im Plugin-Backend. Jeder Eintrag erstellt einen
Menüpunkt, der die entsprechende PHP-Datei aus dem `/backend/` Verzeichnis lädt.

| Feld | Pflicht | Beschreibung |
|---|---|---|
| `text` | ja | Sprachschlüssel für die Navigationsbezeichnung |
| `file` | ja | Dateiname ohne `.php` Erweiterung, wird aus `/backend/` geladen |

#### editor

Optional. Kennzeichnet ein Plugin als Editor-Plugin (`addon.type` muss dafür `editor`
sein). Es gibt zwei Arten von Editor-Plugins:

- **WYSIWYG-/Code-Editoren** (`mode: "wysiwyg"` oder `mode: "code"`, z. B. TinyMCE, ACE):
  stellen lediglich ein anderes Eingabe-Widget für ein `<textarea>` bereit. Der
  gespeicherte Wert ist immer rohes HTML, unabhängig vom gewählten Editor.
- **Content-Format-Editoren** (`mode: "format"`, z. B. ein Drag&Drop-Baukasten oder ein
  Markdown-Editor): bestimmen das *Format* des gespeicherten Werts selbst. Der Inhalt
  wird als `{"editor": "<id>", "content": ...}` im Content-Feld gespeichert (z. B.
  `page_content`) und beim Laden über `se_register_editor()` an das zuständige Plugin
  delegiert (siehe `app/functions/functions.editors.php`). Ist kein Plugin mit
  passender `editor.id` aktiv, fällt SwiftyEdit auf die Anzeige als rohen Text zurück.

| Feld | Pflicht | Beschreibung |
|---|---|---|
| `id` | ja | Eindeutiger Editor-Schlüssel. Bei `mode: "format"` wird dieser Wert 1:1 im gespeicherten JSON referenziert. |
| `label` | ja | Anzeigename im Editor-Umschalter bzw. in der Format-Auswahl |
| `mode` | ja | `wysiwyg`, `code` oder `format` |
| `order` | ja | Sortierung innerhalb der Editor-Liste (aufsteigend) |
| `core` | optional | `true` markiert den Editor als immer aktiv (umgeht die Plugin-Aktivierung). Nur für mitgelieferte Editoren wie TinyMCE/ACE gedacht. |

### Editor-Plugin austauschen

Ein Content-Format-Editor-Plugin ist über seinen `editor.id`-Wert mit bereits
gespeicherten Seiten verknüpft, nicht über den Plugin-Ordnernamen. Um einen
Content-Format-Editor durch eine neue Implementierung zu ersetzen und dabei bereits
gespeicherte Seiten weiter bearbeitbar zu halten:

1. Das alte Plugin im Backend deaktivieren (sonst registrieren beide Plugins denselben
   Schlüssel, und es ist nicht definiert, welches zuletzt geladen wird und damit gewinnt).
2. Im neuen Plugin denselben Wert für `editor.id` in der `info.json` eintragen, den das
   alte Plugin verwendet hat.
3. Das neue Plugin aktivieren.

Alle Seiten, deren Content-Feld `"editor": "<id>"` enthält, werden ab sofort vom neuen
Plugin gerendert und bearbeitet — ganz ohne Datenmigration.

### Updates

SwiftyEdit prüft automatisch auf Updates beim Aufruf von `/backend/addons/`.
Plugins, die eine gültige `update_url` und `build` in ihrer `info.json` definiert haben,
werden gegen die externe Version geprüft. Wenn ein Update verfügbar ist, erscheint
ein Update-Button neben dem Plugin.

Das Verzeichnis `/data/` innerhalb eines Plugins wird bei einem Update nie überschrieben,
sodass dort gespeicherte Nutzerdaten sicher sind.

### Erlaubte Dateitypen im ZIP

Bei der Installation oder Aktualisierung eines Plugins über eine URL prüft SwiftyEdit
den Inhalt der ZIP-Datei. Nur folgende Dateitypen sind erlaubt:

`php`, `tpl`, `json`, `js`, `css`, `html`, `svg`, `png`, `jpg`, `jpeg`, `gif`, `webp`, `txt`, `md`, `sqlite3`

### Plugin hosten

Du kannst dein Plugin auf jedem Server hosten, auch auf GitHub. Die einzige Voraussetzung
ist, dass die `info.json` und alle ZIP-Dateien öffentlich über HTTPS erreichbar sind.
Wenn du dein Plugin im offiziellen Plugin-Verzeichnis auf SwiftyEdit.com listen möchtest,
kannst du es dort einreichen.