---
title: Basics
description: The dashboard and the basics of working in the backend
btn: Basics
group: administrators
priority: 100
---

# Dashboard {#dashboard}

Here you can find all the information about your website at a glance.

At the top you can see what has happened in the last 30 days. The grid has one row per area
(pages, snippets, blog, products, events, uploads, orders and users), but only if there was activity.
Saving the same item several times on one day counts once. For users, new accounts and changes by an administrator count. Each row is measured by its busiest day.
Click a day to see the totals of that day, click an area to open the history.

The history lists every item per day with its title, type, number of saves and the latest time.
Click a row to open the item. Deleted items are shown without a link.

Below you will find your plugins, under “Alerts” everything that needs your attention
(for example missing pages for types of use),
the newest users as well as caches, system information and OPcache.

With the right for sensitive files, “Alerts” also shows notes from the log: CSRF errors of logged-in users and account locks
always, CSRF errors without a login from 3 hits on the same path in 7 days (this can point to a broken form) and failed logins
from 10 in 7 days. The log only takes a limited number of these entries per hour, so the numbers are minimum values.

Open the full log with the “Activity” link in the “System” card. This requires the right for sensitive files.
Entries are deleted after 90 days.

Tip: If you are working on a theme or similar and have not activated the “Smarty Compile Check” option,
you can also clear the Smarty cache here.

### Cache {#cache}

The "System" card on the dashboard lists every cache SwiftyEdit maintains, with its current
size and a button to clear (and, for some, rebuild) it. Most of these are kept up to date
automatically whenever you make changes through the ACP - the manual buttons here are mainly
a troubleshooting/recovery tool, needed after direct database edits, bulk imports, or to
recover from a corrupted cache file.

| Cache                        | What it stores                                                                     | When you need to clear/rebuild it manually                                                                                              |
|-------------------------------|-------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------|
| Template cache (Smarty)      | Compiled frontend theme templates (`.tpl` files under `public/assets/themes/`).     | After editing theme files directly on the server while "Smarty Compile Check" is disabled (see the tip above).                            |
| Template cache (Twig / ACP)  | Compiled backend/ACP templates (`acp/templates/*.tpl`).                             | After editing ACP templates directly on the server. Automatically cleared by SwiftyEdit's own update process, so no action is needed after a regular core update. |
| Navigation                   | The page tree per language, used to build frontend menus.                          | Normally rebuilt automatically whenever a page is created, edited, or deleted. Only needed after a direct database change to pages.       |
| URL paths                    | The list of active page permalinks, used for routing incoming requests.            | After a direct database change to page permalinks.                                                                                        |
| Categories                   | All categories.                                                                     | Normally rebuilt automatically on every category change. Only needed after a direct database change to categories.                        |
| Tags                         | All tags.                                                                           | Normally rebuilt automatically on every tag change. Only needed after a direct database change to tags.                                   |
| Snippets                     | One cache file per snippet (see [Snippets](03-00-snippets.md)).                    | After a direct database change to snippets.                                                                                                |
| Preferences                  | Site-wide settings and branding.                                                    | Normally rebuilt automatically whenever a setting is saved. Only needed after a direct database change to options.                        |
| Products                     | One cache file per product plus per-language slug maps, used by the shop frontend. | After bulk price/stock imports or direct database changes to products. Use "Clear" to remove stale files, "Rebuild" to regenerate them from the database. |

---

### OPcache {#opcache}

If [OPcache](https://www.php.net/manual/en/book.opcache.php) is enabled on your server, the "OPcache" card
on the dashboard shows its current status: memory usage, the number of cached files relative to the limit
(`opcache.max_accelerated_files`), and the hit rate. If the cache is full or timestamp validation
(`opcache.validate_timestamps`) is disabled, a warning is shown as well.

If timestamp validation is disabled, PHP does not detect file changes automatically - in that case you
need to clear the cache manually after your own changes to PHP files for them to take effect. Use the
"Clear cache" button on the same card for that. SwiftyEdit's own update process (core updates as well as
plugin/theme installation via URL) already does this automatically, so there's nothing to do there.

{alert:info}
The card is only visible to users with the "can upload sensitive files" permission.
{/alert}

---

### Categories {#categories}

To structure your site, you can create an unlimited number of categories.
[Pages](02-00-pages.md), [blog posts](04-00-blog.md), [products](05-00-shop.md) and [events](06-00-events.md) 
can be assigned to these categories.

The categories are multilingual. This means that the language of a category must match 
the language of the assigned data record.

If multiple categories are used on a shop page, the content of the currently selected category 
replaces the meta-information (title, description, keywords) of the page.

#### Input fields

| Field       | Type       | Description                                                                                   |
|-------------|------------|-----------------------------------------------------------------------------------------------|
| Title       | `Text`     | Category title                                                                                |
| Link-Name   | `Text`     | Displayed as a link in the frontend                                                           |
| Priority    | `Number`   | The priority (and the language) determines the sorting of the categories.                     |
| Language    | `Select`   | The language of the category                                                                  |
| Thumbnail   | `File`     | An image for this category can be selected here.                                              |
| Description | `Textarea` | The description of the category                                                               |
| Keywords    | `Text`     | Keywords                                                                                      |
| Content     | `wysiwyg`  | Description text for the category. Depending on the theme, this is displayed in the frontend. |

---

## Upload & manage files

Here you can upload images and files and manage the ones you have already uploaded.
You can save information about each uploaded file.

### Upload files

You can open the upload form via the link at the bottom right of the screen.

By selecting "Choose destination folder" you determine where your upload should be stored.
If you upload files with identical filenames, SwiftyEdit will number the files automatically.
If you activate the option "Overwrite existing", of course not.

Which file types you can upload is shown below the window.
The list can only be extended or edited via the config.php file.

SwiftyEdit gives you two default directories (images and files).
Within these directories you can create as many subdirectories as you want.