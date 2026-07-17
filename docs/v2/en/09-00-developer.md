---
title: Developer
description: Developer notes and instructions
btn: Developer
group: developer
priority: 200
---

# Developer

We're glad you're interested in developing with and for SwiftyEdit! This section has everything
you need to build your own themes and plugins, tap into hooks, or contribute to the project
directly.

## Notes

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

A few more small things worth knowing:

* __CSRF protection__ - every `$_POST` action is checked. If you build your own form (e.g. in a
  plugin or a theme's XHR endpoint), you have to include the hidden field
  `<input type="hidden" name="csrf_token" value="...">` (available on the frontend as
  `$hidden_csrf_token`) and validate it server-side with `se_validate_token($_POST['csrf_token'])`
  - otherwise SwiftyEdit redirects to `/`.
* __Environment__ (`$se_environment` in `config.php`) - `'p'` for production (default) or `'d'`
  for development.
* __Operating mode__ (`$se_mode` in `config.php`) - `0` self-hosting (default), `1` self-hosting
  with multisite, `2` provided multisite hosting. Multisite is experimental and not fully
  finished - don't rely on it in your own code without checking the relevant code path yourself.

## Themes

Themes decide how SwiftyEdit looks and feels to your visitors - from the overall layout down to
individual templates and the look of the WYSIWYG editor. The [Themes](09-01-00-themes.md)
chapter walks you through building your own theme, shipping multiple layouts, and how much (or
how little) you can inherit from the `default` theme along the way.

## Plugins

Plugins are your tool for extending SwiftyEdit with your own functionality without touching the
core - anything from a small backend page to a whole new frontend module. The
[Plugins](09-02-plugins.md) chapter shows you how one is structured and how it hooks into the
ACP.

## Hooks

Hooks let you step in at precisely defined moments - reshaping content before it's rendered, or
reacting to events like a page being updated - all without changing core code. The
[Hooks](09-03-hooks.md) chapter covers how it all works.

## Contributing

SwiftyEdit is open source and welcomes contributions. The project is hosted on
[GitHub](https://github.com/SwiftyEdit/SwiftyEdit) - fork the repository, install a dev build with

```bash
composer create-project swiftyedit/swiftyedit=dev-main swiftyedit-dev --stability=dev
```

and open a pull request from a `feature/short-description` or `fix/issue-number` branch. Keep
pull requests small and focused, reference related issues, and update the relevant docs for any
user-facing change. Questions are best asked as a GitHub Discussion or Issue; issues labeled
`good-first-issue` are a good place to start.

The full guidelines (code style, workflow details) live in `CONTRIBUTING.md` at the repository
root. All contributors are expected to follow `CODE_OF_CONDUCT.md` next to it.

## License

SwiftyEdit is licensed under the __GNU General Public License v3.0 or later
(GPL-3.0-or-later)__. The full license text is in `license.txt` at the repository root. In
short: you're free to use, study, modify and redistribute SwiftyEdit, but any distributed
derivative work must also be licensed under the GPL, with its source made available.

Bundled third-party libraries (e.g. the editors in `public/assets/editors/`, or a theme's npm
dependencies listed in its `package.json`) keep their own, separate licenses - check the
relevant library's license before redistributing it as part of a theme or plugin.