---
title: Developer
description: Developer notes and instructions
btn: Developer
group: developer
priority: 200
---

# Notes

The file `config.php` defines all configurations. Since this file is overwritten on every update,
you have to create your own file. To do so, simply create a file in the `data` directory: `data/config.php`.
Here you only need to specify the values you want to override.

To send e-mails via the SMTP protocol, a file `config_smtp.php` is required.

Example:
```php
<?php
// data/config_smtp.php
$smtp_port = 587;
$smtp_username = 'admin@example.com';
$smtp_psw = 'example';
$smtp_encryption = 'tls';
```

## Themes

How to create your own themes is explained in the [Themes](09-01-00-themes.md) chapter.

## Plugins

How to create your own plugins is explained in the [Plugins](09-02-plugins.md) chapter.

## Hooks

How to manipulate content with hooks is explained in the [Hooks](09-03-hooks.md) chapter.