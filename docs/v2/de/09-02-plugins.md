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
  - backend [d] (optional)
  - frontend [d] (optional)
    - index.php
  - hooks-backend [d] (optional)
  - hooks-frontend [d] (optional)
  - lang [d] (optional)
  - data [d] (optional, wird bei Updates nie überschrieben)
  - aftersale.php
  - info.json
  - poster.png (optional)
  - readme.md

### Tipps

- Wenn du deinem Plugin den Präfix `-pay` gibst, wird es automatisch als
  Zahlungs-Plugin erkannt. Dadurch wird die Datei `aftersale.php` zur Pflicht.
- Die xhr.php Datei ist über `/xhr/plugins/{plugin}/` erreichbar.

### Aktivierte Plugins

Plugins gelten als aktiv, wenn sie in eine Seite eingebunden oder im Backend
manuell aktiviert wurden.

Ein Plugin muss aktiviert sein, damit es

- XHR-Anfragen im Frontend verarbeiten kann
- Hooks im Frontend ausgeführt werden können

### Wann werden welche Plugin-Dateien geladen?

Folgende Includes sind möglich:

1. `/plugins/{plugin}/index.php`
2. `/plugins/{plugin}/frontend/index.php`
3. `/plugins/{plugin}/global/index.php`
4. `/plugins/{plugin}/global/xhr.php`

---

1. Wenn ein Plugin über einen Shortcode eingebunden wird: `[plugin={plugin}]foo=bar[/plugin]`
2. Wenn ein Plugin innerhalb einer Seite eingebunden wird, also im Plugin-Tab aktiviert ist,
   wird der Seiteninhalt im Frontend durch das Plugin ersetzt.
3. Wenn ein Plugin aktiv ist. Das bedeutet, es wurde entweder über den Plugin-Tab
   in eine Seite eingebunden oder manuell aktiviert.
4. Wenn ein Plugin XHR-Anfragen im Frontend verarbeiten soll.
   Das Plugin muss dafür aktiviert sein. Die korrekte Route ist `/xhr/plugins/{plugin}/`

## Die info.json Datei

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

### Felder

#### addon

| Feld | Pflicht | Beschreibung |
|---|---|---|
| `id` | empfohlen | Eindeutiger Bezeichner des Plugins. Wird als Ordnername verwendet. Wenn nicht angegeben, wird er aus der URL abgeleitet. |
| `type` | ja | Muss `plugin` sein |
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