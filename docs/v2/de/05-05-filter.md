---
title: Shop - Filter
description: Shop Filter
btn: Filter
group: backend
priority: 400
---

# Produkt-Filter erstellen und verwalten

<kbd>Backend</kbd> ▶ <kbd>Shop</kbd> ▶ <kbd>Filter</kbd>

## Gruppen

Um die Filter zu nutzen, muss zunächst eine Gruppe erstellt werden.
Dieser Gruppe können dann beliebig viele Werte zugeordnet werden.

### Eingabefelder

| Feld         | Typ          | Beschreibung                                                                                                                                    |
|--------------|--------------|----------------------------------------------------------------------------------------------------------------------------------------------------|
| Gruppenname  | `Text`       | Gibt der Gruppe den Namen.                                                                                                                        |
| Beschreibung | `Textarea`   | Erscheint im Frontend als Tooltip.                                                                                                                |
| Priorität    | `Text`       | Sorgt für die Sortierung bei mehreren Gruppen.                                                                                                    |
| Type         | `Select`     | Radio, Checkbox oder Range - entscheidet, ob der Benutzer nur einen Wert (Radio), mehrere Werte (Checkbox) oder einen Wertebereich (Range) dieser Gruppe aktivieren kann. |
| Sprache      | `Select`     | Falls man eine mehrsprachige Website betreibt und Gruppennamen identisch sind.                                                                    |
| Rubriken     | `Checkboxes` | Der Filter wird im Frontend nur angezeigt, wenn er zur Kategorie passt.                                                                           |

## Werte

Die __Werte__ sind die eigentlichen Filter. Diese kann man später im Frontend auswählen.
Auch hier steuert das Feld __Priorität__ die Sortierung.

#### Beispiel

| Gruppe | Werte           |
|--------|-----------------|
| Farbe  | rot, blau, gelb |

### Filter einem Produkt zuordnen

Damit im Frontend auch die richtigen Produkte angezeigt werden,
müssen die Filter im Backend bei dem jeweiligen Produkt aktiviert werden.

Dazu öffnet man im Backend das Produkt und klickt in den Tab Filter. Hier werden alle Filter angezeigt und
können aktiviert werden.