---
title: Grundlagen
description: Das Dashboard und die grundlegende Bedienung des Backends
btn: Grundlagen
group: administrators
priority: 100
---

# Dashboard {#dashboard}

Hier findest du auf einen Blick sämtliche Informationen zu deiner Internetseite.

Oben siehst du, was in den letzten 30 Tagen passiert ist. Das Raster hat eine Zeile je Bereich
(Seiten, Snippets, Blog, Produkte, Veranstaltungen, Uploads, Bestellungen und Benutzer), aber nur,
wenn es dort Aktivität gab. Mehrfaches Speichern desselben Inhalts an einem Tag zählt einmal.
Bei den Benutzern zählen neue Konten und Änderungen durch einen Admin. Jede Zeile wird an ihrem stärksten Tag gemessen. Ein Klick auf einen Tag zeigt die Summen dieses Tages,
ein Klick auf einen Bereich öffnet den Verlauf.

Der Verlauf listet jeden Inhalt pro Tag mit Titel, Typ, Anzahl der Speichervorgänge und letzter Uhrzeit.
Ein Klick auf eine Zeile öffnet den Inhalt. Gelöschte Inhalte erscheinen ohne Link.

Darunter findest du deine Plugins, unter „Meldungen“ alles, was deine Aufmerksamkeit braucht
(zum Beispiel fehlende Seiten für Verwendungstypen),
die neuesten Benutzer sowie Caches, Systeminformationen und OPcache.

Mit dem Recht für sensible Dateien zeigt „Meldungen“ auch Hinweise aus dem Protokoll: CSRF-Fehler angemeldeter Benutzer und
Kontosperren immer, CSRF-Fehler ohne Anmeldung ab 3 Treffern auf demselben Pfad in 7 Tagen (das kann auf ein fehlerhaftes
Formular hindeuten) und falsche Logins ab 10 in 7 Tagen. Das Protokoll nimmt nur eine begrenzte Zahl solcher Einträge pro Stunde auf,
die Zahlen sind deshalb Mindestwerte.

Das vollständige Protokoll öffnest du über den Link „Aktivität“ in der Karte „System“. Dafür brauchst du
das Recht für sensible Dateien. Einträge werden nach 90 Tagen gelöscht.

Tipp: Falls du an einem Theme o.ä. arbeitest und die Option "Smarty Compile Check" nicht aktiviert hast,
kannst du hier auch den Smarty Cache leeren.

### Cache {#cache}

Die Karte „System“ im Dashboard listet alle Caches auf, die SwiftyEdit pflegt, mit ihrer
aktuellen Größe und einem Button zum Leeren (und bei manchen zusätzlich zum Neuaufbauen).
Die meisten davon werden automatisch aktuell gehalten, sobald du Änderungen über die ACP
vornimmst - die manuellen Buttons hier sind vor allem ein Werkzeug zur Fehlerbehebung bzw.
Wiederherstellung, nötig z. B. nach direkten Datenbankänderungen, Massenimporten oder um
eine beschädigte Cache-Datei zu reparieren.

| Cache                        | Was gespeichert wird                                                                 | Wann manuelles Leeren/Neuaufbauen nötig ist                                                                                              |
|-------------------------------|----------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------|
| Template Cache (Smarty)      | Kompilierte Frontend-Theme-Templates (`.tpl`-Dateien unter `public/assets/themes/`).   | Nach direkter Bearbeitung von Theme-Dateien auf dem Server, wenn „Smarty Compile Check“ deaktiviert ist (siehe Tipp oben).                 |
| Template Cache (Twig / ACP)  | Kompilierte Backend-/ACP-Templates (`acp/templates/*.tpl`).                            | Nach direkter Bearbeitung von ACP-Templates auf dem Server. Wird beim eigenen Update-Prozess von SwiftyEdit automatisch geleert, nach einem normalen Core-Update ist also nichts weiter zu tun. |
| Navigation                   | Der Seitenbaum je Sprache, aus dem die Frontend-Menüs gebaut werden.                   | Wird normalerweise automatisch bei jeder Seitenänderung neu aufgebaut. Nur nach einer direkten Datenbankänderung an Seiten nötig.          |
| URL-Pfade                    | Die Liste der aktiven Permalinks, die für das Routing eingehender Anfragen genutzt wird. | Nach einer direkten Datenbankänderung an Seiten-Permalinks.                                                                                |
| Kategorien                   | Alle Kategorien.                                                                        | Wird normalerweise automatisch bei jeder Kategorieänderung neu aufgebaut. Nur nach einer direkten Datenbankänderung an Kategorien nötig.   |
| Tags                         | Alle Schlagwörter.                                                                      | Wird normalerweise automatisch bei jeder Tag-Änderung neu aufgebaut. Nur nach einer direkten Datenbankänderung an Tags nötig.              |
| Snippets                     | Eine Cache-Datei je Snippet (siehe [Snippets](03-00-snippets.md)).                     | Nach einer direkten Datenbankänderung an Snippets.                                                                                          |
| Präferenzen                  | Globale Einstellungen und Branding.                                                     | Wird normalerweise automatisch bei jedem Speichern einer Einstellung neu aufgebaut. Nur nach einer direkten Datenbankänderung an Optionen nötig. |
| Produkte                     | Eine Cache-Datei je Produkt sowie Slug-Maps je Sprache, genutzt vom Shop-Frontend.      | Nach Massenimporten (Preise/Bestand) oder direkten Datenbankänderungen an Produkten. „Leeren“ entfernt veraltete Dateien, „Neu aufbauen“ erzeugt sie wieder aus der Datenbank. |

---

### OPcache {#opcache}

Ist auf deinem Server [OPcache](https://www.php.net/manual/de/book.opcache.php) aktiviert, zeigt dir die
Karte „OPcache“ im Dashboard dessen aktuellen Status: Speicherverbrauch, Anzahl gecachter Dateien im
Verhältnis zum Limit (`opcache.max_accelerated_files`) und die Trefferquote. Ist der Cache voll oder die
Zeitstempel-Prüfung (`opcache.validate_timestamps`) deaktiviert, erscheint zusätzlich ein Hinweis.

Ist die Zeitstempel-Prüfung deaktiviert, erkennt PHP Änderungen an Dateien nicht automatisch - in dem Fall
musst du den Cache nach eigenen Änderungen an PHP-Dateien manuell leeren, damit sie wirksam werden. Das
erledigst du über den Button „Cache leeren“ auf derselben Karte. Bei SwiftyEdits eigenem Update-Prozess
(Core-Updates sowie Plugin-/Theme-Installation über die URL) geschieht das bereits automatisch, dort musst
du nichts weiter tun.

{alert:info}
Die Karte ist nur für Benutzer mit der Berechtigung „kann sensible Dateien hochladen“ sichtbar.
{/alert}

---

### Kategorien {#categories}

Um deine Website zu strukturieren, kannst du eine unbegrenzte Anzahl von Kategorien erstellen.
[Seiten](02-00-pages.md), [Blogbeiträge](04-00-blog.md), [Produkte](05-00-shop.md) und [Veranstaltungen](06-00-events.md) 
können diesen Kategorien zugeordnet werden.

Die Kategorien sind mehrsprachig. Das bedeutet, dass die Sprache einer Kategorie mit der Sprache 
des zugeordneten Datensatzes übereinstimmen muss.

Werden auf einer Shopseite mehrere Kategorien verwendet, ersetzen die Inhalte der aktuell ausgewählten Kategorie 
die Meta-Angaben (Title, Description, Keywords) der Seite.

#### Eingabefelder

| Field        | Type       | Description                                                                         |
|--------------|------------|-------------------------------------------------------------------------------------|
| Titel        | `Text`     | Der Titel                                                                           |
| Link-Name    | `Text`     | Wird im Frontend als Link angezeigt                                                 |
| Priorität    | `Number`   | Die Priorität ist für die Sortierung der Kategorien verantwortlich                  |
| Sprache      | `Select`   | Falls die Seite mehrere Sprachen unterstützt                                        |
| Thumbnail    | `File`     | Ein oder mehrere Thumbnails.                                                        |
| Beschreibung | `Textarea` | Die Beschreibung (Meta Description)                                                 |
| Keywords     | `Text`     | Schlüsselwörter                                                                     |
| Inhalt       | `wysiwyg`  | Beschreibungstext der Kategorie. Je nach Theme wird dieser im Frontend eingeblendet |

---

## Dateien hochladen & verwalten {#uploads}

Hier kannst du Bilder und Dateien hochladen und die bereits hochgeladenen verwalten.  
Für jede hochgeladene Datei kannst du Informationen speichern.

### Dateien hochladen

Das Upload-Formular öffnest du über den Link unten rechts auf dem Bildschirm.

Mit „Zielordner wählen“ legst du fest, in welchem Ordner dein Upload gespeichert werden soll.  
Wenn du Dateien mit identischen Dateinamen hochlädst, nummeriert SwiftyEdit die Dateien automatisch.  
Die Option „Bestehende überschreiben“ deaktiviert diese Funktion natürlich.

Welche Dateitypen du hochladen kannst, wird unter dem Fenster angezeigt.  
Die Liste kann nur über die Datei `config.php` erweitert oder bearbeitet werden.

SwiftyEdit stellt dir zwei Standardverzeichnisse zur Verfügung: `images` und `files`.  
Innerhalb dieser Verzeichnisse kannst du beliebig viele Unterordner erstellen.