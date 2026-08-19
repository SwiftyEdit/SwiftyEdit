---
title: Settings
description: Settings
btn: Settings
group: backend
priority: 900
---

# Einstellungen

<kbd>Backend</kbd> ▶ <kbd>Einstellungen</kbd>

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
Die Shop-Seite ist in vier Tabs unterteilt:

* <kbd>Allgemein</kbd> Einträge pro Seite und die Standard-Sortierung für die Produktliste, ob der
  Cache für die Produktliste aktiviert ist, der Warenkorb-Modus, der Bestell-Modus und Gastbestellungen,
  ein maximaler Bestellwert, die Widerrufsfrist, bis zu drei Steuersätze, die Standardwährung sowie wie
  und für wen Preise angezeigt werden.
* <kbd>Zahlung & Versand</kbd> Der Versand-Modus und die Versandkosten sowie Aktivierungsschalter für
  installierte Zahlungs- und Versand-Plugins.
* <kbd>Liefergebiete</kbd> Länder, in die Du lieferst, hinzufügen, bearbeiten oder entfernen.
* <kbd>Geschäftsangaben</kbd> Deine Geschäftsadresse und Steuernummer.

### Warenkorb und Bestellungen
Der Warenkorb-Modus schaltet den Warenkorb komplett aus, beschränkt ihn auf registrierte Benutzer oder
öffnet ihn für alle. Der Bestell-Modus legt fest, ob Produkte direkt bestellt werden können, ob
Besucher stattdessen nur eine Bestellanfrage senden können (statt zu bezahlen), oder beides. Wenn Du
einen maximalen Bestellwert festlegst, fällt der Warenkorb bei Überschreitung automatisch auf die
Anfrage-Funktion zurück, auch wenn die Direktbestellung aktiviert ist.

### Listen {#listen}
Aktiviere Listen, damit angemeldete Kunden Produkte über die Produktkarte oder Produktseite in eine
oder mehrere persönliche Listen speichern können. Eine Liste kann optional über einen öffentlichen Link
geteilt werden - wer diesen Link öffnet, kann die Liste ansehen und die enthaltenen Produkte ohne
eigenes Konto in den eigenen Warenkorb legen.

### Gastbestellungen
Aktiviere Gastbestellungen, damit Kunden den Checkout abschließen können, ohne ein Konto anzulegen.
Gastbestellungen werden per E-Mail bestätigt, die eingegebene Lieferadresse wird nur für die aktuelle
Sitzung gespeichert.

### Widerrufsfrist
Das Feld "Widerrufsbutton sichtbar für (Tage)" legt fest, wie viele Tage lang der Button "Bestellung
widerrufen" bei einer Bestellung auf der Seite "Meine Bestellungen" angezeigt wird. Standardmäßig sind
das 14 Tage (die gesetzliche EU-Widerrufsfrist); mit `0` wird der Button unabhängig vom Bestellalter
immer angezeigt. Das betrifft nur die Sichtbarkeit des Buttons - die Seite "Bestellung widerrufen"
selbst ist weiterhin jederzeit direkt erreichbar.

### Steuersätze und Währung
Du kannst bis zu drei feste Steuersätze festlegen (Steuer #1-#3); jedes Produkt wählt dann individuell
einen davon aus - eine separate Verwaltung von Steuerklassen gibt es nicht. Die Standardwährung ist ein
Freitextfeld (z. B. `EUR`). Außerdem kannst Du festlegen, ob Preise brutto, netto oder beides angezeigt
werden, und ob sie für alle Besucher oder nur für registrierte Benutzer sichtbar sind.

### Versandkosten
Lege fest, ob der Versand mit einer Pauschale berechnet wird oder anhand von Versandkategorien, wobei
die teuerste im Warenkorb vorhandene Kategorie die Versandkosten bestimmt. Du kannst sowohl die
Pauschale als auch bis zu drei kategorie-basierte Versandkosten definieren.

### Zahlungs- und Versand-Plugins
Zahlungsarten (z. B. Barzahlung, Rechnung, PayPal) und Versandoptionen werden über Plugins
bereitgestellt. Jedes installierte Zahlungs- oder Versand-Plugin erscheint in einer eigenen Liste im
Tab Zahlung & Versand, wo Du es für den Shop aktivieren kannst.

### Liefergebiete
Füge über die Länderauswahl die Länder hinzu, in die Du lieferst. Jedes Liefergebiet kann auf
öffentlich oder Entwurf gesetzt werden, außerdem kannst Du festlegen, ob für Bestellungen in dieses
Land Steuer hinzugerechnet werden soll.

### Geschäftsangaben
Deine Geschäftsadresse und Steuernummer werden hier hinterlegt und für Bestellungen und Rechnungen
verwendet.

## Veranstaltungen / Events
Hier findest Du die Voreinstellungen für das Event-Modul. Du kannst z.B. festlegen ob die Gästelisten
aktiviert werden sollen.

## Labels {#labels}
Sämtliche Einträge und Daten im Backend können mit Labels versehen werden.
Wenn Du viele Daten und Einträge verwaltest, helfen diese Labels den Überblick zu behalten.

## Database {#database}
Dieser Tab erscheint nur, wenn die Seite mit SQLite betrieben wird (bei MySQL-Installationen
wird er ausgeblendet, da dort nicht relevant).

**Enable WAL mode** schaltet den Journal-Modus von SQLite vom Standard `DELETE` auf `WAL`
(Write-Ahead Logging) um. Im `DELETE`-Modus sperrt ein Schreibvorgang die gesamte
Datenbankdatei, sodass gleichzeitige Lesezugriffe warten müssen, bis der Schreibvorgang
abgeschlossen ist. Im `WAL`-Modus können Lesezugriffe weiterlaufen, während geschrieben
wird, statt die komplette Datei zu sperren. Auf Seiten mit viel gleichzeitigem Traffic kann
das `database is locked`-Fehler und Zugriffszeiten spürbar reduzieren. Die Einstellung wird
sofort auf alle drei SQLite-Datenbanken (content, user, posts) angewendet und lässt sich
jederzeit wieder deaktivieren - es handelt sich um eine dauerhafte Eigenschaft der
Datenbankdateien, die nicht bei jedem Request neu gesetzt werden muss.

Zwei Dinge solltest Du vor dem Aktivieren wissen:

- **Storage:** WAL-Modus benötigt funktionierendes Shared-Memory-Locking auf dem
  Dateisystem. Auf normalem lokalem Storage ist das unproblematisch, auf
  Netzwerk-Dateisystemen (z. B. NFS) oder bestimmten nicht-lokalen Docker-Volume-Mounts
  kann es unzuverlässig sein.
- **Backups:** Der WAL-Modus legt zwei zusätzliche Dateien (`-wal` und `-shm`) neben jeder
  Datenbankdatei an. Ein Backup-Prozess, der nur die `.sqlite3`-Datei kopiert, kann aktuelle,
  noch nicht in die Hauptdatei übernommene Änderungen verpassen. Stelle sicher, dass Dein
  Backup die `-wal`/`-shm`-Dateien mit einschließt, oder führe vor dem Kopieren einen
  Checkpoint aus (`PRAGMA wal_checkpoint(TRUNCATE)`).

---

## Update {#update}
Update ist ein eigener Eintrag in der Seitenleiste (nicht Teil der Einstellungen).
Falls eine neuere Version zur Installation bereitsteht, wird diese dort angezeigt. Was ein
Update dabei technisch macht und wie Du sicher aktualisierst, steht unter
[Updates](01-01-installation.md#updates).