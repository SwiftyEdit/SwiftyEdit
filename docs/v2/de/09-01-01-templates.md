---
title: Themes - Templates
description: The template system
btn: Templates
group: developer
priority: 200
---

# Die Templates (.tpl Dateien)

Einige Templates werden direkt aus SwiftyEdit inkludiert.
Diese Templates sollten sich also im Ordner `styles/deinTheme/templates/` befinden.
Falls eine Datei in Deinem Theme fehlt oder Du sie bewusst weglässt,
lädt SwiftyEdit die Datei aus dem "default" Theme.

## Die wichtigsten Template Dateien

* __index.tpl__<br>
  Ist das erste Template welches geladen wird.
  Von hier aus kannst du all deine anderen Template-Dateien verknüpfen.
* __404.tpl__<br>
  Wird beim HTTP-Statuscode 404 angezeigt (Seite nicht gefunden)
* __registerform.tpl__<br>
  Enthält das Formular um sich als Benutzer zu registrieren.
* __profile_main.tpl__<br>
  Hier können Benutzer ihr Profil aktualisieren.
* __status_message.tpl__<br>
  Hier werden Systemmeldungen ausgegeben
* __statusbox.tpl__<br>
  Hier werden die Links zum ACP (bei Administatoren) oder zum Profil angezeigt
* __loginbox.tpl__<br>
  Zeigt das Formular zur Anmeldung (kann im ACP deaktiviert werden).
* __searchresults.tpl__<br>
  Zeigt die Suchergebnisse an.
* __password.tpl__<br>
  Das Formular um sein Passwort zurückzusetzen.