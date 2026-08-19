---
title: Settings
description: Settings
btn: Settings
group: backend
priority: 900
---

# Settings

<kbd>Backend</kbd> ▶ <kbd>Settings</kbd>

The settings are divided into the following sub-categories

* General
* User
* Posts
* Shop
* Events
* Labels

## General

The General page is divided into three tabs:

* <kbd>General</kbd> The standard metadata (page name, title, subtitle, description, author),
  the RSS time offset, the number of saved page versions, and image settings (image prefix,
  logo/thumbnail/favicon, maximum image and thumbnail sizes, maximum upload file size).
  All metadata can be overwritten later by individual pages or products etc.
* <kbd>System</kbd> Domain, SSL domain, base path and the login slug, as well as date/time
  format and timezone, themes & templates, the maintenance code, the Smarty cache settings,
  the default language and the option to hide individual languages.
* <kbd>E-Mail</kbd> The mailer configuration (sender name and address, mail type / SMTP,
  notification address) and a test-mail function.

### RSS Time offset
You can set a time offset here. This prevents posts from ending up in the RSS feed immediately.

### Number of page versions
Every time you update a page, SwiftyEdit saves the overwritten version.
This allows you to switch back to an older version if you have made a mistake.
Here you can specify how many versions of a page should be saved.

## User
These settings control everything around user accounts and interaction:

* Whether visitors may register themselves, whether the login form is shown,
  and whether new users have to be unlocked by an administrator.
* The session lifetime of the backend and a blacklist of user names that may not be registered.
* Which fields are required on registration.
* Comments: the mode, the authorization, the auto-close time, the maximum number of entries
  and the maximum nesting level.
* Votings and reactions (off, for registered users only, or globally).

## Posts
These settings affect the posts (blog).

## Shop
The Shop page is divided into four tabs:

* <kbd>General</kbd> Products per page and the default sorting for the product listing, whether the
  product listing cache is enabled, the shopping cart mode, the order mode and guest checkout, a
  maximum order value, the order withdrawal period, up to three tax rates, the default currency, and
  how prices are displayed and to whom.
* <kbd>Payment & Shipping</kbd> The shipping mode and shipping costs, plus activation switches for
  any installed payment and delivery plugins.
* <kbd>Delivery areas</kbd> Add, edit or remove the countries you deliver to.
* <kbd>Business details</kbd> Your business address and tax number.

### Shopping carts and orders
The shopping carts setting turns the cart off entirely, or restricts it to registered users, or opens
it to everyone. The orders setting controls whether products can be ordered directly, whether visitors
can only send an order request instead of checking out, or both. If you set a maximum order value,
carts that exceed it automatically fall back to the request form, even if direct ordering is enabled.

### Lists {#lists}
Enable lists to let logged-in customers save products to one or more personal lists from the product
card or product page. A list can optionally be shared via a public link — visitors who open that link
can view the list and add its products to their own cart without needing an account.

### Guest orders
Enable guest orders to let customers complete checkout without creating an account. Guest orders are
confirmed by e-mail, and the delivery address entered is only kept for the current session.

### Order withdrawal period
The "order withdrawal button visible for" setting controls how many days the "withdraw order" button
stays visible next to an order on the "My orders" page. It defaults to 14 days (the EU-mandated
withdrawal period); enter `0` to always show the button regardless of order age. This only affects the
button's visibility, not the "Order Withdrawal" page itself, which is always reachable directly.

### Tax rates and currency
You can define up to three flat tax rates (tax #1-#3); each product then picks one of them
individually — there is no separate tax-class management. The default currency is a free-text field
(e.g. `EUR`). You can also choose whether prices are shown gross, net, or both, and whether they are
visible to all visitors or only to registered users.

### Shipping costs
Choose whether shipping is charged as a flat rate, or based on shipping categories, where the most
expensive category present in the shopping cart determines the shipping cost. You can define the flat
rate as well as up to three category-based shipping rates.

### Payment and delivery plugins
Payment methods (e.g. cash, invoice, PayPal) and delivery options are provided by plugins. Any
installed payment or delivery plugin shows up in its own list on the Payment & Shipping tab, where you
can activate it for the shop.

### Delivery areas
Add the countries you deliver to from the country dropdown. Each delivery area can be set to public
or draft, and you can specify whether tax should be added for orders shipped there.

### Business details
Your business address and tax number are stored here for use on orders and invoices.

## Events
Here you will find the default settings for the event module. For example, you can specify whether the guest lists
should be activated.

## Labels
Most entries and data in the Backend can be provided with labels.
If you manage a lot of data and entries, these labels help you to keep an overview.

## Database {#database}
This tab only appears when the site runs on SQLite (it is hidden on MySQL installations,
where it does not apply).

**Enable WAL mode** switches SQLite's journal mode from the default `DELETE` mode to
`WAL` (Write-Ahead Logging). In `DELETE` mode, a write locks the entire database file, so
concurrent reads have to wait until the write is finished. `WAL` mode lets reads continue
while a write is in progress, instead of locking the whole file. On a site with a lot of
concurrent traffic, this can noticeably reduce `database is locked` errors and response
times. The setting is applied immediately to all three SQLite databases (content, user,
posts) and can be turned off again at any time - it's a persistent property of the
database files, not something that needs to be re-applied on every request.

Two things to know before enabling it:

- **Storage:** WAL mode needs working shared-memory locking on the filesystem. It is safe
  on normal local storage, but can be unreliable on network filesystems (e.g. NFS) or
  certain non-local Docker volume mounts.
- **Backups:** WAL mode adds two extra files (`-wal` and `-shm`) next to each database
  file. A backup process that only copies the `.sqlite3` file can miss recent changes that
  haven't been checkpointed yet. Make sure your backup includes the `-wal`/`-shm` files, or
  runs a checkpoint (`PRAGMA wal_checkpoint(TRUNCATE)`) before copying.

---

## Update {#update}
Update is a separate top-level item in the sidebar (not part of the settings).
If a newer version is available for installation, it will be displayed there. See
[Updates](01-01-installation.md#updates) for what an update actually does and how to update
safely.

