---
title: Settings
description: All global SwiftyEdit settings in one place
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
* API keys

## General {#general}

The General page is divided into three tabs: <kbd>General</kbd>, <kbd>System</kbd> and
<kbd>E-Mail</kbd>. Each section is saved with its own button.

The texts of the following sections are also shown next to the settings in the backend.

### Website {#general-website}
Name, title, subtitle and description of your website. Themes show them in the header, for
example, and use them for the meta tags of all pages that don't have their own values.

### Logo and icons {#general-branding}
The logo, thumbnail and favicon are stored right away when you upload them. From the favicon
(PNG, JPG or WebP, at least 512×512 px) SwiftyEdit creates all icon sizes, including the
Apple Touch Icon, and the web manifest.

### Image sizes {#general-images}
Uploaded images are scaled down to the maximum width and height, thumbnails to the thumbnail
size. All values in pixels.

### Uploads {#general-uploads}
The maximum file size of uploads in KB. If uploads remain unchanged, images are stored as they
were uploaded - without scaling and without a thumbnail.

### Page versions {#general-page-versions}
Every time you update a page, SwiftyEdit saves the overwritten version. This allows you to
switch back to an older version if you have made a mistake. If there are more versions than
the number set here, the oldest ones are deleted when the page is saved.

### Domain {#general-domain}
Domain and SSL domain (including `http://` or `https://`) and the base path make up the
address of your website - if an SSL domain is set, it is used. SwiftyEdit needs the address
for absolute links, e.g. canonical URLs, sitemaps, feeds and e-mails.

With a login slug, the backend login is only available at `/admin/{slug}`, `/admin/` leads to
the start page.

### Date and time {#general-datetime}
The timezone and the formats of dates and times, used in the frontend and in the backend.

### Themes and templates {#general-themes}
As long as a maintenance code is set, visitors only see the maintenance page. Whoever enters
the code there sees the website as usual (e.g. to check it before going live).

You can also decide whether visitors may choose a theme themselves. The Smarty compile check
recompiles changed templates on every request - useful while you develop a theme, otherwise
leave it off.

### Language {#general-language}
The default language of your website. Hidden languages are no longer offered in the backend,
e.g. in the language selection of pages and posts.

### E-mail {#general-mail}
The sender name and address of all e-mails of your website. They are sent with PHP `mail()`
or via SMTP - the SMTP access data are stored in `data/config_smtp.php`. The notification
address receives a copy of the order e-mails and order requests, as well as order withdrawals.

### Test e-mail {#general-mail-test}
Sends an e-mail with the saved settings to the notification address, or to the sender address
if there is none. Save your changes first.

## User {#user}
These settings control everything around user accounts and interaction.

### Registration and login {#user-registration}
Whether visitors may register themselves and whether the login form is shown. Without the login
form, the frontend login is switched off completely (the backend login at `/admin/` is not
affected, and "forgot password" keeps working).

If new users have to be unlocked by an administrator, they can log in, but can only order in the
shop after they have been unlocked in the user management.

### Required fields {#user-required-fields}
Fields of the registration form that have to be filled in, in addition to user name, e-mail and
password. Only select fields that the registration form of your theme contains - otherwise nobody
can register anymore.

### Blocked user names {#user-blacklist}
User names that may not be registered, separated by commas. Upper and lower case don't matter.

### Backend session {#user-session}
How long (in seconds) an administrator stays logged in to the backend without any activity. If
the PHP setting `session.gc_maxlifetime` of the server is longer, it is used instead.

### Comments {#user-comments}
Whether comments have to be approved by an administrator, appear immediately or are switched off
completely. In addition, comments are switched on or off for each page, post and product. Whoever
writes a comment always enters a name and an e-mail address.

"Close the comment function after *n* days" ends comments automatically: for posts and products
the days count from the release date, for pages from the last edit - so saving a page opens its
comments again. After that, existing comments stay visible, but new comments and answers are no
longer possible. 0 or an empty field means: never close.

### Votings and reactions {#user-votings}
The default for new posts, products and events - you can change it for each entry: off, only for
registered users, or for all visitors. To prevent double votes, anonymous votes and guest list
confirmations are stored with a hash of the visitor's IP address; after 90 days the hash is
removed - the vote keeps counting.

## Posts {#posts}
These settings affect the posts (blog).

### Entries per page {#posts-entries}
How many posts a blog list shows per page, further posts can be reached via the pagination. If no
value or 0 is set, there are 10 entries. Events have a setting of their own - if nothing is set
there, this number is used for them as well.

## Shop {#shop}
The Shop page is divided into four tabs: <kbd>General</kbd>, <kbd>Payment & Shipping</kbd>,
<kbd>Delivery areas</kbd> and <kbd>Business details</kbd>. Each section is saved with its own button.

### Product list {#shop-list}
How many products a product list shows per page (no value or 0 means 10) and how it is sorted by
default - visitors can change the sorting in the list themselves. If the cache is switched on, the
data of the products is cached instead of being read from the database on every request.

### Shopping carts and orders {#shop-cart}
The shopping carts setting turns the cart off entirely, restricts it to registered users, or opens it
to everyone. The orders setting controls whether products can be ordered directly, whether visitors
can only send an order request instead, or both. If you set a maximum order value, carts that exceed
it automatically fall back to the request form, even if direct ordering is enabled.

### Lists {#shop-lists}
Enable lists to let logged-in customers save products to one or more personal lists from the product
card or product page. A list can optionally be shared via a public link — visitors who open that link
can view the list and add its products to their own cart without needing an account.

### Guest orders {#shop-guest-orders}
Enable guest orders to let customers complete checkout without creating an account. Guest orders are
confirmed by e-mail, and the delivery address entered is only kept for the current session.

### Order withdrawal period {#shop-withdrawal}
How many days the "withdraw order" button stays visible next to an order on the "My orders" page.
Without a value it is 14 days (the EU-mandated withdrawal period); enter `0` to always show the button
regardless of order age. This only affects the button's visibility, not the "Order Withdrawal" page
itself, which is always reachable directly.

### Tax rates and currency {#shop-taxes}
You can define up to three flat tax rates (tax #1-#3); each product then picks one of them
individually — there is no separate tax-class management. The default currency is a free-text field
(e.g. `EUR`). You also choose whether prices are shown gross, net, or both, and whether they are
visible to all visitors or only to registered users.

### Shipping costs {#shop-shipping}
Choose whether shipping is charged as a flat rate, or based on shipping categories, where the most
expensive category present in the shopping cart determines the shipping cost. You can define the flat
rate as well as up to three category-based shipping rates.

### Delivery plugins {#shop-delivery-plugins}
Delivery options are provided by plugins (folder name ending in `-delivery`). Every installed delivery
plugin shows up here and can be activated for the shop.

### Payment plugins {#shop-payment-plugins}
Payment methods such as cash, invoice or PayPal are provided by plugins (folder name ending in
`-pay`). Every installed payment plugin shows up here, activated plugins are offered as payment
methods in the checkout.

### Delivery areas {#shop-delivery-areas}
Add the countries you deliver to from the country dropdown - each country is listed once, adding it
again changes the existing entry. Each delivery area can be set to public or draft, and you can
specify whether tax is added for orders shipped there.

### Business details {#shop-business}
Your business address and tax number. SwiftyEdit itself doesn't use them at the moment, but plugins
can read them (`$se_settings['business_address']` and `$se_settings['business_taxnumber']`).

## Events {#events}
The default settings of the event module.

### Entries {#events-list}
How many events a list shows per page. If no value is set, the number from the posts settings is
used, without that one it is 10.

The time offset sets how many hours an event is still shown after its end - in the frontend and via
the API. In the backend it applies when past events are hidden.

### Guest list {#events-guestlist}
The default guest list setting for new events - you can change it for each event: off, only for
registered users, or for all visitors. Visitors sign the guest list to confirm that they take part.

## Labels {#labels}
Most entries and data in the Backend can be provided with labels.
If you manage a lot of data and entries, these labels help you to keep an overview.

### Edit labels {#labels-list}
You change the color, title and description of each label right in the list and save them with the
button next to it. The description shows up as a tooltip when you hover over the label in a list. If
a label is deleted, the entries keep its number, but the label is no longer shown.

### New label {#labels-new}
A new label needs a title, color and description are optional. It is then available for all entries
and in the global filter.

## API keys {#api-keys}
API keys give other applications access to the public API - for example a second website that
displays your products. Developers can find out how to use the API in the [API](09-04-api.md)
chapter.

### New API key {#api-keys-new}
To create a key, enter a name (e.g. the name of the application using it) and select the
permissions - what each of them means is described in the section Permissions of the
documentation.

{alert:warning}
__The key is shown only once__, right after creating it. Copy it and store it in a safe place -
SwiftyEdit only stores a fingerprint (hash) of the key and can't show it again later. If a key
gets lost, simply create a new one.
{/alert}

### Permissions {#api-keys-scopes}

| Permission | Meaning |
|------------|---------|
| Read products | The key can fetch public products. |
| Read prices, even if they are only shown to logged-in customers | Only relevant if the shop setting allows prices for logged-in customers only. Without this permission, the API returns no prices in that case. |
| Read blog posts | The key can fetch public blog posts. |
| Read events | The key can fetch public events. |
| Read pages | The key can fetch public pages. Password protected pages and pages for user groups stay hidden. |
| Read categories | The key can fetch the categories of products, posts and events. |

### Manage keys {#api-keys-list}
The list shows the beginning of each key so you can tell them apart, as well as when the key was
last used and how many requests it made.

* __Revoke__ blocks a key immediately - applications using it lose access. Revoking can't be
  undone.
* Only revoked keys can be __deleted__.

## Database {#database}
This page only appears when the site runs on SQLite (it is hidden on MySQL installations,
where it does not apply).

### WAL mode {#database-wal}
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

### Compact database {#database-vacuum}
The table shows the size and the free space inside each of the three database
files. Deleted data leaves free space in the file that SQLite reuses for new data - but the
file doesn't get smaller by that. The button compacts the files (`VACUUM`) and gives this
space back to the disk. Each file is rewritten: nothing can be saved while it runs, and it
temporarily needs as much free disk space as the largest file. This is mainly useful after
a lot of data has been deleted.

---

## Update {#update}
Update is a separate top-level item in the sidebar (not part of the settings).
If a newer version is available for installation, it will be displayed there. See
[Updates](01-01-installation.md#updates) for what an update actually does and how to update
safely.

