---
title: Seiten - Editoren
description: Wie du beim Bearbeiten von Seiteninhalten zwischen Editoren wechselst
btn: Editoren
group: backend
priority: 190
---

# Editoren beim Bearbeiten von Inhalten

<kbd>Backend</kbd> ▶ <kbd>Seiten</kbd> ▶ <kbd>Seite bearbeiten</kbd>

Beim Bearbeiten des Inhaltsfelds einer Seite können zwei voneinander unabhängige Dinge
eine Rolle spielen: **welches Eingabe-Werkzeug** du benutzt, und **in welchem Format**
der Inhalt gespeichert wird. Die meiste Zeit siehst du nur das erste – das zweite wird
erst sichtbar, sobald ein zusätzliches Format-Plugin installiert ist.

## Eingabe-Werkzeug: WYSIWYG, Code oder reiner Text

Oben rechts am Inhaltsfeld findest du eine Umschalt-Leiste (z. B. „TinyMCE“, „ACE“,
„Text“). Sie bestimmt nur, mit welchem Werkzeug du gerade tippst – der gespeicherte
Inhalt bleibt in allen drei Fällen ganz normales HTML. Du kannst jederzeit zwischen den
Werkzeugen hin- und herwechseln, ohne dass dabei etwas verloren geht.

## Format wechseln (nur sichtbar, wenn installiert)

Ist zusätzlich zum normalen HTML-Editor mindestens ein **Format-Editor**-Plugin
installiert und aktiviert (z. B. ein Markdown-Editor oder ein Drag-&-Drop-Baukasten),
erscheint neben der Werkzeug-Leiste ein weiteres Dropdown mit dem Namen des jeweiligen
Editors sowie „Legacy (HTML)“.

Damit legst du fest, in welchem *Format* der Inhalt dieser Seite überhaupt vorliegt –
nicht nur, mit welchem Werkzeug du ihn bearbeitest. Ein Format-Editor kann den Inhalt
grundlegend anders speichern und bearbeiten als der normale HTML-Editor (z. B. als
Markdown-Text oder als Baum aus einzelnen Bausteinen).

**Wichtig:** Ein Formatwechsel ist **nicht rückgängig zu machen**, ohne den Inhalt neu
zu schreiben:

- Wechselst du von Legacy-HTML (oder einem anderen Format) zu einem Format-Editor,
  startest du dort mit einem **leeren** Inhalt – der bisherige Text/HTML wird nicht
  automatisch übernommen. Du wirst vorher gefragt, ob du das wirklich willst.
- Wechselst du dagegen **zu** „Legacy (HTML)“, wird dein bisheriger Inhalt als
  fertiges, statisches HTML übernommen – du verlierst also nichts Sichtbares. Danach
  lässt sich die Seite aber nicht mehr mit dem vorherigen Format-Editor weiterbearbeiten,
  sondern nur noch ganz normal als HTML.

Die Seite selbst musst du danach noch **speichern**, damit der neue Zustand dauerhaft
übernommen wird – bis dahin kannst du den Formatwechsel jederzeit rückgängig machen,
indem du die Seite ohne zu speichern verlässt.

## Vollbildmodus

Bei Format-Editoren (z. B. einem Baukasten mit mehreren Spalten) kann die normale
Formularbreite schnell eng werden. Über das Symbol neben dem Format-Dropdown öffnest du
den Editor in einem Vollbild-Fenster – die Bearbeitung selbst ist identisch, es steht
dir nur mehr Platz zur Verfügung.

## Was passiert, wenn ein Format-Editor deinstalliert wird?

Seiten, die mit einem Format-Editor erstellt wurden, bleiben im Frontend unverändert
sichtbar, auch wenn das entsprechende Plugin später deaktiviert oder entfernt wird –
der zuletzt gespeicherte Inhalt wurde bereits als HTML festgehalten. Nur die
**Bearbeitung** dieser Seite mit genau diesem Format-Editor ist dann nicht mehr
möglich, solange das Plugin fehlt. Reaktivierst du das Plugin später wieder, kannst du
normal weiterbearbeiten.

Willst du eine Seite dauerhaft vom Format-Editor lösen (z. B. bevor du das Plugin
entfernst), wechsle das Format einfach auf „Legacy (HTML)“ und speichere – siehe oben.
