---
title: Einstellungen
description: Alle globalen Einstellungen von SwiftyEdit an einem Ort
btn: Einstellungen
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
* API-Keys

## Allgemein {#general}

Die Seite Allgemein ist in drei Tabs unterteilt: <kbd>Allgemein</kbd>, <kbd>System</kbd> und
<kbd>E-Mail</kbd>. Jeder Abschnitt wird mit seinem eigenen Button gespeichert.

Die Texte der folgenden Abschnitte werden im Backend auch direkt neben den Einstellungen angezeigt.

### Website {#general-website}
Name, Titel, Untertitel und Beschreibung Deiner Website. Themes zeigen sie z.B. im Kopfbereich an
und nutzen sie für die Meta-Tags aller Seiten, die keine eigenen Werte haben.

### Logo und Icons {#general-branding}
Logo, Vorschaubild und Favicon werden direkt beim Hochladen gespeichert. Aus dem Favicon (PNG, JPG
oder WebP, mindestens 512×512 px) erzeugt SwiftyEdit alle Icon-Größen inklusive Apple Touch Icon
und das Web-Manifest.

### Bildgrößen {#general-images}
Hochgeladene Bilder werden auf die maximale Breite und Höhe verkleinert, Vorschaubilder auf die
Thumbnail-Größe. Alle Angaben in Pixel.

### Uploads {#general-uploads}
Die maximale Dateigröße von Uploads in KB. Bleiben Uploads unverändert, werden Bilder so
gespeichert, wie sie hochgeladen wurden - ohne Verkleinerung und ohne Vorschaubild.

### Seitenversionen {#general-page-versions}
Jedes Mal, wenn Du eine Seite aktualisierst, speichert SwiftyEdit die überschriebene Version.
Damit kannst Du, falls Du mal einen Fehler gemacht hast, wieder zu einer älteren Version
zurückwechseln. Gibt es mehr Versionen als hier angegeben, werden beim Speichern der Seite die
ältesten gelöscht.

### Domain {#general-domain}
Domain und SSL-Domain (inklusive `http://` bzw. `https://`) und der Basis-Pfad ergeben die Adresse
Deiner Website - ist eine SSL-Domain eingetragen, wird sie verwendet. SwiftyEdit braucht die
Adresse für absolute Links, z.B. Canonical URLs, Sitemaps, Feeds und E-Mails.

Mit einem Login-Slug ist die Anmeldung zum Backend nur noch unter `/admin/{slug}` erreichbar,
`/admin/` führt dann zur Startseite.

### Datum und Uhrzeit {#general-datetime}
Die Zeitzone und die Formate von Datum und Uhrzeit, im Frontend und im Backend.

### Themes und Templates {#general-themes}
Solange ein Wartungs-Code eingetragen ist, sehen Besucher nur die Wartungsseite. Wer dort den Code
eingibt, sieht die Website wie gewohnt (z.B. um sie vor dem Livegang zu prüfen).

Außerdem legst Du fest, ob Besucher selbst ein Theme wählen dürfen. Der Smarty Compile Check
übersetzt geänderte Templates bei jedem Aufruf neu - praktisch, während Du ein Theme entwickelst,
ansonsten bleibt er aus.

### Sprache {#general-language}
Die Standardsprache Deiner Website. Ausgeblendete Sprachen werden im Backend nicht mehr angeboten,
z.B. in der Sprachauswahl von Seiten und Beiträgen.

### E-Mail {#general-mail}
Absendername und -adresse aller E-Mails Deiner Website. Sie werden mit PHP `mail()` oder per SMTP
verschickt - die SMTP-Zugangsdaten stehen in `data/config_smtp.php`. Die Benachrichtigungs-Adresse
erhält eine Kopie der Bestell-E-Mails und Bestellanfragen sowie die Widerrufe von Bestellungen.

### Test-E-Mail {#general-mail-test}
Schickt eine E-Mail mit den gespeicherten Einstellungen an die Benachrichtigungs-Adresse, oder an
die Absenderadresse, falls keine eingetragen ist. Speichere Deine Änderungen also vorher.

## User {#user}
Diese Einstellungen steuern alles rund um Benutzerkonten und Interaktion.

### Registrierung und Login {#user-registration}
Ob sich Besucher selbst registrieren dürfen und ob das Login-Formular angezeigt wird. Ohne
Login-Formular ist die Anmeldung im Frontend ganz abgeschaltet (die Backend-Anmeldung unter
`/admin/` ist nicht betroffen, "Passwort vergessen" funktioniert weiterhin).

Müssen neue Benutzer von einem Administrator freigeschaltet werden, können sie sich zwar anmelden,
im Shop aber erst bestellen, wenn sie in der Benutzerverwaltung freigeschaltet wurden.

### Pflichtfelder {#user-required-fields}
Felder des Registrierungsformulars, die zusätzlich zu Benutzername, E-Mail und Passwort ausgefüllt
werden müssen. Wähle nur Felder aus, die das Registrierungsformular Deines Themes auch enthält -
sonst kann sich niemand mehr registrieren.

### Gesperrte Benutzernamen {#user-blacklist}
Benutzernamen, die nicht registriert werden dürfen, durch Kommas getrennt. Groß- und
Kleinschreibung spielt keine Rolle.

### Backend-Session {#user-session}
Wie lange (in Sekunden) ein Administrator ohne Aktivität im Backend angemeldet bleibt. Ist die
PHP-Einstellung `session.gc_maxlifetime` des Servers länger, gilt diese.

### Kommentare {#user-comments}
Ob Kommentare von einem Administrator freigegeben werden müssen, sofort erscheinen oder ganz
abgeschaltet sind. Zusätzlich werden Kommentare bei jeder Seite, jedem Beitrag und jedem Produkt
einzeln ein- oder ausgeschaltet. Wer kommentiert, gibt immer einen Namen und eine E-Mail-Adresse an.

Mit „Die Kommentarfunktion schließen nach *n* Tagen“ kannst Du Kommentare automatisch beenden: Bei
Beiträgen und Produkten zählen die Tage ab dem Veröffentlichungsdatum, bei Seiten ab der letzten
Änderung - jedes Speichern einer Seite öffnet die Kommentare also wieder. Danach bleiben die
vorhandenen Kommentare sichtbar, neue Kommentare und Antworten sind aber nicht mehr möglich.
0 oder ein leeres Feld bedeutet: nie schließen.

### Votings und Reaktionen {#user-votings}
Die Voreinstellung für neue Beiträge, Produkte und Veranstaltungen - bei jedem Eintrag kannst Du
sie einzeln ändern: aus, nur für registrierte Benutzer oder für alle Besucher. Gegen doppelte
Stimmen werden anonyme Votings und Gästelisten-Zusagen mit einem Hash der IP-Adresse gespeichert;
nach 90 Tagen wird der Hash entfernt - die Stimme zählt weiter.

## Posts {#posts}
Diese Einstellungen betreffen die Posts (Blog).

### Einträge pro Seite {#posts-entries}
Wie viele Beiträge eine Blog-Liste pro Seite zeigt, weitere Beiträge sind über die Seitennavigation
erreichbar. Die Listen der Veranstaltungen verwenden dieselbe Anzahl. Ist kein Wert oder 0 eingetragen,
sind es 10 Einträge.

## Shop {#shop}
Die Shop-Seite ist in vier Tabs unterteilt: <kbd>Allgemein</kbd>, <kbd>Zahlung & Versand</kbd>,
<kbd>Liefergebiete</kbd> und <kbd>Geschäftsangaben</kbd>. Jeder Abschnitt wird mit seinem eigenen
Button gespeichert.

### Produktliste {#shop-list}
Wie viele Produkte eine Produktliste pro Seite zeigt (ohne Wert oder mit 0 sind es 10) und wie sie
standardmäßig sortiert ist - Besucher können die Sortierung in der Liste selbst ändern. Ist der Cache
eingeschaltet, werden die Daten der Produkte zwischengespeichert, statt sie bei jedem Aufruf aus der
Datenbank zu lesen.

### Warenkorb und Bestellungen {#shop-cart}
Der Warenkorb-Modus schaltet den Warenkorb komplett aus, beschränkt ihn auf registrierte Benutzer oder
öffnet ihn für alle. Der Bestell-Modus legt fest, ob Produkte direkt bestellt werden können, ob
Besucher stattdessen nur eine Bestellanfrage senden können, oder beides. Wenn Du einen maximalen
Bestellwert festlegst, fällt der Warenkorb bei Überschreitung automatisch auf die Anfrage-Funktion
zurück, auch wenn die Direktbestellung aktiviert ist.

### Listen {#shop-lists}
Aktiviere Listen, damit angemeldete Kunden Produkte über die Produktkarte oder Produktseite in eine
oder mehrere persönliche Listen speichern können. Eine Liste kann optional über einen öffentlichen Link
geteilt werden - wer diesen Link öffnet, kann die Liste ansehen und die enthaltenen Produkte ohne
eigenes Konto in den eigenen Warenkorb legen.

### Gastbestellungen {#shop-guest-orders}
Aktiviere Gastbestellungen, damit Kunden den Checkout abschließen können, ohne ein Konto anzulegen.
Gastbestellungen werden per E-Mail bestätigt, die eingegebene Lieferadresse wird nur für die aktuelle
Sitzung gespeichert.

### Widerrufsfrist {#shop-withdrawal}
Wie viele Tage lang der Button "Bestellung widerrufen" bei einer Bestellung auf der Seite "Meine
Bestellungen" angezeigt wird. Ohne Wert sind es 14 Tage (die gesetzliche EU-Widerrufsfrist); mit `0`
wird der Button unabhängig vom Bestellalter immer angezeigt. Das betrifft nur die Sichtbarkeit des
Buttons - die Seite "Bestellung widerrufen" selbst ist weiterhin jederzeit direkt erreichbar.

### Steuersätze und Währung {#shop-taxes}
Du kannst bis zu drei feste Steuersätze festlegen (Steuer #1-#3); jedes Produkt wählt dann individuell
einen davon aus - eine separate Verwaltung von Steuerklassen gibt es nicht. Die Standardwährung ist ein
Freitextfeld (z. B. `EUR`). Außerdem legst Du fest, ob Preise brutto, netto oder beides angezeigt
werden, und ob sie für alle Besucher oder nur für registrierte Benutzer sichtbar sind.

### Versandkosten {#shop-shipping}
Lege fest, ob der Versand mit einer Pauschale berechnet wird oder anhand von Versandkategorien, wobei
die teuerste im Warenkorb vorhandene Kategorie die Versandkosten bestimmt. Du kannst sowohl die
Pauschale als auch bis zu drei kategorie-basierte Versandkosten definieren.

### Versand-Plugins {#shop-delivery-plugins}
Versandoptionen werden über Plugins bereitgestellt (Ordnername endet auf `-delivery`). Jedes
installierte Versand-Plugin erscheint hier und kann für den Shop aktiviert werden.

### Zahlungs-Plugins {#shop-payment-plugins}
Zahlungsarten wie Barzahlung, Rechnung oder PayPal werden über Plugins bereitgestellt (Ordnername
endet auf `-pay`). Jedes installierte Zahlungs-Plugin erscheint hier, aktivierte Plugins werden im
Checkout als Zahlungsart angeboten.

### Liefergebiete {#shop-delivery-areas}
Füge über die Länderauswahl die Länder hinzu, in die Du lieferst - jedes Land gibt es nur einmal,
erneutes Hinzufügen ändert den vorhandenen Eintrag. Jedes Liefergebiet kann auf öffentlich oder
Entwurf gesetzt werden, außerdem legst Du fest, ob für Bestellungen in dieses Land Steuer
hinzugerechnet wird.

### Geschäftsangaben {#shop-business}
Deine Geschäftsadresse und Steuernummer. SwiftyEdit selbst verwendet sie derzeit nicht, Plugins
können sie aber auslesen (`$se_settings['business_address']` und
`$se_settings['business_taxnumber']`).

## Veranstaltungen / Events
Hier findest Du die Voreinstellungen für das Event-Modul. Du kannst z.B. festlegen ob die Gästelisten
aktiviert werden sollen.

## Labels {#labels}
Sämtliche Einträge und Daten im Backend können mit Labels versehen werden.
Wenn Du viele Daten und Einträge verwaltest, helfen diese Labels den Überblick zu behalten.

## API-Keys {#api-keys}
API-Keys geben anderen Anwendungen Zugriff auf die öffentliche API - zum Beispiel einer zweiten
Website, die Deine Produkte anzeigt. Wie die API benutzt wird, erfahren Entwickler im Kapitel
[API](09-04-api.md).

Zum Anlegen eines Keys vergibst Du einen Namen (z.B. den Namen der Anwendung, die ihn nutzt) und
wählst die Berechtigungen aus:

| Berechtigung | Bedeutung |
|--------------|-----------|
| Produkte lesen | Der Key kann öffentliche Produkte abrufen. |
| Preise lesen, auch wenn sie nur angemeldeten Kunden angezeigt werden | Nur relevant, wenn der Shop Preise laut Einstellung nur angemeldeten Kunden zeigt. Ohne diese Berechtigung liefert die API in dem Fall keine Preise. |
| Blog-Beiträge lesen | Der Key kann öffentliche Blog-Beiträge abrufen. |
| Veranstaltungen lesen | Der Key kann öffentliche Veranstaltungen abrufen. |
| Seiten lesen | Der Key kann öffentliche Seiten abrufen. Passwortgeschützte Seiten und Seiten für Benutzergruppen bleiben verborgen. |
| Kategorien lesen | Der Key kann die Kategorien von Produkten, Beiträgen und Veranstaltungen abrufen. |

{alert:warning}
__Der Key wird nur einmal angezeigt__, direkt nach dem Anlegen. Kopiere ihn und bewahre ihn
sicher auf - SwiftyEdit speichert nur einen Fingerabdruck (Hash) des Keys und kann ihn später
nicht mehr anzeigen. Ist ein Key verloren gegangen, legst Du einfach einen neuen an.
{/alert}

In der Liste siehst Du den Anfang jedes Keys, damit Du sie auseinanderhalten kannst, außerdem
wann der Key zuletzt benutzt wurde und wie viele Anfragen damit gestellt wurden.

* __Sperren__ blockiert einen Key sofort - Anwendungen, die ihn nutzen, verlieren den Zugriff.
  Das Sperren lässt sich nicht rückgängig machen.
* __Löschen__ lassen sich nur gesperrte Keys.

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

**Compact database** zeigt für jede der drei Datenbankdateien die Größe und den freien
Platz darin. Gelöschte Daten hinterlassen freien Platz in der Datei, den SQLite für neue
Daten wiederverwendet - kleiner wird die Datei dadurch aber nicht. Der Button komprimiert
die Dateien (`VACUUM`) und gibt diesen Platz an die Festplatte zurück. Dabei wird jede
Datei neu geschrieben: Solange das läuft, kann nichts gespeichert werden, und es wird
vorübergehend so viel freier Speicherplatz benötigt, wie die größte Datei groß ist.
Sinnvoll ist das vor allem, nachdem viele Daten gelöscht wurden.

---

## Update {#update}
Update ist ein eigener Eintrag in der Seitenleiste (nicht Teil der Einstellungen).
Falls eine neuere Version zur Installation bereitsteht, wird diese dort angezeigt. Was ein
Update dabei technisch macht und wie Du sicher aktualisierst, steht unter
[Updates](01-01-installation.md#updates).