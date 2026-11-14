---
title: Blog - SEO, URLs & RSS
description: Metadaten, Slug, Hauptkategorie-Seite, Canonical URL, RSS-Feed und Sitemap von Blog-Einträgen
btn: SEO, URLs & RSS
group: backend
priority: 300
---

# SEO, URLs & RSS

<kbd>Backend</kbd> ▶ <kbd>Blog</kbd> ▶ <kbd>Bearbeiten</kbd> ▶ <kbd>SEO</kbd>

Auf dem Tab <kbd>SEO</kbd> legst Du fest, wie ein Eintrag in Suchmaschinen erscheint und unter
welcher URL er erreichbar ist. Alle Felder sind optional - lässt Du sie leer, erzeugt SwiftyEdit
sinnvolle Werte.


## Metadaten

| Feld         | Verwendung                                        | Wenn leer                                                       |
|--------------|---------------------------------------------------|-----------------------------------------------------------------|
| Titel        | Seitentitel (`<title>`) der Detailseite           | Der Titel des Eintrags                                          |
| Beschreibung | Meta-Description der Detailseite                  | Die Beschreibung (Teaser) des Eintrags ohne HTML                |
| Keywords     | Meta-Tag `keywords` der Detailseite               | Kein Keywords-Tag                                               |

Die Ersatzwerte für Titel und Beschreibung werden beim Speichern eingetragen. Änderst Du später den
Titel oder die Beschreibung des Eintrags, bleiben die Metadaten unverändert - leere die Felder, wenn
sie neu übernommen werden sollen.

Die Keywords werden auch bei der Suche im Backend durchsucht. Mit den [Tags](04-02-post-options.md#tags)
in der Seitenleiste haben sie nichts zu tun.


## Slug {#slug}

Der Slug ist der lesbare Teil der URL, z.B. `mein-erster-eintrag`. Lässt Du das Feld leer, wird er
beim Speichern aus dem Titel gebildet. Die vollständige URL der Detailseite lautet:

```
/<Seite>/<slug>-<ID>.html
```

Weil die ID Teil der URL ist, muss der Slug nicht eindeutig sein.


## Hauptkategorie-Seite {#hauptkategorie}

Ein Eintrag kann auf mehreren Blog-Seiten erscheinen, z.B. auf `/blog/` und auf `/news/`. Mit der
Hauptkategorie-Seite legst Du fest, welche dieser Seiten die maßgebliche ist. Zur Auswahl stehen
alle Seiten, die Blog-Einträge anzeigen.

Mit <kbd>Standard verwenden</kbd> nimmt SwiftyEdit, in dieser Reihenfolge:

1. die Seite mit der Nutzungsart *Posts / Produkte / Events anzeigen* in der Sprache des Eintrags,
2. sonst die erste Blog-Seite in der Sprache des Eintrags, die Nachrichten anzeigt.

Aus der Hauptkategorie-Seite und dem Slug werden die [Canonical URL](#canonical-url) und die
[RSS-URL](#rss) gebildet.


## Canonical URL {#canonical-url}

Die Canonical URL teilt Suchmaschinen mit, welche URL für einen Eintrag die maßgebliche ist - wichtig,
wenn ein Eintrag auch auf anderen Seiten oder in mehreren Kategorien auftaucht.

Ist das Feld leer, bildet SwiftyEdit die Canonical URL beim Speichern aus der
[Hauptkategorie-Seite](#hauptkategorie), dem Slug und der ID, z.B.
`https://example.com/blog/mein-erster-eintrag-12.html`. Solange Du den automatisch gebildeten Wert
nicht änderst, wird er angepasst, wenn Du später den Slug oder die Hauptkategorie-Seite änderst.

Soll die Canonical URL stattdessen woanders hinzeigen - z.B. auf eine eigens dafür gebaute
Landingpage, oder auf die URL, die der Eintrag vor einer Umbenennung hatte - trägst Du diese URL
in das Feld *Canonical URL* ein. Eine selbst eingetragene URL bleibt unverändert. Leerst Du das Feld,
wird beim Speichern wieder der automatische Wert verwendet.


## RSS {#rss}

Auf dem Tab <kbd>Einstellungen</kbd> stellst Du ein, ob ein Eintrag in den RSS-Feed Deiner Website
aufgenommen wird. Der Feed ist unter `/rss.php` (RSS) bzw. `/rss.php?type=atom` (Atom) erreichbar.

| Feld    | Bedeutung                                                                                            |
|---------|------------------------------------------------------------------------------------------------------|
| RSS     | Bei <kbd>Ja</kbd> wird der Eintrag beim Speichern in den Feed eingetragen bzw. dort aktualisiert, bei <kbd>Nein</kbd> wird er aus dem Feed entfernt. Standard: <kbd>Nein</kbd>. |
| RSS-URL | Der Link, auf den der Feed-Eintrag zeigt. Wird genauso gebildet und angepasst wie die [Canonical URL](#canonical-url). |

Im Feed erscheinen Titel und Beschreibung des Eintrags. Als Zeitpunkt gilt das
[Veröffentlichungsdatum](04-02-post-options.md#veroeffentlichungsdatum): Ein Eintrag mit einem Datum in
der Zukunft erscheint erst danach im Feed. Zusätzlich kannst Du unter <kbd>Einstellungen</kbd> ▶
<kbd>Allgemein</kbd> einen *RSS-Zeitversatz* festlegen, um Einträge nach dem Speichern noch eine
Weile zurückzuhalten.

Gut zu wissen:

* Feed-Einträge, die älter als 30 Tage sind, werden automatisch entfernt.
* Wird der Eintrag gelöscht, verschwindet er sofort aus dem Feed.
* Mit [Hide detail page](04-02-post-options.md#detailseite) wird RSS automatisch ausgeschaltet.


## XML-Sitemap

Bei jedem Speichern und Löschen wird die Sitemap der Blog-Einträge (`/sitemap-posts.xml`)
neu erzeugt. Aufgenommen werden alle öffentlichen Einträge, außer

* Einträgen vom Typ *Link* (sie haben keinen eigenen Inhalt),
* Einträgen mit [Hide detail page](04-02-post-options.md#detailseite).

Als URL wird die Canonical URL verwendet, sofern sie auf Deine eigene Website zeigt.
