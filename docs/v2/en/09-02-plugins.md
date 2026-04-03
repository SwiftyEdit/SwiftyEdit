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
    - data [d] (optional, never overwritten on update)
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

---

1. When a plugin is integrated via shortcode: `[plugin={plugin}]foo=bar[/plugin]`
2. When a plugin is integrated within a page, i.e., activated in the plugin tab,
   the page content is replaced by the plugin in the frontend.
3. When a plugin is active. This means that it has either been integrated into a page via the plugin tab
   or activated manually.
4. When a plugin is supposed to process XHR requests in the frontend.
   The plugin must be activated for this. The correct route is `/xhr/plugins/{plugin}/`

## The info.json file

Every plugin must have an `info.json` file in its root directory. This file contains
metadata about the plugin and is used by SwiftyEdit to display plugin information
in the backend and to manage updates.

### Basic structure
```json
{
  "addon": {
    "id": "my-plugin",
    "type": "plugin",
    "name": "My Plugin",
    "version": "1.0",
    "build": 1,
    "author": "Your Name",
    "description": "A short description of your plugin",
    "update_url": "https://your-server.com/plugins/my-plugin/info.json"
  },
  "versions": [
    {
      "version": "1.0",
      "build": 1,
      "requires_build": "25-145",
      "download_url": "https://your-server.com/plugins/my-plugin/my-plugin-1.zip"
    }
  ],
  "navigation": [
    {
      "text": "nav_overview",
      "file": "start"
    }
  ]
}
```

### Fields

#### addon

| Field | Required | Description |
|---|---|---|
| `id` | recommended | Unique identifier of the plugin. Used as folder name. If omitted, derived from the URL. |
| `type` | yes | Must be `plugin` |
| `name` | yes | Display name of the plugin |
| `version` | yes | Current version, human readable (e.g. `1.0`) |
| `build` | yes | Current build number. Used by SwiftyEdit to detect updates. |
| `author` | yes | Name of the author |
| `description` | yes | Short description of the plugin |
| `update_url` | optional | URL to the remote `info.json`. Required for automatic update checks. |

#### versions

A list of all available versions, sorted from newest to oldest. SwiftyEdit will
automatically select the most recent version that is compatible with the installed
SwiftyEdit build.

| Field | Required | Description |
|---|---|---|
| `version` | yes | Version number, human readable |
| `build` | yes | Build number of this version |
| `requires_build` | yes | Minimum SwiftyEdit build required (e.g. `25-145`) |
| `download_url` | yes | URL to the ZIP file of this version |

#### navigation

Defines the navigation items shown in the plugin backend. Each entry creates
a menu item that loads the corresponding PHP file from the `/backend/` directory.

| Field | Required | Description |
|---|---|---|
| `text` | yes | Language key for the navigation label |
| `file` | yes | Filename without `.php` extension, loaded from `/backend/` |

### Updates

SwiftyEdit automatically checks for updates when visiting `/backend/addons/`.
Plugins that have a valid `update_url` and `build` defined in their `info.json`
will be checked against the remote version. If an update is available, an update
button will appear next to the plugin.

The `/data/` directory inside a plugin is never overwritten during an update,
making it safe to store user-generated content there.

### Allowed file types in ZIP

When installing or updating a plugin via URL, SwiftyEdit validates the contents
of the ZIP file. Only the following file types are allowed:

`php`, `tpl`, `json`, `js`, `css`, `html`, `svg`, `png`, `jpg`, `jpeg`, `gif`, `webp`, `txt`, `md`, `sqlite3`

### Hosting your plugin

You can host your plugin on any server, including GitHub. The only requirement
is that the `info.json` and all ZIP files are publicly accessible via HTTPS.
If you want your plugin to be listed in the official plugin directory on
SwiftyEdit.com, you can submit it there.