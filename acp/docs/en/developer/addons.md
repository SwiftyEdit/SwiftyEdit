---
title: Addons
description: Create and/or install addons for SwiftyEdit
btn: Addons
group: developer
priority: 100
---

# SwiftyEdit Addons

There are two types of addons: Plugins and Themes.

- Plugins are stored in the directory `/plugins/`
- Themes are in the directory `/public/assets/themes/`

## The anatomy of a plugin

Differently from SwiftyEdit version 1, all plugins must adhere to a specific folder structure:

- Plugin [d]
  - backend [d]
  - frontend [d] (optional)
    - index.php
  - hooks [d] (optional)
  - lang [d] (optional)
  - aftersale.php
  - info.json
  - poster.png (optional)
  - readme.md

### Tipps

- if you include a plugin in the frontend via [plugin=*] shortcut, the file /frontend/index.php will be executed.
- if you name your plugin with the präfix `-pay`, it is automatically recognized as a payment plugin. This makes the aftersale.php file mandatory.