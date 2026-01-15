---
title: Plugins
description: Create or install plugins
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

- if you name your plugin with the präfix `-pay`, it is automatically recognized 
as a payment plugin. This makes the `aftersale.php` file mandatory.
- You can access the xhr.php file via /xhr/plugins/{plugin}/

### Activated plugins

Plugins are considered active if they have been integrated into any page
or have been manually activated in the backend.

A plugin must be activated so that it

- can process XHR requests in the frontend
- hooks can be executed in the frontend

### When are which plugin files loaded?

The following includes are possible::

1. `/plugins/{plugin}/index.php`
2. `/plugins/{plugin}/frontend/index.php`
3. `/plugins/{plugin}/global/index.php`
4. `/plugins/{plugin}/global/xhr.php`


1. When a plugin is integrated via shortcode: `[plugin={plugin}]foo=bar[/plugin]`
2. When a plugin is integrated within a page, i.e., activated in the plugin tab,
   the page content is replaced by the plugin in the frontend.
3. When a plugin is active. This means that it has either been integrated into a page via the plugin tab
   or activated manually.
4. When a plugin is supposed to process XHR requests in the frontend.
   The plugin must be activated for this. The correct route is `/xhr/plugins/{plugin}/`