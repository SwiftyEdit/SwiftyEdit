---
title: Blog - Optionen
description: Sprache, Kategorien, Tags, Veröffentlichung, Status, Kommentare und weitere Optionen eines Eintrags
btn: Optionen
group: backend
priority: 300
---

# Optionen eines Eintrags

<kbd>Backend</kbd> ▶ <kbd>Blog</kbd> ▶ <kbd>Bearbeiten</kbd>

Diese Optionen gibt es bei allen [Beitragstypen](04-01-post-types.md). Die meisten findest Du in der
Seitenleiste rechts neben dem Formular, einige auf dem Tab <kbd>Einstellungen</kbd>.


## Seitenleiste

| Option                 | Standard                       | Kurz gesagt                                                         |
|------------------------|--------------------------------|---------------------------------------------------------------------|
| Sprache                | aktuelle Backend-Sprache       | Auf Blog-Seiten welcher Sprache der Eintrag erscheint               |
| Kategorien             | keine                          | Unter welchen Kategorien der Eintrag gelistet wird                  |
| Tags                   | keine                          | Schlagworte, über die Besucher verwandte Inhalte finden             |
| Veröffentlichungsdatum | jetzt                          | Ab wann der Eintrag sichtbar ist und wie er einsortiert wird        |
| Hide detail page       | aus                            | Eintrag nur in der Liste zeigen, ohne verlinkte Detailseite         |
| Priorität              | 0                              | Reihenfolge innerhalb eines Tages                                   |
| Oben fixiert           | aus                            | Eintrag steht immer ganz oben                                       |
| Status                 | Entwurf                        | Öffentlich oder nur für Administratoren sichtbar                    |
| Kommentare             | Nein                           | Besucher dürfen den Eintrag kommentieren                            |
| Abstimmungen/Reaktionen| aus den Benutzer-Einstellungen | Daumen hoch/runter unter dem Eintrag                                |
| Labels                 | keine                          | Farbige Markierungen zur internen Organisation                      |

### Sprache {#sprache}

Ein Eintrag erscheint nur auf Blog-Seiten, deren Sprache mit der Sprache des Eintrags übereinstimmt.
Hast Du eine mehrsprachige Website, legst Du für jede Sprache eigene Einträge an.

Die Sprache bestimmt außerdem, welche Seite für die URL der Detailseite verwendet wird
(siehe [SEO, URLs & RSS](04-03-post-seo.md#hauptkategorie)).

### Kategorien {#kategorien}

Kategorien legst Du unter <kbd>Kategorien</kbd> an. Ein Eintrag kann beliebig vielen Kategorien
zugeordnet werden. Kategorien werden verwendet:

* **auf der Blog-Seite** - ist eine Seite auf bestimmte Kategorien beschränkt, zeigt sie nur
  Einträge aus diesen Kategorien. Ein Eintrag ohne passende Kategorie erscheint dort nicht.
* **als Navigation** - jede Kategorie hat eine eigene URL unterhalb der Blog-Seite, z.B.
  `/blog/neuigkeiten/`, unter der nur ihre Einträge gelistet werden.
* **in der Liste** - die Kategorien eines Eintrags werden als Links angezeigt.

Kategorien gehören zu einer Sprache. Auf einer Blog-Seite werden nur Kategorien in der Sprache der
Seite angezeigt.

### Tags {#tags}

Tags sind frei wählbare Schlagworte. Du gibst sie durch Komma getrennt ein. Im Frontend werden sie
unter dem Eintrag als Links angezeigt. Ein Klick auf einen Tag führt

* auf die Seite mit der Nutzungsart *Tag-Archiv*, wenn es eine gibt - dort erscheinen alle
  Inhalte (Einträge, Produkte, Events) mit diesem Tag,
* sonst auf die Blog-Seite, gefiltert auf diesen Tag.

Tags sind nicht dasselbe wie die **Keywords** auf dem Tab <kbd>SEO</kbd> - Keywords landen nur im
Meta-Tag `keywords` und sind für Besucher nicht sichtbar.

### Veröffentlichungsdatum {#veroeffentlichungsdatum}

Datum und Uhrzeit, ab der ein Eintrag öffentlich sichtbar ist. Neue Einträge erhalten den aktuellen
Zeitpunkt. Setzt Du ein Datum in der Zukunft, kannst Du Einträge im Voraus schreiben: Sie erscheinen
automatisch, sobald der Zeitpunkt erreicht ist - vorausgesetzt, der Status ist <kbd>Öffentlich</kbd>.

Das Datum wird außerdem

* im Frontend beim Eintrag angezeigt,
* für die [Sortierung](04-00-blog.md#sortierung) verwendet (nur der Tag, nicht die Uhrzeit),
* als Zeitpunkt des Eintrags im [RSS-Feed](04-03-post-seo.md#rss) verwendet.

### Hide detail page {#detailseite}

Aktivierst Du diese Option, wird der Eintrag nur in der Liste angezeigt, der Titel ist nicht
verlinkt. Das ist sinnvoll für kurze Meldungen, die keinen eigenen Artikel brauchen.

Dabei wird automatisch

* der RSS-Feed für diesen Eintrag ausgeschaltet,
* der Eintrag aus der XML-Sitemap herausgenommen.

### Priorität und Oben fixiert {#prioritaet}

Einträge werden nach dem Veröffentlichungsdatum sortiert. Mit der **Priorität** ordnest Du Einträge
desselben Tages: je höher die Zahl, desto weiter oben. Mit **Oben fixiert** steht ein Eintrag vor
allen anderen, unabhängig vom Datum. Sind mehrere Einträge fixiert, gilt für sie untereinander
wieder die normale Sortierung.

Beides kannst Du auch direkt in der Übersicht ändern (Stern und Prioritätsfeld).
Details: [Sortierung im Frontend](04-00-blog.md#sortierung).

### Status {#status}

| Status     | Bedeutung                                                                                             |
|------------|-------------------------------------------------------------------------------------------------------|
| Öffentlich | Der Eintrag ist für alle Besucher sichtbar, sobald das Veröffentlichungsdatum erreicht ist.           |
| Entwurf    | Nur eingeloggte Administratoren sehen den Eintrag, mit dem Hinweis, dass es sich um einen Entwurf handelt. Er erscheint nicht in der Sitemap. |

Neue Einträge sind zunächst Entwürfe.

### Kommentare {#kommentare}

Bei <kbd>Ja</kbd> wird unter der Detailseite des Eintrags der Kommentarbereich angezeigt. Wer
kommentieren darf, ob Kommentare freigegeben werden müssen und wie lange ein Eintrag offen für
Kommentare bleibt, stellst Du unter <kbd>Einstellungen</kbd> ▶ <kbd>User</kbd> ein.

### Abstimmungen/Reaktionen {#votings}

Besucher können einen Eintrag mit Daumen hoch oder runter bewerten.

| Einstellung                | Bedeutung                                                                      |
|----------------------------|--------------------------------------------------------------------------------|
| Deaktiviert                | Es werden keine Abstimmungs-Buttons angezeigt.                                 |
| Für registrierte Benutzer  | Die Buttons werden angezeigt, abstimmen können aber nur eingeloggte Benutzer.  |
| Für alle Benutzer          | Auch Besucher ohne Konto können abstimmen.                                     |

Jeder kann pro Eintrag nur einmal abstimmen. Der Standardwert für neue Einträge kommt aus
<kbd>Einstellungen</kbd> ▶ <kbd>User</kbd>.

### Labels {#labels}

Labels sind farbige Markierungen, die Du unter <kbd>Einstellungen</kbd> ▶ <kbd>Labels</kbd> anlegst
(siehe [Einstellungen](08-00-settings.md#labels)).
Sie dienen der internen Organisation (z.B. *„Überarbeiten“* oder *„Kampagne Herbst“*) und werden in
der Übersicht im Backend angezeigt. Im Frontend erscheinen sie nicht.


## Tab Einstellungen {#tab-einstellungen}

| Feld      | Bedeutung                                                                                                 |
|-----------|-----------------------------------------------------------------------------------------------------------|
| Autor     | Freitext, z.B. Dein Name. Wird im Frontend beim Eintrag angezeigt.                                        |
| Quelle    | Die Quelle des Inhalts, z.B. für übernommene Meldungen. Gibt es nicht bei Video und Link. Das Standard-Theme zeigt die Quelle nicht an. |
| RSS       | Soll der Eintrag in den RSS-Feed aufgenommen werden? Siehe [RSS](04-03-post-seo.md#rss).                  |
| RSS-URL   | Der Link des Eintrags im Feed. Siehe [RSS](04-03-post-seo.md#rss).                                        |


## Tab Addons

Installierte Plugins können hier eigene Felder für Blog-Einträge ergänzen. Ist kein solches Plugin
aktiv, bleibt der Tab leer.
