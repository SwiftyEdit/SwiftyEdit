---
title: Blog - Beitragstypen
description: Die sechs Arten von Blog-Einträgen und welche Felder sie haben
btn: Beitragstypen
group: backend
priority: 300
---

# Beitragstypen

<kbd>Backend</kbd> ▶ <kbd>Blog</kbd> ▶ <kbd>Neu</kbd>

Jeder Blog-Eintrag hat einen von sechs Typen. Der Typ bestimmt, welche Felder das Formular hat und
wie der Eintrag im Frontend dargestellt wird. Er wird beim ersten Speichern festgelegt.

| Typ        | Wofür                                        | Eigene Felder                              | Detailseite in der Sitemap |
|------------|----------------------------------------------|--------------------------------------------|----------------------------|
| Nachricht  | Klassischer Blogartikel mit Intro und Text   | Text (Tab Inhalt)                          | ja                         |
| Bild       | Ein einzelnes Bild mit Beschreibung          | -                                          | ja                         |
| Galerie    | Mehrere hochgeladene Bilder                  | Bilder-Upload                              | ja                         |
| Video      | Ein eingebettetes YouTube-Video              | Video-URL                                  | ja                         |
| Link       | Hinweis auf eine externe Webseite            | URL, Linktext                              | nein                       |
| Download   | Eine Datei zum Herunterladen                 | Datei, externe Datei, Version, Lizenz      | ja                         |

Alle Typen teilen sich die Tabs <kbd>SEO</kbd>, <kbd>Einstellungen</kbd>, <kbd>Addons</kbd> und die
Seitenleiste (siehe [Optionen](04-02-post-options.md) und [SEO, URLs & RSS](04-03-post-seo.md)).


## Gemeinsame Felder

Diese Felder kommen bei (fast) allen Typen vor:

### Titel

Die Überschrift des Eintrags - in der Liste und auf der Detailseite. Aus dem Titel wird beim ersten
Speichern automatisch der [Slug](04-03-post-seo.md#slug) gebildet, wenn Du keinen eigenen festlegst.
Ist kein Meta-Titel eingetragen, wird der Titel auch als Seitentitel (`<title>`) verwendet.

### Beschreibung

Der Einleitungstext (Teaser), mit dem Editor formatierbar. Er wird in der Liste angezeigt und steht
auf der Detailseite über dem eigentlichen Inhalt. Bei allen Typen außer *Nachricht* und *Download*
ist die Beschreibung der einzige Text des Eintrags.

Ist auf dem Tab <kbd>SEO</kbd> keine Meta-Description eingetragen, wird die Beschreibung (ohne
HTML) dafür verwendet.

### Bilder

Über die Bildauswahl wählst Du Bilder aus Deinen Uploads. Verwendet wird das **erste** ausgewählte
Bild:

* als Vorschaubild in der Liste im Frontend und im Backend,
* auf der Detailseite (bei *Nachricht* und *Bild*),
* als Vorschaubild beim Teilen in sozialen Netzwerken (`og:image`).

Hat ein Eintrag kein Bild, wird beim Teilen das Vorschaubild der Seite bzw. das globale
Vorschaubild verwendet. Die Galerie hat keine Bildauswahl, sie nutzt ihre hochgeladenen Bilder.


## Nachricht {#message}

Der Standard-Typ für Artikel. Das Formular hat zwei Tabs:

* <kbd>Intro</kbd> - Titel, Beschreibung und Bilder.
* <kbd>Inhalt</kbd> - der eigentliche Text des Artikels.

In der Liste erscheinen Bild, Titel und Beschreibung mit einem Button zum Weiterlesen. Auf der
Detailseite werden Bild, Beschreibung und Text nacheinander angezeigt.


## Bild {#image}

Für einen Eintrag, bei dem ein Bild im Mittelpunkt steht. Es gibt nur den Tab <kbd>Inhalt</kbd> mit
Titel, Beschreibung und Bildauswahl. Das erste Bild wird in der Liste und auf der Detailseite groß
zusammen mit der Beschreibung angezeigt.


## Galerie {#gallery}

Für eine Sammlung von Bildern. Anders als bei den übrigen Typen wählst Du die Bilder nicht aus,
sondern lädst sie direkt in die Galerie hoch.

1. Trage Titel und Beschreibung ein und **speichere** den Eintrag. Erst danach ist der Button
   <kbd>Hochladen</kbd> aktiv, weil die Bilder der ID der Galerie zugeordnet werden.
2. Lade die Bilder über <kbd>Hochladen</kbd> hoch (mehrere auf einmal sind möglich).
3. Die hochgeladenen Bilder erscheinen unter *Uploads*. Dort kannst Du ihre Reihenfolge ändern und
   einzelne Bilder löschen.

Beim Hochladen wird jedes Bild auf die maximale Bild- und Thumbnail-Größe verkleinert, die unter
<kbd>Einstellungen</kbd> ▶ <kbd>Allgemein</kbd> eingestellt ist. Gespeichert werden die Bilder unter
`/assets/galleries/<Jahr>/gallery<ID>/` - das Jahr ist das Erstellungsjahr des Eintrags.

Im Frontend zeigt die Liste die ersten fünf Thumbnails und einen Button wie *„12 Bilder anzeigen“*.
Die Detailseite zeigt alle Bilder der Galerie.

Wird die Galerie gelöscht, werden auch alle ihre Bilder gelöscht.


## Video {#video}

Für ein YouTube-Video. Auf dem Tab <kbd>Inhalt</kbd> gibt es zusätzlich das Feld **URL (YouTube)**.

Trage einfach die Adresse des Videos ein, wie Du sie bei YouTube kopierst. Diese Formen werden erkannt:

* `https://www.youtube.com/watch?v=VIDEO-ID`
* `https://youtu.be/VIDEO-ID`
* `https://www.youtube.com/embed/VIDEO-ID`
* `https://www.youtube.com/shorts/VIDEO-ID`

Aus der Adresse wird die Video-ID gelesen und das Video in der Liste und auf der Detailseite
eingebettet. Wird keine gültige ID gefunden, bleibt der Video-Player leer.

Das ausgewählte Bild dient als Vorschaubild, z.B. beim Teilen. Videos haben kein Feld *Quelle*.


## Link {#link}

Für einen Hinweis auf eine andere Webseite. Auf dem Tab <kbd>Inhalt</kbd> gibt es zusätzlich:

* **URL** - die Zieladresse des Links.
* **Text** - die Beschriftung des Link-Buttons, z.B. *„Zum Artikel“*.

Besucher werden nicht direkt, sondern über eine Weiterleitung von SwiftyEdit zum Ziel geschickt.
Dabei wird jeder Klick gezählt; die Anzahl siehst Du in der Übersicht im Backend.
Link-Einträge werden nicht in die XML-Sitemap aufgenommen und haben kein Feld *Quelle*.


## Download {#file}

Für eine Datei, die Besucher herunterladen können. Neben <kbd>Intro</kbd> und <kbd>Inhalt</kbd>
(wie bei der Nachricht) gibt es den Tab <kbd>Dateien</kbd>:

| Feld              | Bedeutung                                                                                                     |
|-------------------|---------------------------------------------------------------------------------------------------------------|
| Datei auswählen   | Eine Datei aus Deinen Uploads (Verzeichnis `/assets/files/`). Angeboten werden nur erlaubte Dateitypen.         |
| Version           | Wird auf dem Download-Button angezeigt, z.B. *„Download 1.2.0“*.                                               |
| Externe Datei     | Die vollständige URL einer Datei auf einem anderen Server. Ist das Feld ausgefüllt, hat es Vorrang vor der ausgewählten Datei. |
| Lizenz            | Ein Hinweis zur Lizenz, wird unter dem Download-Button angezeigt.                                             |

Jeder Download wird gezählt - auch bei externen Dateien, die über SwiftyEdit weitergeleitet werden.
Die Anzahl siehst Du in der Übersicht im Backend.

Die Datei musst Du vorher über <kbd>Uploads</kbd> hochladen, damit sie in der Auswahl erscheint.

In der Liste erscheinen Beschreibung und Download-Button. Die Detailseite zeigt zusätzlich den Text
vom Tab <kbd>Inhalt</kbd>, z.B. für eine ausführliche Anleitung oder ein Changelog.
