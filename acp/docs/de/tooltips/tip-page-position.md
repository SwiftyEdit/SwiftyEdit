---
title: Position
description: Wie man Seiten einfach sortiert
group: tips
priority: 0
---

# Wie man Seiten einfach sortiert

## Einzelne Seite

 - Einzelne Seiten haben keine Sortierfunktion.
 - Sie werden nicht in die Navigation aufgenommen.
 - Das Eingabefeld Position hat hier keinerlei Funktion

## Portal

 - Portal-Seiten könnte man auch als Startseiten bezeichnen.
 - Jede aktivierte bzw. genutzte Sprache sollte eine Portal-Seite haben.
 - Das Eingabefeld Position hat auch hier keinerlei Funktion

## Hauptmenü

 - Hauptmenü-Seiten erstellen, wie der Name schon sagt, einen Eintrag im Hauptmenü
 - Das Feld Position bestimmt, wo die Seite einsortiert wird

## Diese Seite ist eine Unterseite von …

 - Hier kann eine Seite (als Unterseite) in das Menü eingefügt werden.
 - Der Wert aus dem Feld Position, wird automatisch an die "Eltern-Seite" angehängt.

### Beispiel Sortierung

| Sortierung | Seite |
| ---------- | ----- |
| 100 | Startseite |
| 200 | Produkte |
| 200.100 | Äpfel |
| 200.200 | Birnen |
| 300 | Kontakt |


#### Ergebnis:

- Startseite
- Produkte
    - Äpfel
    - Birnen
- Kontakt

```html
<ul>
    <li>Startseite</li>
    <li>Produkte
        <ul>
            <li>Äpfel</li>
            <li>Birnen</li>
        </ul>
    </li>
    <li>Kontakt</li>
</ul>
```