---
title: Installation
description: Install SwiftyEdit in less than 5 minutes
btn: Installation
group: developer
priority: 100
---

# Technical requirements

* PHP 8.3+
* enabled PDO/SQLite module - usually enabled by default; if not, ask your web host.
* MySQL 5.6+ if you'd rather run the database on a dedicated MySQL server instead of SQLite.

## Database: SQLite by default {#database}

SwiftyEdit is built with SQLite as its primary database, not just as a quick-start fallback -
the project's own day-to-day development happens on SQLite, and real effort has gone into
making it fast and reliable for a production site:

* __No separate database server__ - PDO/SQLite ships with PHP itself, so there's nothing extra
  to install, configure, or keep running (and one less service that can fail).
* __Trivial backup and migration__ - the whole database is just a few files under
  `data/database/`. Copying them is a complete, consistent backup; moving to a new server is a
  file copy, not a dump-and-import.
* __Split into three separate database files__ - content, user accounts, and posts each get
  their own SQLite file (see `app/database.php`) instead of sharing one database. This keeps a
  busy area (e.g. logins) from locking out another (e.g. content reads) under concurrent access.
* __Lower resource footprint__ - no separate database process competing for memory and CPU,
  which matters especially on small VPS or shared hosting.
* __Database-heavy areas are cached__ - for parts of the app that hit the database hardest, like
  the shop, SwiftyEdit maintains its own file-based cache (e.g. product data under
  `data/cache/products/`, with a slug lookup map, toggled via the "product listing cache"
  setting). Not every click on a product page has to run a database query at all, which takes a
  lot of the pressure off the database itself, whichever engine you choose.

MySQL 5.6+ is fully supported and works exactly the same way from SwiftyEdit's point of view -
it's a solid choice if your hosting is already built around MySQL, or if you're more comfortable
managing a dedicated database server. Both are first-class options; SwiftyEdit doesn't treat
either one as the "real" database.

## Installing SwiftyEdit

__Please note:__ As of version 2.0, we use the /public/ folder as the domain root.
This means that your domain must point to this folder.

The installation takes only a few minutes.

Installation via Composer:

`composer create-project swiftyedit/swiftyedit`

Or download the files from the website: https://swiftyedit.org/de/download/

### The installation

1. Copy all files to the server.
2. In the web browser, go to the `/install/` directory and follow the instructions ...

If the installation was successful, the next step is to go to the ACP.
Just click on <kbd>Administration</kbd>.
As of version 2, the backend can be accessed at a new address: `example.com/admin/`

__Tip:__ Under <kbd>Settings</kbd> → <kbd>General</kbd> → <kbd>System</kbd> you can set a login
slug - an extra secret segment appended to the admin URL. Once it's set, the backend is no
longer reachable at plain `example.com/admin/`; only the full address including your slug (e.g.
`example.com/admin/your-secret-slug`) shows the login form - anyone hitting `/admin/` without it
is redirected away. This hides the login page from anyone (and any bot) who doesn't already know
the address.

## Updates {#updates}

SwiftyEdit has a built-in update function: open __Update__ in the ACP sidebar (see
[Settings](08-00-settings.md#update)) to see whether a newer stable, beta or alpha version is
available, and install it with a single click.

Technically, an update downloads the chosen release as a zip file, extracts it, and copies the
files directly onto your live installation, then runs any necessary database migrations (new
tables/columns). Your `data/` directory and your installed plugins and themes are left
untouched - with the exception of the bundled plugins (the payment plugins `se_cash-pay`,
`se_invoice-pay`, `se_paypal-pay`, and the editor plugins `tinymce-editor`, `ace-editor`), which
are always refreshed to the shipped version. There is no automatic backup or rollback step.

__Before updating a live site, test the update on a staging copy first__ - this matters
especially if you have your own or third-party plugins/themes installed. A new core version
isn't guaranteed to stay compatible with them: since the updater doesn't touch addon files at
all, an incompatible hook, a changed function signature, or a database change on the core side
would only surface once you actually load the affected page. This is common sense for most
developers, but worth stating plainly: take your own backup (files and database) before
updating, since SwiftyEdit itself won't create one for you.