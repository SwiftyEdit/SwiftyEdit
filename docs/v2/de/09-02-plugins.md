---
title: Plugins
description: Plugins erstellen oder installieren
btn: Plugins
group: developer
priority: 200
---

Plugins müssen sich im Ordner `/plugins/` befinden.

## Plugin-Anatomie:

Im Gegensatz zu SwiftyEdit Version 1 müssen alle Plugins einer bestimmten Ordnerstruktur entsprechen:

- Plugin [d]
    - backend [d] (optional)
    - frontend [d] (optional)
        - index.php
    - hooks-backend [d] (optional)
    - hooks-frontend [d] (optional)
    - lang [d] (optional)
    - aftersale.php
    - info.json
    - poster.png (optional)
    - readme.md

### Tipps

- Wenn Sie Ihr Plugin mit dem Präfix „-pay“ benennen, wird es automatisch
  als Zahlungs-Plugin erkannt. Dadurch wird die Datei „aftersale.php“ erforderlich.
- Sie können auf die Datei „xhr.php“ über /xhr/plugins/{plugin}/ zugreifen.

### Aktivierte Plugins

Plugins gelten als aktiv, wenn sie entweder in irgendeine Seite eingebunden wurden 
oder sie manuell im Backend aktiviert wurden.

Ein Plugin muss aktiviert sein damit es

- im Frontend XHR-Requests verarbeiten kann
- im Frontend Hooks ausgeführt werden können

### Wann werden welche Plugin Dateien geladen?

Folgende includes sind möglich:

1. `/plugins/{plugin}/index.php`
2. `/plugins/{plugin}/frontend/index.php`
3. `/plugins/{plugin}/global/index.php`
4. `/plugins/{plugin}/global/xhr.php`


1. Wenn ein Plugin per Shortcode eingebunden wird: `[plugin={plugin}]foo=bar[/plugin]`
2. Wenn ein Plugin innerhalb einer Seite eingebunden wird, also im Plugin-Tab aktiviert wird,
wird im Frontend der Seiten-Inhalt durch das Plugin ersetzt.
3. Wenn ein Plugin aktiv ist. Das bedeutet, es wurde entweder über den Plugin-Tab 
in eine Seite integriert oder manuell aktiviert.
4. Wenn ein Plugin im Frontend XHR-Requests verarbeiten soll. 
Das Plugin muss dazu aktiviert sein. Die korrekte Route lautet `/xhr/plugins/{plugin}/`

