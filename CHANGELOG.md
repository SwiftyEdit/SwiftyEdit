# Changelog

All notable changes to SwiftyEdit are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Versions are identified by version number and build (see `version.json`).

Security fixes are listed in the release they ship with, not in advance.
See [SECURITY.md](SECURITY.md) for how to report a vulnerability.

## 2.1.0 - 2026-10-10 (Build 26-0222)

### Added

- **Update notices:** the update page shows notices from swiftyedit.net (e.g.
  announcements of changes in upcoming versions). They come with the
  version information that the update page loads anyway - no additional
  request.
- **Two-factor authentication for the backend** (disabled by default). With
  `$se_2fa_required = true;` in `data/config.php`, every backend user has to
  confirm the login with a second factor - a code by e-mail or from an
  authenticator app (TOTP, e.g. Aegis, 2FAS, Google or Microsoft
  Authenticator). The setup runs at the next login (QR code generated
  locally, no external service) and ends with 10 one-time recovery codes. For emergencies,
  `$se_2fa_bypass` skips 2FA for one user until a given date (see
  `config.php`).
  - "Trust this device for 30 days" after the second factor - the password
    is still required. Trusted devices are listed in the personal settings
    and can be removed there; a new password or a new 2FA setup removes all.
  - Every backend user manages their own 2FA in the personal settings (user
    menu → Settings): set it up, change the method, create new recovery
    codes, or disable it while 2FA is not required.
  - In the user management, administrators with the user right see who has
    2FA set up and can reset it for other users.
  - The dashboard recommends enabling 2FA (can be hidden) and warns while
    the emergency switch is set.

### Changed

- **Themes - templates changed:** custom themes that ship their own copy of
  one of these templates have to update it, otherwise the function breaks.
  Themes without an own copy use the updated templates of the `default`
  theme automatically.
  - `password.tpl` - the link from the reset e-mail now opens a form for the
    new password (variables `reset_token`, `reset_done`, fields `new_psw`,
    `new_psw_repeat`, `reset_token`, button `set_new_psw`). With an old copy,
    passwords can't be reset anymore.
  - `profile/change-password.tpl` - new field `s_psw_current` (current
    password). With an old copy, changing the password in the profile always
    fails.
  - `registerform.tpl` - the live check of the password repeat uses
    `hx-post` and includes `csrf_token`; passwords are no longer filled back
    into the form (`send_psw`, `send_psw_repeat` are gone). With an old copy,
    the live check stays silent; the registration itself still works.

  Compare your copies with the templates of the `default` theme.
- **Comments:** the comment mode setting is now respected. With "All comments
  must be approved by an admin", new comments wait for approval in the inbox
  instead of appearing immediately. With "Deactivate the comment function",
  no new comments are accepted.
- **Password reset:** the link in the reset e-mail opens a form where users
  choose a new password themselves. No password is sent by e-mail anymore;
  afterwards users get a short confirmation e-mail. Reset links are valid for
  one hour and can be used once. Custom `mail_psw_updated` snippets that
  contain `{temp_psw}` should be updated - the placeholder stays empty now.
- **Profile:** changing the password requires the current password.
- **Login:** after too many failed attempts (`$se_failed_logins_limit`), an
  account is locked temporarily (1 minute, doubling up to 60 minutes) instead
  of until the unlock link is used. The unlock link still lifts the lock
  right away. Repeated failed logins from the same client are throttled.
- **Login:** administrator rights are only granted by the backend login
  (`/admin/`). Administrators who log in through the frontend get a regular
  user session (moderation rights are kept) - for draft previews, private
  pages and the edit helpers in the frontend, log in via `/admin/`.
- **Backend session:** the session lifetime from the settings is now also
  enforced on the server, not only by the countdown in the browser.
- **Addons:** with `$se_upload_addons = false`, updating addons from the
  backend is disabled as well, just like installing them. The addon list
  shows that an update is available.
- **Plugins:** backend writers (`/admin-xhr/<module>/write/`) only accept POST
  requests with a valid CSRF token.
- **Plugins:** the output of `[plugin=...]` and `[script]` is no longer parsed
  for `[image=]`, `[file=]` and `[mod=]` shortcodes.
- **Votes:** anonymous votes are identified differently. Visitors who voted
  anonymously before the update can vote once more.
- **Images:** uploaded raster images are limited to 50 megapixels.
- **Default theme:** jQuery and js-cookie are no longer included. Only htmx is
  loaded by the theme's core script - custom templates based on the default
  theme that use jQuery have to load it themselves.
- **Backend help:** front matter of the help pages is parsed with
  `symfony/yaml` instead of `mustangostang/spyc`. A broken front matter no
  longer breaks the page.
- **Dependencies:** `erusev/parsedown` updated to 1.8 and
  `erusev/parsedown-extra` to 0.9 (they have to stay paired).
- **Backend:** the plugin and theme lists and the addon catalog show a loading
  indicator while their content is loaded.
- **Update:** new order on the update page - notices from swiftyedit.net come
  first, the downloaded files follow directly below the channels. The result
  of a download and the installation protocol are shown in the same box, right
  below the button that started them.
- **Database:** new columns `user_locked_until` and `user_reset_psw_expires`
  in `se_user` (added automatically by the update).
- **Files:** SwiftyEdit now creates `data/site_secret.php` (keep it private,
  don't share it) and `data/cache/ratelimit/`.
- Updated translations.

### Removed

- The "remember me" checkbox of the backend login. It only turned off the
  automatic logout in the browser; the session lifetime is now enforced on
  the server. With 2FA, "trust this device" replaces it.
- The `[include]` shortcode (its source folder no longer exists since v2).
  Existing `[include]` tags are removed from the output.
- **Default theme:** the cookie notice that was shown for a `privacy_policy`
  snippet. It depended on jQuery, which the theme no longer loads.

### Fixed

- The personal backend settings (user menu → Settings) could only be opened
  and saved with the right to manage users.
- The backend login page did not use the timezone from the settings.
- Backend XHR readers were chosen by a substring match, so a plugin whose
  name contains a module name (e.g. "snap**shop**") was routed to the wrong
  reader.
- The `/avatars/` URL alias was missing from the `.htaccess` template, so
  profile avatars could be missing on new installations.
- Documentation links and heading anchors in the backend help.
- **Update:** on servers without PHP output buffering, the list of downloaded
  files was not refreshed after a download - the page had to be reloaded.
- **Update:** after a download, a second download only worked after reloading
  the page.

## 2.0.1 - 2026-10-06 (Build 26-0221)

### Changed

- **Update:** the update page lists the channels (stable, beta, alpha) in one
  overview with the build and date of each, marks the installed version and
  shows the "not for production" warning directly at beta and alpha entries.
- **API:** pagination is handled by one shared helper for all endpoints; the
  page number is capped at 1,000,000.
- Domain references changed from swiftyedit.org/.com to swiftyedit.dev.

### Removed

- **Smarty output cache**, including the "Smarty Cache" and "Smarty Cache
  lifetime" settings. It gave almost no speedup and could show the same
  content to every visitor (pagination, filters, sorting, voting state).
  Compiled templates (`templates_c/`) are not affected. On existing
  installations, `data/cache/cache/` is no longer used and can be deleted.

### Fixed

- Pages that show posts, products or events by type only (without selected
  categories) could answer sub-URLs with a 404.
- The system check on the dashboard flagged missing Smarty cache directories
  as an error, although Smarty creates them when needed. Only existing,
  non-writable directories are flagged now.
- Typo in the German welcome text of the installer.

## [2.0.0] - 2026-10-05 (Build 26-0220)

First release of the 2.x series. Highlights are listed in
[ROADMAP.md](ROADMAP.md), the documentation is in [docs/v2](docs/v2).
Since 2.0 the domain root has to point to `/public/`.

[2.0.0]: https://github.com/SwiftyEdit/SwiftyEdit/releases/tag/v2.0.0
