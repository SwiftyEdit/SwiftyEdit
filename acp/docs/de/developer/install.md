---
title: Installation
description: Installiere SwiftyEdit in weniger als 5 Minuten
btn: Installation
group: developer
priority: 100
---

# Technische Voraussetzungen

Das System benötigt keine außergewöhnlichen Komponenten.

* PHP 8.3+
* aktiviertes PDO/SQLite Modul - PDO/SQLite ist normalerweise standardmäßig aktiviert. Falls nicht musst Du bei Deinem Webhoster nachfragen.
* MySQL (empfohlen), falls Du nicht die komplette Installation auf SQLite betreiben möchtest.

## SwiftyEdit installieren

Die Installation dauert nur wenige Minuten.

Installation vie Composer:

```composer create-project swiftyedit/swiftyedit```

Oder lade die Dateien von der Webseite: https://swiftyedit.org/de/download/

### Die Installation

1. Alle Dateien auf den Server kopieren.
2. Die Domain muss auf das Verzeichnis /public/ zeigen.
3. Im Webbrowser das Verzeichnis /install/ aufrufen und den Anweisungen folgen ...

War die Installation erfolgreich, führt der nächste Weg in das ACP.
Einfach auf <kbd>Administration</kbd> klicken.
Das Backend erreichst Du übrigens immer über die URL `example.com/admin/`.