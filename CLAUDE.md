# CLAUDE.md

Guidance for working in the SwiftyEdit codebase.

## What this is

SwiftyEdit is a lightweight, open-source CMS (websites, blog, shop, events) written in
PHP. Version 2.0.0. License GPL-3.0-or-later.

- **PHP 8.3+** required.
- **Database:** MySQL 5.6+ **or** SQLite (accessed via [Medoo](https://medoo.in/)).
- **Web server:** Apache with `mod_rewrite` and PDO.
- Since v2, the **domain root must be `/public/`**.

## Conventions

- **Code comments must be written in English** (even though project discussion may be in German).
- Global functions are prefixed **`se_`** (e.g. `se_get_content`, `se_add_hook`, `se_get_preferences`).
- Two template engines are in use:
  - **Frontend → Smarty** (`.tpl` in `public/assets/themes/<theme>/`).
  - **Backend/ACP → Twig** (`acp/templates/*.tpl`, loaded in `acp/header.php`).
- Output must be sanitized: **HTMLPurifier** is used in the backend; see `app/functions/functions.sanitizer.php`.
- Every `$_POST` action is CSRF-protected via `se_generate_token()` / `se_validate_token()`.
  The hidden field is `$hidden_csrf_token` (`name="csrf_token"`).
- **HTMX** is used extensively across frontend and backend for partial updates
  (e.g. `hx-swap-oob` for out-of-band nav-state updates, `hx-trigger="load"` for
  lazy content, cross-listener bridging between `htmx:afterOnLoad` events for
  things like anchor scrolling inside modals). Prefer HTMX request/swap patterns
  over full page reloads or hand-rolled fetch/AJAX when extending existing UI.

## Working style

- Prefer **minimal, surgical changes** over large refactors unless explicitly asked.
- Favor **readable, straightforward code** over clever/compact solutions.
- Prefer **self-contained solutions** over adding new external dependencies.
- **Only git-tracked plugins/themes are real.** Any plugin under `plugins/<name>/`
  or theme under `public/assets/themes/<name>/` that is not committed to git is a
  local test/experiment only — not part of the actual project. Ignore its contents
  when reasoning about the codebase, and do not treat it as a dependency or an
  officially supported feature unless it's tracked in git.

## Layout

```
public/          Web root (domain root since v2)
  index.php      Frontend entry → app/app.php
  admin.php      Backend entry → acp/index.php
  admin_xhr.php  Backend XHR/AJAX entry
  install.php    Installer
  assets/        themes/, editors/, images, files, uploads
app/             Frontend application
  app.php        Main frontend controller (require order: bootstrap → routing → smarty → handlers)
  bootstrap.php  Session, config, DB connect, preferences, language, CSRF
  routing.php    Parses ?query= (from .htaccess rewrite) into $swifty_slug / $requestPathParts
  database.php   Medoo connections: $db_content, $db_user, $db_posts
  smarty.php     Smarty init
  handlers/      Page-type handlers: shop (products/checkout/orders), posts, events, account, etc.
  xhr/           Frontend AJAX endpoints (login, comments, votes, search, ...)
  functions/     se_* function library (get_content, navigation, pages, posts, shop, user, snippets, sanitizer)
  hooks/         Hook system (see below)
acp/             Admin Control Panel (backend)
  index.php      Backend main file
  core/          Feature modules: pages, blog, shop, events, users, settings, snippets, uploads, addons, ...
  templates/     Twig templates (Bootstrap form/layout partials)
plugins/         Bundled + user plugins (see below)
data/            SE_CONTENT: SQLite DBs, config overrides, uploads. Runtime/user data.
languages/       Language packs (en, de, ...)
docs/v2/{de,en}/ User & developer documentation (Markdown)
tests/           Playwright e2e tests
install/         Installer assets, incl. .htaccess template
config.php       Default config — REPLACED on every update. Never put site-specific values here.
```

## Configuration

- `config.php` holds defaults and **is overwritten on update**.
- Site-specific overrides go in **`data/config.php`** (`SE_CONTENT/config.php`), which is
  included at the end of `config.php`. SMTP overrides go in `SE_CONTENT/config_smtp.php`.
- Key path constants: `SE_ROOT`, `SE_CONTENT` (=`data/`), `SE_PUBLIC`, `SE_PLUGINS`, `SE_THEMES`.
- `$se_settings[...]` — runtime preferences (from DB, built in `bootstrap.php`; `$se_prefs` is the deprecated equivalent).
- `$se_environment` = `'p'` (production) / `'d'` (development).
- `$se_mode` = `self-hosting` / `multisite` — the `multisite` value exists in code but
  is **experimental/unfinished**; do not assume multisite behavior is fully supported
  unless verified in the relevant code path.

## Plugins

Located in `plugins/<name>/`. Each has:
- `info.json` — manifest (`addon` metadata + backend `navigation`).
- `index.php` — entry; `frontend/index.php` for the frontend module of a page.
- Activated plugins come from `se_get_activated_addons()`.

Bundled plugins that ship with releases (see `build_prepare.sh`):
- Payment: `se_cash-pay`, `se_invoice-pay`, `se_paypal-pay`.
- Editors: `tinymce-editor`, `ace-editor`. Editor browser assets are served from
  `public/assets/editors`. Multiple editors are supported with a default fallback.

## Hooks

Defined in `app/hooks/hooks.php`. Scope is `'frontend'` or `'backend'`.

- `se_add_hook($scope, $hookName, $callback)` — register.
- `se_do_hook($scope, $hookName, $context)` / `se_do_frontend_hook(...)` — fire an action.
- `se_apply_filters($scope, $hookName, $value, $context)` — filter a value through callbacks.

Example: `app.php` fires `page.display.after` after rendering.

## Running & tooling

- **PHP CLI:** `/Applications/MAMP/bin/php/php8.3.14/bin/php` (local dev also has 8.4.17 via `dev.env` `PHP_BIN`).
- **Local URLs / test credentials:** `secrets.json` (admin + frontend URLs, login).
- **Frontend theme builds:** Vite (migrated from Webpack). Key patterns:
  - `rollupOptions` with ES module format.
  - Custom `copyAssets()` plugin (using `fs-extra`) for copying font/static assets.
  - `loadPaths` for resolving SCSS imports from `node_modules`.
  - Do **not** use `vite-plugin-sass-glob-import` — incompatible with Vite 6.
- **Tests (Playwright e2e):**
  - `npm test` (= `npx playwright test`). Specs in `tests/e2e/{admin,frontend}/`, helpers in `tests/helpers/`.
  - First run: `npx playwright install`.
- **Build a release:** `composer build` (runs `build_prepare.sh` → `dist/<build>/`; needs `jq`).
- **Composer checks:** `composer check` (audit + validate), `composer outdated`.
- Version info: `version.json`.

## Known gotchas

- **Smarty 5:** the `trim` modifier is missing from `DefaultExtension` and must be
  registered manually as a custom modifier.
- **ParsedownExtra:** only works reliably with Parsedown `1.7.x`. It breaks on
  `1.8.x` — use the `ParsedownExtraPlugin` fork if `1.8.x` compatibility is needed.

## Documentation duty

When adding new `se_get_snippet` usage (or other documented snippet behavior), update the
snippet docs in **both** `docs/v2/de/03-00-snippets.md` and `docs/v2/en/03-00-snippets.md`.
