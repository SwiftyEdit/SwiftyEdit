---
title: Plugins
description: The plugin system
btn: Plugins
group: developer
priority: 200
---

Plugins are stored in the directory `/plugins/`

## The anatomy of a plugin

Differently from SwiftyEdit version 1, all plugins must adhere to a specific folder structure:

- Plugin [d]
    - backend [d] (optional)
    - frontend [d] (optional)
        - index.php
    - hooks-backend [d] (optional)
    - hooks-frontend [d] (optional)
    - lang [d] (optional)
    - aftersale.php
    - info.json
    - poster.png (optional)
    - readme.md

### Tipps

- if you include a plugin in the frontend via [plugin=*] shortcut, the file /plugins/{plugin}/frontend/index.php will be executed.
- if you name your plugin with the präfix `-pay`, it is automatically recognized as a payment plugin. This makes the aftersale.php file mandatory.
- You can access the xhr.php file in your theme via /api/themes/{theme}/