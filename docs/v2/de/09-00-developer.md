---
title: Developer
description: Developer notes and instructions
btn: Developer
group: developer
priority: 200
---

# Entwickler

Schön, dass Du Dich für die Entwicklung mit und an SwiftyEdit interessierst! In diesem Bereich
findest Du alles, was Du brauchst, um eigene Themes und Plugins zu bauen, mit Hooks in Abläufe
einzugreifen oder direkt zum Projekt beizutragen.

## Notizen

Die Datei `config.php` legt sämtliche Konfigurationen fest. Da die Datei bei jedem Update überschrieben wird, 
musst du eine eigene Datei anlegen. Lege dazu einfach eine Datei im Verzeichnis data an: `data/config.php`. 
Du musst hier nur die Werte angeben, die du überschreiben möchtest.

Um E-Mails über das SMTP Protokoll zu senden wird eine Datei `config_smtp.php` benötigt.

Beispiel:
```php
<?php
// data/config_smtp.php
$smtp_port = 587;
$smtp_username = 'admin@example.com';
$smtp_psw = 'example';
$smtp_encryption = 'tls';
```

Ein paar weitere Kleinigkeiten, die gut zu wissen sind:

* __CSRF-Schutz__ - Jede `$_POST`-Aktion wird geprüft. Baust Du ein eigenes Formular (z. B. in
  einem Plugin oder einem Theme-XHR-Endpunkt), musst Du das versteckte Feld
  `<input type="hidden" name="csrf_token" value="...">` (im Frontend als `$hidden_csrf_token`
  verfügbar) mitsenden und serverseitig mit `se_validate_token($_POST['csrf_token'])` prüfen -
  sonst leitet SwiftyEdit auf `/` um.
* __Umgebung__ (`$se_environment` in `config.php`) - `'p'` für Produktion (Standard) oder `'d'`
  für Entwicklung.
* __Betriebsmodus__ (`$se_mode` in `config.php`) - `0` Self-Hosting (Standard), `1` Self-Hosting
  mit Multisite, `2` bereitgestelltes Multisite-Hosting. Multisite ist experimentell und noch
  nicht vollständig ausgereift - verlasse Dich in eigenem Code nicht darauf, ohne den jeweiligen
  Codepfad selbst zu prüfen.

## Themes

Themes entscheiden, wie SwiftyEdit für Deine Besucher aussieht und sich anfühlt - vom
Gesamtlayout über einzelne Templates bis zum Look des WYSIWYG-Editors. Das Kapitel
[Themes](09-01-00-themes.md) nimmt Dich mit durch den Aufbau eines eigenen Themes, das
Bereitstellen mehrerer Layouts und zeigt, wie viel (oder wie wenig) Du dabei vom `default`-Theme
erben kannst.

## Plugins

Plugins sind Dein Werkzeug, um SwiftyEdit um eigene Funktionen zu erweitern, ohne den Core
anzufassen - von einer kleinen Backend-Seite bis zu einem ganz neuen Frontend-Modul. Wie ein
Plugin aufgebaut ist und sich ins ACP einklinkt, zeigt Dir das Kapitel [Plugins](09-02-plugins.md).

## Hooks

Hooks lassen Dich an genau festgelegten Stellen ins Geschehen eingreifen - Inhalte vor der
Ausgabe verändern oder auf Ereignisse wie eine aktualisierte Seite reagieren, ganz ohne
Kernfunktionen anzurühren. Wie das im Detail funktioniert, erfährst Du im Kapitel
[Hooks](09-03-hooks.md).

## Mitarbeit

SwiftyEdit ist Open Source und freut sich über Beiträge. Das Projekt liegt auf
[GitHub](https://github.com/SwiftyEdit/SwiftyEdit) - forke das Repository und installiere eine
Dev-Version mit

```bash
composer create-project swiftyedit/swiftyedit=dev-main swiftyedit-dev --stability=dev
```

und erstelle einen Pull Request von einem Branch nach dem Muster `feature/kurzbeschreibung` oder
`fix/issue-nummer`. Halte Pull Requests klein und fokussiert, verweise auf zugehörige Issues und
aktualisiere bei nutzerrelevanten Änderungen auch die passende Doku. Fragen stellst Du am besten
als GitHub Discussion oder Issue; Issues mit dem Label `good-first-issue` eignen sich gut zum
Einstieg.

Die vollständigen Richtlinien (Code-Style, Workflow-Details) stehen in `CONTRIBUTING.md` im
Wurzelverzeichnis des Repositories. Von allen Mitwirkenden wird erwartet, dass sie den
`CODE_OF_CONDUCT.md` direkt daneben befolgen.

## Lizenz

SwiftyEdit steht unter der __GNU General Public License v3.0 or later (GPL-3.0-or-later)__. Der
vollständige Lizenztext liegt in `license.txt` im Wurzelverzeichnis des Repositories. Kurz
gefasst: Du darfst SwiftyEdit frei nutzen, untersuchen, verändern und weitergeben, aber jede
weitergegebene abgeleitete Arbeit muss ebenfalls unter der GPL stehen, mit verfügbar gemachtem
Quellcode.

Mitgelieferte Bibliotheken von Drittanbietern (z. B. die Editoren in
`public/assets/editors/`, oder die npm-Abhängigkeiten eines Themes aus dessen `package.json`)
behalten ihre eigene, separate Lizenz - prüfe vor einer Weitergabe als Teil eines Themes oder
Plugins die Lizenz der jeweiligen Bibliothek.