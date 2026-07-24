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
    - global [d] (optional)
        - index.php (loaded as soon as the plugin is active)
        - xhr.php (handles XHR requests under `/xhr/plugins/{plugin}/`)
    - backend [d] (optional)
        - page-values.php / product-values.php / post-values.php (optional, adds fields
          to the "Addons" tab of the Page/Product/Post editor, see below)
    - frontend [d] (optional)
        - index.php (page-module plugins only, replaces the page content)
    - hooks-backend [d] (optional)
    - hooks-frontend [d] (optional)
    - lang [d] (optional)
    - data [d] (optional, never overwritten on update)
    - aftersale.php
    - info.json
    - poster.png (optional)
    - readme.md

### Tips

- if you name your plugin with the suffix `-pay` (e.g. `my-gateway-pay`), it is
  automatically recognized as a payment plugin. This makes the `aftersale.php` file mandatory.
- XHR requests are handled by the `global/xhr.php` file, which is reachable
  via `/xhr/plugins/{plugin}/`.

### Activated plugins

Plugins are considered active if they have been integrated into any page
or have been manually activated in the backend.

A plugin must be activated so that it

- can process XHR requests in the frontend
- hooks can be executed in the frontend
- can show its own fields in the "Addons" tab of the Page/Product/Post editor
  (`{page|product|post}-values.php`, see below)

### When are which plugin files loaded?

The following includes are possible:

1. `/plugins/{plugin}/index.php`
2. `/plugins/{plugin}/frontend/index.php`
3. `/plugins/{plugin}/global/index.php`
4. `/plugins/{plugin}/global/xhr.php`
5. `/plugins/{plugin}/backend/{page|product|post}-values.php`

---

1. When a plugin is integrated via shortcode: `[plugin={plugin}]foo=bar[/plugin]`
2. When a plugin is integrated within a page, i.e., activated in the plugin tab,
   the page content is replaced by the plugin in the frontend.
3. When a plugin is active. This means that it has either been integrated into a page via the plugin tab
   or activated manually.
4. When a plugin is supposed to process XHR requests in the frontend.
   The plugin must be activated for this. The correct route is `/xhr/plugins/{plugin}/`
5. When the Page, Product or Post editor renders its "Addons" tab - once for every
   plugin that is activated in the backend (see below).

### Addon fields in the Page/Product/Post editor

A plugin can add its own input fields to the "Addons" tab of the Page, Product and Post
editors by providing one of these files:

- `/plugins/{plugin}/backend/page-values.php`
- `/plugins/{plugin}/backend/product-values.php`
- `/plugins/{plugin}/backend/post-values.php`

These files are only loaded for plugins that are **activated in the backend** - being
integrated into a page is not enough.

Each file receives a `$record_data` array (the current record's database row) and must
set a `$plugin_form_tpl` string containing the HTML for the plugin's own fields:

```php
<?php
// plugins/my-plugin/backend/page-values.php

$values = json_decode($record_data['addon_string'], true) ?: [];

$plugin_form_tpl = '<div class="mb-1">';
$plugin_form_tpl .= '<label>My Field</label>';
$plugin_form_tpl .= '<input type="text" class="form-control" name="addon_values[my_field]" value="'
    . htmlspecialchars($values['my_field'] ?? '') . '">';
$plugin_form_tpl .= '</div>';
```

- Name your inputs `addon_values[key]` for single values, or `addon_values[key][]` for
  multi-value fields (checkboxes, multi-select).
- SwiftyEdit automatically prefixes every field name with your plugin's folder name
  before saving, and strips that prefix again before handing `$record_data['addon_string']`
  to your file - so you don't need to worry about field-name collisions with other
  plugins that add addon fields to the same record.
- The submitted values are stored as JSON in the record's `addon_string` column
  (`se_pages`, `se_products` or `se_posts`).

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

> **Note:** Most fields are optional. In practice, the shipped plugins get by with a
> minimal set: `name`, `version`, `author`, `description` and `navigation`. The fields
> `id`, `type`, `build`, `versions[]`, `update_url` and `requires_build` are only needed
> for the automatic update-check feature.

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

#### editor

Optional. Marks a plugin as an editor plugin (`addon.type` must be `editor`). There
are two kinds of editor plugins:

- **WYSIWYG/code editors** (`mode: "wysiwyg"` or `mode: "code"`, e.g. TinyMCE, ACE):
  only provide a different input widget for a `<textarea>`. The stored value is
  always raw HTML, regardless of which editor was chosen.
- **Content-format editors** (`mode: "format"`, e.g. a drag & drop page builder or a
  Markdown editor): determine the *format* of the stored value itself. Content is
  stored as `{"editor": "<id>", "content": ...}` in the content field (e.g.
  `page_content`) and delegated to the responsible plugin on load via
  `se_register_editor()` (see `app/functions/functions.editors.php`). If no plugin
  with a matching `editor.id` is active, SwiftyEdit falls back to displaying the raw
  text.

| Field | Required | Description |
|---|---|---|
| `id` | yes | Unique editor key. For `mode: "format"`, this value is referenced verbatim in the stored JSON. |
| `label` | yes | Display name in the editor switch / format selector |
| `mode` | yes | `wysiwyg`, `code`, or `format` |
| `order` | yes | Sort order within the editor list (ascending) |
| `core` | optional | `true` marks the editor as always active (bypasses plugin activation). Intended for bundled editors like TinyMCE/ACE only. |

### Replacing an editor plugin

A content-format editor plugin is tied to already-saved pages through its
`editor.id` value, not through the plugin's folder name. To replace a content-format
editor with a new implementation while keeping previously saved pages editable:

1. Deactivate the old plugin in the backend (otherwise both plugins register the
   same key, and it's undefined which one wins depending on load order).
2. Give the new plugin the same `editor.id` value in its `info.json` that the old
   plugin used.
3. Activate the new plugin.

Every page whose content field contains `"editor": "<id>"` will now be rendered and
edited by the new plugin - no data migration required.

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