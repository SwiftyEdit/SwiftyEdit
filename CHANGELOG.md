# Changelog

All notable changes to SwiftyEdit are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Versions are identified by version number and build (see `version.json`).

Security fixes are listed in the release they ship with, not in advance.
See [SECURITY.md](SECURITY.md) for how to report a vulnerability.

## 2.2.0 - 2026-10-26 (Build 26-0227)

### Added

- **Delete user accounts in the backend** (user editor, new tab "Account",
  which also holds the 2FA reset). Accounts are not removed from the
  database, so their id is never reused: the personal data is removed and
  the account is marked as deleted. Orders are kept (retention duties),
  comments are anonymized (or deleted, if chosen), wishlists, carts, trusted devices, event
  confirmations, group memberships and page authorizations are removed, API
  keys are deactivated. The user name stays reserved for 180 days. The own
  account and the last account with the right to manage users can't be
  deleted. Deleting the own account in the frontend profile follows the
  same rules; it is not possible for accounts with backend access or with
  open orders. Content the user wrote (pages, posts, products, events)
  stays.
- **Price groups** can be deleted, as long as no product uses them. The list
  shows net and gross prices.
- **Order e-mails:** the product tables come from templates of the theme
  (`templates-mail/order-products.tpl`, `order-products-row.tpl`,
  `order-request-products.tpl`, `order-request-products-row.tpl`, with the
  default theme as fallback) and know the new placeholders
  `{price_gross_single}`, `{price_gross}` and `{options_comment}`.
- **Embeds:** `$se_embed_hosts` in `config.php` lists the sources whose
  iframes are kept when editor content is cleaned (default: YouTube,
  youtube-nocookie, Vimeo, Google Maps, OpenStreetMap). Add more in
  `data/config.php`, as a host or a host with a path. Used for categories
  for now (see Changed).
- **Updates:** `$se_backend_updates = false;` in `data/config.php` switches
  off downloading and installing updates in the backend, e.g. if you deploy
  by hand. New versions are still shown.
- **Folder permissions:** `SE_DIR_PERMISSIONS` (default `0755`) for folders
  SwiftyEdit creates; change it with `define('SE_DIR_PERMISSIONS', 0775);` in
  `data/config.php`.
- **Plugins:** backend endpoints of plugins can call `se_acp_guard()` for the
  same session checks as the core.
- `SECURITY.md` is part of the release.

### Changed

- **Login:** accounts are locked after 5 failed logins in a row by default
  (`$se_failed_logins_limit`, before: off). The lock starts at 1 minute and
  doubles with every further failed attempt, at most 60 minutes; the owner
  gets a mail with an unlock link. Set another value or switch it off
  (`$se_failed_logins_limit = null;`) in `data/config.php`.
- **Login lock:** the unlock link in the e-mail is valid for one hour and only
  unlocks the account after clicking a button, so link previews and mail
  scanners no longer unlock it by just opening the link. Links from older
  e-mails no longer work; the next lock sends a new one.
- **Sessions:** the session cookie is set with `HttpOnly`, `SameSite=Lax`
  and, on https, `Secure`; unknown session ids are no longer accepted.
  Existing sessions keep their old cookie until the browser is closed.
- **Security headers:** SwiftyEdit sends `X-Content-Type-Options`,
  `Referrer-Policy` and `Permissions-Policy` (camera, microphone and
  geolocation off) itself, so they also work with nginx or Caddy. The
  backend can only be framed by itself (`frame-ancestors 'self'`).
- **Forms:** a missing or invalid CSRF token (usually an expired session)
  shows a short message with HTTP 403 instead of silently redirecting to
  the homepage, and is written to the log.
- **Errors:** serious PHP errors (fatal, parse, uncaught exceptions) are
  written to the PHP error log in production instead of being suppressed.
  They are still not shown on the page.
- **Backend:** uploads (media, branding) check the same session rules as the
  rest of the backend: maximum lifetime, idle timeout and account changes
  (new password, withdrawn rights). Uploading a logo, thumbnail or favicon
  requires the "system settings" permission.
- **Categories:** teaser and text are cleaned when saved (HTMLPurifier) -
  scripts, event handlers and `javascript:` links are removed, iframes are
  only kept for the sources in `$se_embed_hosts`. Existing texts change only
  when they are saved again.
- **Page passwords** still stored as MD5 are replaced with a secure hash
  the next time a visitor enters the right password.
- **Folders** created by SwiftyEdit get permissions `0755` instead of `0777`.
- **Files:** `data/site_secret.php` is written with permissions `0600`
  (existing files are adjusted). If it can't be written, the dashboard shows a
  warning.
- **Shop filters:** the counts in the filter sidebar are calculated with the
  product list and take the active filters of the other groups into account.
  Themes use `{$item.count}`; the endpoint `/xhr/se/counter/?filter=` is
  deprecated, but still works for custom themes.
- **Products** can't get a release date in the future any more (it would hide
  the product) - use the status to hide a product. Empty or future dates
  become the current time.
- **Product editor:** release date, comments and votings are collapsed at the
  bottom of the sidebar, with their current value shown. The product list in
  the backend works better on small screens.
- **Backend design:** flatter nested cards with fine borders.
- **Default theme:** the logo in the header keeps its size (at most 5rem
  high, 16rem wide) and the title takes the remaining width.
- **Themes - templates changed:** custom themes with an own copy should update
  it.
  - `header.tpl` - logo column and `.header-logo` class.
  - `sidebar-filter.tpl` - uses `{$item.count}` instead of one request per
    filter item.
  - `templates-mail/order-*.tpl` - new (see Added); without them, the default
    theme's files are used.
  - `php/tinymce-links.php`, `php/tinymce-images.php` - see Fixed.
- **Database:** new columns `user_unlock_expires`, `user_deleted_at` and
  `user_deleted_nick` in `se_user` (added automatically by the update). New
  installations no longer create the unused column `ba_sales_tax_id_number`.
- Updated translations and npm dependencies.

### Fixed

- Password protected pages with a list of products, posts or events showed
  the list (and comments) without the password - only the title changed.
  They now show the password form until the right password is entered.
  The password protects the page, not the entries themselves: products and
  posts stay reachable through search, other pages and the API.
- Comments: the comment endpoint didn't check whether comments are switched
  on for the page, post or product, so they could be read and written for
  any entry by id - including drafts and password protected pages. It now
  checks the comment setting of the entry, its visibility and the page
  password. Answers must belong to the same entry as the comment.
- The page and image lists for the editor (`tinymce-links.php`,
  `tinymce-images.php` in the default theme) were also delivered to logged-in
  frontend users, not only to administrators.
- Categories: the hash and the image paths were taken from the form
  unchecked. The hash of an existing category can no longer be changed.
- The backend user form showed the tax number and the VAT ID number, but
  didn't save them.
- Product editor: the votings setting showed the wrong value.
- Logout: a POST without form fields logged out without a CSRF token.
- The rate limit files in `data/cache/ratelimit/` were never removed; files
  older than one day are now cleaned up.

### Manual updates

The installer or the update in the backend writes the `.htaccess` files of
the upload folders (`assets/images`, `files`, `avatars`, `galleries`) and
renews the core block in `public/.htaccess` (e.g. passing on
`HTTP_AUTHORIZATION`, the `avatars` alias). After a **manual** update:

- open `/install/` once, so the `.htaccess` files of the upload folders are
  written and the database is updated,
- compare the core block in `public/.htaccess` with
  `install/contents/_htaccess.txt` and replace it if it differs.

## 2.1.2 - 2026-10-16 (Build 26-0224)

### Added

- **Sessions:** every login ends after a fixed time, however active the
  session is - 12 hours by default, `$se_session_max_lifetime` in
  `data/config.php` (`0` = no limit).
- **Two-factor authentication:** after 10 wrong codes, the second factor of an
  account is locked for 5 minutes; every further wrong code doubles the lock
  (at most 24 hours). A correct code resets the counter, trusted devices can
  still log in. The user management shows until when an account is locked;
  resetting 2FA removes the lock.
- **.htaccess:** a section "User rules before SwiftyEdit" at the top of
  `public/.htaccess` for own redirects and rewrites - rules after the core
  block never apply to SwiftyEdit URLs. Updates add the section to existing
  files.
- **.htaccess:** `$se_update_htaccess = false;` in `data/config.php` keeps
  updates from touching `public/.htaccess` (e.g. if it is managed elsewhere).
- **Upload folders:** the installer and every update write a small
  `.htaccess` into the upload folders (`assets/images`, `files`, `galleries`,
  ...) that blocks scripts and protects SVG files opened directly - also if
  `public/.htaccess` is outdated. **After a manual update, open `/install/`**
  so the files are written.
- **Help window:** plugins and themes can ship their own documentation
  (`readme.md` or `docs/{lang}/*.md`, sorted by the front matter `priority`).
  The help window then shows only the docs of that plugin or theme, and the
  addon and theme lists get a help button. Plugin pages show the plugin name
  with a help button next to the breadcrumb.

### Changed

- **Two-factor authentication:** the setup at login always starts with a code
  sent to the stored e-mail address; the method is chosen afterwards (with
  the e-mail method, the setup is done right away). This needs working
  e-mail delivery - otherwise another administrator or `$se_2fa_bypass`
  helps.
- **Two-factor authentication:** after a password reset with the "forgot
  password" link, the next backend login of an account with codes by e-mail
  needs one of its recovery codes. Keep them also with the e-mail method.
- **Backend accounts** can no longer change their e-mail address in the
  frontend profile - only in the backend.
- **Sessions:** a new password, withdrawn rights or a deactivated account end
  all other sessions of that account - in the backend and in the profile,
  cart, checkout, orders, wishlist, comments and votings right away, on
  other frontend pages within 5 minutes. Changing the own password keeps the
  current session.
- **Logout** only works with a button (POST with CSRF token). Opening
  `/logout` or `?goto=logout` shows a confirmation instead of logging out
  directly, so own links keep working.
- **Login:** with the login form switched off in the settings, the frontend
  login is switched off completely (the backend login and "forgot password"
  are not affected).
- **Login:** after too many failed logins, the counter decreases again over
  time (one attempt per 10 minutes), so single attempts can't keep an account
  locked permanently.
- **Login:** older password hashes are renewed at the next login.
- **Votings and guest lists** follow the setting of the entry (off,
  registered users only, everybody) - also for direct requests.
- **Anonymous votes and guest list confirmations:** the stored hash of the
  visitor's IP address is removed after 90 days; the vote keeps counting.
- **Updates:** the core block of `public/.htaccess` is still replaced, but the
  previous file is saved to `data/backups/` first, and the update page shows a
  note if the core block changed or the file had no SwiftyEdit markers
  (previously it was overwritten without a backup).
- **Addons:** loading addon files from other servers allows fewer address
  ranges, connects to exactly the checked address and limits the size
  (ZIP 50 MB, info.json 1 MB).
- **Backend:** the writers for users, groups, blog posts, events and products
  only take known form fields.
- **Backend design:** a lightly tinted background, calmer button colors and a
  new outline button style, used in the addon list.
- **Themes - templates changed:** custom themes with an own copy should update
  it.
  - `statusbox.tpl` - the logout entry is a small form now (`$hidden_csrf_token`).
    An old copy still works, with the confirmation step.
  - `loginbox.tpl` - the "stay logged in" checkbox is gone (see Removed).
  - `profile/change-mail.tpl` - new variable `mail_change_blocked`; backend
    accounts see a note instead of the form.
- **Database:** new columns `user_2fa_failed`, `user_2fa_locked_until`,
  `user_2fa_mail_blocked` and `user_failed_login_time` in `se_user` (added
  automatically by the update).
- **Files:** SwiftyEdit creates `data/backups/` (backups of `.htaccess`).
- Updated translations.

### Removed

- The "stay logged in" checkbox of the frontend login. It never kept anybody
  logged in. `$label_remember_me` is no longer assigned; the table
  `se_tokens` is no longer created, its rows are deleted by the update.

### Fixed

- Two-factor codes (app, e-mail and recovery codes) could be used twice by
  parallel requests.
- A request to `/xhr/se/login/` without a form caused an error 500.
- Folder names on the update page were not escaped.
- Saving an event set its release date to "now"; the field now shows the
  stored date.
- Saving a product emptied values that are not part of the form, e.g. the
  view counter, the download counters of attachments and notes.
- Guest lists: on MySQL, the same visitor could confirm several times.
- Settings › User: internal 2FA columns were offered as required fields for
  the registration.

## 2.1.1 - 2026-10-13 (Build 26-0223)

### Added

- **Addons:** the addon list can be filtered by type (plugins, payment,
  editors).

### Changed

- **Addons and updates:** loading the addon catalog and checking for updates
  give up after 5 seconds if the server doesn't answer, and no longer block
  other backend requests while they wait.
- Updated npm dependencies.

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
