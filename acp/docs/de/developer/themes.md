---
title: Themes
description: Das Templatesystem
btn: Themes
group: developer
priority: 200
---

# Das Templatesystem
Als Templatesystem kommt smarty zum Einsatz. 
Wer ein eigenes Template erstellen möchte, sollte sich also zunächst mit der smarty Dokumentation 
vertraut machen.

## Besonderheiten
Ein Template ist nicht nur für das Aussehen Deiner Website verantwortlich, 
sondern kann auch die Optik bzw. die Funktionen des Editors tinyMCE steuern. 
Damit kannst Du erreichen, dass sich die WYSIWYG Ansicht so nah als möglich am Original-Layout 
(also dem Frontend) orientiert.

__Zwei Dateien sind dafür maßgeblich verantwortlich:__

* styles/deinTemplate/css/editor.css
* styles/deinTemplate/js/tinyMCE_config.js

Die CSS Datei ist für das Aussehen der Inhalte im Editor verantwortlich. 
Dies ist der einfachste Weg, den Editor an Dein Layout anzupassen. 
Die JavaScript Datei ist für die Konfiguration zuständig. 
Weitere Informationen findest Du in der tinyMCE Dokumentation.