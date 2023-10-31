---
title: Shop Filter
description: Produkt-Filter für das Frontend
btn: Shop > Filter
group: backend
priority: 430
---

# Produkt-Filter erstellen und verwalten

## Gruppen

Um die Filter zu nutzen, muss zunächst eine Gruppe erstellt werden.
Dieser Gruppe können dann beliebig viele Werte zugeordnet werden.

### Eingabefelder

* __Gruppenname__ gibt der Gruppe den Namen
* __Beschreibung__ erscheint im Frontend als Tooltip
* __Priorität__ Sorgt für die Sortierung bei mehreren Gruppen
* __Type__ Checkbox oder Radio, entscheidet ob der Benutzer mehrere oder nur einen Wert dieser Grupe aktivieren kann.
* __Sprache__ Falls man eine mehrsprachige Website betreibt und Gruppennamen identisch sind
* __Rubriken__ Der Filter wird im Frontend nur angezeigt, wenn er zur Kategorie passt.

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