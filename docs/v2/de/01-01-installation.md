---
title: Installation
description: Install SwiftyEdit in less than 5 minutes
btn: Installation
group: developer
priority: 100
---

# Technische Voraussetzungen

Das System benötigt keine außergewöhnlichen Komponenten.

* PHP 8.3+
* Aktiviertes PDO/SQLite Modul. PDO/SQLite ist normalerweise standardmäßig aktiviert. Falls nicht, musst Du bei Deinem Webhoster nachfragen.
* MySQL 5.6+, falls Du die Datenbank lieber auf einem eigenen MySQL-Server statt auf SQLite betreiben möchtest.

## Datenbank: SQLite als Standard {#database}

SwiftyEdit wird mit SQLite als primärer Datenbank entwickelt - nicht nur als Schnelleinstieg für
den Notfall. Die Entwicklung des Projekts selbst findet täglich auf SQLite statt, und es steckt
echter Aufwand darin, SQLite für den produktiven Einsatz schnell und zuverlässig zu machen:

* __Kein separater Datenbankserver nötig__ - PDO/SQLite bringt PHP schon mit, es muss nichts
  Zusätzliches installiert, konfiguriert oder am Laufen gehalten werden (und ein Dienst weniger,
  der ausfallen kann).
* __Backup und Umzug sind trivial__ - die komplette Datenbank besteht aus ein paar Dateien unter
  `data/database/`. Diese zu kopieren ist ein vollständiges, konsistentes Backup; der Umzug auf
  einen neuen Server ist eine Dateikopie, kein Dump-und-Import.
* __Aufteilung in drei getrennte Datenbank-Dateien__ - Content, Benutzerkonten und Posts erhalten
  jeweils ihre eigene SQLite-Datei (siehe `app/database.php`), statt sich eine gemeinsame
  Datenbank zu teilen. So blockiert ein stark genutzter Bereich (z. B. Logins) bei gleichzeitigen
  Zugriffen keinen anderen (z. B. Content-Abfragen).
* __Geringerer Ressourcenverbrauch__ - kein separater Datenbankprozess, der um Arbeitsspeicher
  und CPU konkurriert - das macht sich besonders auf kleinen VPS oder Shared Hosting bemerkbar.
* __Datenbank-intensive Bereiche werden gecacht__ - für die Bereiche, die die Datenbank am
  stärksten beanspruchen, etwa den Shop, unterhält SwiftyEdit einen eigenen dateibasierten Cache
  (z. B. Produktdaten unter `data/cache/products/`, inklusive Slug-Lookup-Map, umschaltbar über
  die Einstellung "Cache für die Produktliste"). Nicht jeder Klick auf eine Produktseite löst
  dadurch überhaupt eine Datenbankabfrage aus - das nimmt der Datenbank selbst viel Last ab,
  unabhängig davon, welches Datenbanksystem Du nutzt.

MySQL 5.6+ wird vollständig unterstützt und funktioniert aus Sicht von SwiftyEdit genauso gut -
eine gute Wahl, wenn Dein Hosting ohnehin auf MySQL ausgelegt ist oder Du lieber einen eigenen
Datenbankserver verwaltest. Beide Optionen sind gleichwertig; SwiftyEdit behandelt keine der
beiden als die "eigentliche" Datenbank.

## SwiftyEdit installieren

Die Installation dauert nur wenige Minuten.

Installation via Composer:

```composer create-project swiftyedit/swiftyedit```

### Manuelle Installation

Lade die aktuelle Version von der Webseite: https://swiftyedit.org/de/download/

1. Alle Dateien auf den Server kopieren.
2. Die Domain muss auf das Verzeichnis /public/ zeigen.
3. Im Webbrowser das Verzeichnis /install/ aufrufen und den Anweisungen folgen ...

War die Installation erfolgreich, führt der nächste Weg in das Backend.
Einfach auf <kbd>Administration</kbd> klicken.
Das Backend erreichst Du übrigens immer über die URL `example.com/admin/`.

__Hinweis:__ Das Verzeichnis `/install/` sollte nach der Installation nicht gelöscht werden -
Kernfunktionen (z. B. das Speichern von Seiten, Produkten, Beiträgen, Events oder Benutzern)
laden ihre Datenbankschemata weiterhin zur Laufzeit aus `install/contents/`. Der
Installations-Assistent selbst sperrt sich nach erfolgreicher Einrichtung automatisch und ist
danach nur noch für bereits eingeloggte Administratoren erreichbar.

__Tipp:__ Unter <kbd>Einstellungen</kbd> → <kbd>Allgemein</kbd> → <kbd>System</kbd> kannst Du
einen Login-Slug festlegen - ein zusätzliches, geheimes Segment, das an die Backend-URL angehängt
wird. Ist er gesetzt, ist das Backend nicht mehr über das einfache `example.com/admin/`
erreichbar; nur die vollständige Adresse inklusive Deines Slugs (z. B.
`example.com/admin/dein-geheimer-slug`) zeigt das Anmeldeformular - wer `/admin/` ohne den Slug
aufruft, wird weitergeleitet. So bleibt die Login-Seite vor allen (auch automatisierten Bots)
verborgen, die die Adresse nicht bereits kennen.

## Updates {#updates}

SwiftyEdit hat eine eingebaute Update-Funktion: Öffne __Update__ in der Seitenleiste des ACP
(siehe [Einstellungen](08-00-settings.md#update)), um zu sehen, ob eine neuere Stable-, Beta-
oder Alpha-Version verfügbar ist, und installiere sie mit einem Klick.

Technisch lädt ein Update das gewählte Release als ZIP-Datei herunter, entpackt sie und kopiert
die Dateien direkt in Deine laufende Installation, anschließend werden nötige
Datenbank-Migrationen (neue Tabellen/Spalten) ausgeführt. Dein `data/`-Verzeichnis sowie
installierte Plugins und Themes bleiben dabei unangetastet - mit Ausnahme der mitgelieferten
Plugins (der Zahlungs-Plugins `se_cash-pay`, `se_invoice-pay`, `se_paypal-pay` sowie der
Editor-Plugins `tinymce-editor`, `ace-editor`), die immer auf den ausgelieferten Stand
aktualisiert werden. Ein automatisches Backup oder einen Rollback-Schritt gibt es nicht.

__Teste ein Update auf einer Live-Seite deshalb zuerst auf einer Staging-Kopie__ - das gilt
besonders, wenn eigene oder Dritt-Plugins/-Themes installiert sind. Eine neue Core-Version ist
nicht automatisch kompatibel zu diesen: Da der Updater Addon-Dateien überhaupt nicht anfasst,
zeigt sich ein inkompatibler Hook, eine geänderte Funktionssignatur oder eine Datenbankänderung
auf Core-Seite erst, wenn die betroffene Seite tatsächlich aufgerufen wird. Für die meisten
Entwickler ist das selbstverständlich, trotzdem der klare Hinweis: Erstelle vor einem Update
selbst ein Backup (Dateien und Datenbank) - SwiftyEdit legt keins für Dich an.