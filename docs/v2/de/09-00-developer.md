---
title: Developer
description: Developer notes and instructions
btn: Developer
group: developer
priority: 200
---

# Notes

Die Datei `config.php` legt sämtliche Konfigurationen fest. Da die Datei bei jedem Update überschrieben wird, 
musst du eine eigene Datei anlegen. Lege dazu einfach eine Datei im Verzeichnis data an: `data/config.php`. 
Du musst hier nur die Werte angeben, die du überschreiben möchtest.

Um E-Mails über das SMTP Protokoll zu senden wird eine Datei `config_smtp.php` benötigt.

Beispiel:
```php
<?php
// data/config_smtp.php
$smtp_port = 587;
$smtp_username = 'admin@example.com';
$smtp_psw = 'example';
$smtp_encryption = 'tls';
```

## Themes

Wie Du eigene Themes erstellst, erfährst Du im Kapitel [Themes](09-01-00-themes.md).

## Plugins

Wie Du eigene Plugins erstellst, erfährst Du im Kapitel [Plugins](09-02-plugins.md).

## Hooks

Wie Du Inhalte mit Hooks manipulierst, erfährst Du im Kapitel [Hooks](09-03-hooks.md).