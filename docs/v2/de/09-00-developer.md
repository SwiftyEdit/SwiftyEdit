---
title: Entwickler
description: Eigene Themes und Plugins entwickeln, Konfiguration und Mitarbeit am Projekt
btn: Entwickler
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
* __Bot-Liste__ (`$se_bot_list` in `config.php`) - eine einfache Liste von Teilstrings
  (`googlebot`, `bingbot`, ...), die case-insensitiv gegen den `User-Agent` des Besuchers
  geprüft werden, per `se_is_bot()`. Wird verwendet, um Crawler von den Hit-Countern
  (Seitenaufrufe, Produkt-/Blog-/Event-Aufrufe, Datei-Downloads) auszuschließen, damit sie
  weder die Zahlen aufblähen noch bei viel Traffic unnötige Schreiblast erzeugen. Überschreibe
  die Liste in `data/config.php`, wenn Du Einträge hinzufügen oder entfernen willst - es ist
  ein simpler Substring-Abgleich, keine vollständige Bot-Erkennung, also eher "gut genug für
  Zähler" als eine Sicherheitsmaßnahme.
* __Zwei-Faktor-Anmeldung__ (`$se_2fa_required`, `$se_2fa_bypass` in `config.php`) - mit
  `$se_2fa_required = true;` in `data/config.php` brauchen alle Backend-Zugänge einen zweiten
  Faktor, `$se_2fa_bypass` ist der Notschalter. Details unter
  [Benutzerverwaltung](07-00-user.md#two-factor-authentication).
* __Sitzungsdauer__ (`$se_session_max_lifetime` in `config.php`) - jede Anmeldung endet nach
  dieser Anzahl Sekunden, unabhängig von Aktivität (Standard 12 Stunden, `0` = keine Grenze).
  Unabhängig davon beenden ein neues Passwort, entzogene Rechte oder ein deaktiviertes Konto alle
  anderen Sitzungen dieses Kontos; ändert jemand sein eigenes Passwort, bleibt die aktuelle
  Sitzung bestehen. Im Backend und in den sensiblen Frontend-Bereichen (Profil, Warenkorb,
  Checkout, Bestellungen, Wunschliste, Kommentare, Votings) greift das sofort, auf allen übrigen
  Frontend-Seiten nach spätestens 5 Minuten. Die Liste steht in `app/app.php`
  (`$se_sensitive_page_types`) und `app/xhr/route.php` (`$sensitive_routes`); eigene
  Plugin-Endpunkte mit persönlichen Daten können `se_validate_user_session()` selbst aufrufen.
* __.htaccess bei Updates__ (`$se_update_htaccess` in `config.php`) - Updates ersetzen den
  Core-Block von `public/.htaccess`, eigene Regeln davor und danach bleiben erhalten. Mit
  `$se_update_htaccess = false;` in `data/config.php` bleibt die Datei unangetastet. Details unter
  [Installation](01-01-installation.md#updates-htaccess).
* __Einbettungen__ (`$se_embed_hosts` in `config.php`) - HTML aus dem Editor wird beim Speichern
  mit `se_purify_html()` bereinigt (derzeit Teaser und Text der Kategorien). Iframes bleiben nur
  erhalten, wenn ihre Quelle eine `https`-Adresse aus dieser Liste ist. Ein Eintrag ist ein Host
  (`www.youtube.com`) oder ein Host mit Pfad (`www.google.com/maps/embed`) - dann sind nur
  Adressen erlaubt, die damit beginnen. Standard: YouTube (`www.youtube.com`,
  `www.youtube-nocookie.com`), Vimeo (`player.vimeo.com`), Google Maps
  (`www.google.com/maps/embed`) und OpenStreetMap (`www.openstreetmap.org/export/embed.html`).
  Weitere ergänzt Du in `data/config.php`, z. B. für Facebook und Instagram:
  `$se_embed_hosts[] = 'www.facebook.com/plugins/';` und
  `$se_embed_hosts[] = 'www.instagram.com';`. Es funktioniert nur die Iframe-Variante eines
  Einbettungscodes - Codes mit `<script>` (z. B. der Standard-Code von Instagram mit `embed.js`)
  werden immer entfernt. Beachte, dass Einbettungen bei jedem Seitenaufruf Inhalte vom Anbieter
  laden; je nach Datenschutzrecht braucht es dafür eine Einwilligung.

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

## API

Über die öffentliche REST-API können andere Anwendungen Deine Inhalte lesen - etwa eine zweite
Website, die Deine Produkte anzeigt. Wie Du API-Keys nutzt, welche Endpunkte es gibt und wie
die Antworten aufgebaut sind, beschreibt das Kapitel [API](09-04-api.md).

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