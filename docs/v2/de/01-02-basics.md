---
title: Basics
description: SwiftyEdit basics
btn: Basics
group: administrators
priority: 100
---

# Dashboard {#dashboard}

Hier findest du auf einen Blick sämtliche Informationen zu deiner Internetseite.

Auf der Linken Seite werden die neuesten Einträge aufgelistet.
Du kannst direkt auf die einzelnen Einträge klicken, um diese zu bearbeiten.
Außerdem kannst du direkt neue Einträge erstellen.

Auf der rechten Seite findest Du ein Protokoll, aktuelle Warnungen und Informationen zur verwendeten Software.

Tipp: Falls du an einem Theme o.ä. arbeitest und die Option "Smarty Compile Check" nicht aktiviert hast,
kannst du hier auch den Smarty Cache leeren.

---

### Kategorien {#categories}

Um deine Website zu strukturieren, kannst du eine unbegrenzte Anzahl von Kategorien erstellen.
[Seiten](02-00-pages.md), Blogbeiträge, Produkte und Veranstaltungen können diesen Kategorien zugeordnet werden.

Die Kategorien sind mehrsprachig.
Das bedeutet, dass die Sprache einer Kategorie mit der Sprache des zugeordneten Datensatzes übereinstimmen muss.

#### Eingabefelder

| Field        | Type       | Description                                                               |
|--------------|------------|---------------------------------------------------------------------------|
| Titel        | `Text`     | Category title                                                            |
| Priorität    | `Number`   | The priority (and the language) determines the sorting of the categories. |
| Sprache      | `Select`   | The language of the category                                              |
| Thumbnail    | `File`     | An image for this category can be selected here.                          |
| Beschreibung | `Textarea` | The description of the category                                           |

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