---
title: Settings
description: Settings
btn: Settings
group: backend
priority: 900
---

# Einstellungen

Die Einstellungen sind in folgende Unterkategorien unterteilt

* Allgemein
* User
* Posts
* Shop
* Events
* Labels

## Allgemein

Die Seite Allgemein ist in drei Tabs unterteilt:

* <kbd>Allgemein</kbd> Die Standard-Metadaten (Seitenname, Titel, Untertitel, Beschreibung, Autor),
  der RSS-Zeitversatz, die Anzahl gespeicherter Seitenversionen sowie die Bild-Einstellungen
  (Bild-Präfix, Logo/Vorschaubild/Favicon, maximale Bild- und Vorschaubildgrößen, maximale
  Upload-Dateigröße). Sämtliche Metadaten können später von einzelnen Seiten oder Produkten usw.
  überschrieben werden.
* <kbd>System</kbd> Domain, SSL-Domain, Basis-Pfad und Login-Slug sowie Datums-/Zeitformat und
  Zeitzone, Themes & Templates, der Wartungs-Code, die Smarty-Cache-Einstellungen, die
  Standardsprache und die Möglichkeit, einzelne Sprachen auszublenden.
* <kbd>E-Mail</kbd> Die Mailer-Konfiguration (Absendername und -adresse, Mail-Typ / SMTP,
  Benachrichtigungsadresse) sowie eine Test-Mail-Funktion.

### RSS Time offset
Hier kannst Du einen Zeitversatz festlegen. Dies verhindert, dass Beiträge direkt nach dem Speichern
im RSS Feed landen.

### Seitenversionen
Jedes mal, wenn Du eine Seite aktualisierst, speichert SwiftyEdit die überschriebene Version.
Damit kannst Du, falls Du mal einen Fehler gemacht hast, wieder zu einer älteren Version zurückwechseln.
Hier legst Du fest, wie viele Versionen einer Seite gespeichert bleiben sollen.

## User
Diese Einstellungen steuern alles rund um Benutzerkonten und Interaktion:

* Ob sich Besucher selbst registrieren dürfen, ob das Login-Formular angezeigt wird
  und ob neue Benutzer von einem Administrator freigeschaltet werden müssen.
* Die Session-Lebensdauer des Backends und eine Blacklist von Benutzernamen, die nicht
  registriert werden dürfen.
* Welche Felder bei der Registrierung Pflichtfelder sind.
* Kommentare: der Modus, die Autorisierung, die Auto-Close-Zeit, die maximale Anzahl an
  Einträgen und die maximale Verschachtelungstiefe.
* Votings und Reaktionen (aus, nur für registrierte Benutzer oder global).

## Posts
Diese Einstellungen betreffen die Posts (Blog).

## Shop
Hier kannst Du festlegen, ob die Warenkorbfunktion aktiv ist und/oder ob Artikel verkauft werden.
Außerdem kannst Du Angaben zu den Steuersätzen, Versand und Liefergebiete hinterlegen.

## Veranstaltungen / Events
Hier findest Du die Voreinstellungen für das Event-Modul. Du kannst z.B. festlegen ob die Gästelisten
aktiviert werden sollen.

## Labels {#labels}
Sämtliche Einträge und Daten im Backend können mit Labels versehen werden.
Wenn Du viele Daten und Einträge verwaltest, helfen diese Labels den Überblick zu behalten.

---

## Update
Update ist ein eigener Eintrag in der Seitenleiste (nicht Teil der Einstellungen).
Falls eine neuere Version zur Installation bereitsteht, wird diese dort angezeigt.