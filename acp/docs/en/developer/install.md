---
title: Installation
description: Install SwiftyEdit in less than 5 minutes
btn: Installation
group: developer
priority: 100
---

# Technical requirements

* PHP 8.1+
* enabled PDO/SQLite module - PDO/SQLite is usually enabled by default. If not you have to ask your web host.
* MySQL (recommended) if you don't want to run the whole installation on SQLite.

## Installing SwiftyEdit

The installation takes only a few minutes.

Installation via Composer:

`composer create-project swiftyedit/swiftyedit`

Or download the files from the website: https://swiftyedit.org/de/download/

### The installation

1. Copy all files to the server.
2. In the web browser, go to the `/install/` directory and follow the instructions ...

If the installation was successful, the next step is to go to the ACP.
Just click on <kbd>Administration</kbd>.
By the way, you can always reach the backend via the URL `example.com/acp/`.