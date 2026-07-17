---
title: Themes - Templates
description: The template system
btn: Templates
group: developer
priority: 200
---

# The templates (.tpl files)

Some templates are included directly by SwiftyEdit.
These templates should therefore be located in the `public/assets/themes/{theme}/templates/` folder.

## Inheriting from the default theme {#inheriting-from-the-default-theme}

This is worth calling out on its own, because it's one of the more useful things about the
template system: __a theme only has to contain the `.tpl` files it actually wants to change.__
Smarty's template directory is set to `[your-theme/templates/, default/templates/]`, in that
order (`app/smarty.php`), and every `$smarty->fetch(...)` call anywhere in the codebase passes a
bare filename, never a theme-specific path - the search across those two directories happens
inside Smarty itself (`vendor/smarty/smarty/src/Resource/FilePlugin.php`), the same way for every
single template. So a "partial theme" that ships, say, only `products-list.tpl` and
`products-display.tpl` to give the shop a different layout is a fully supported approach, not a
workaround: every other page (account, blog, events, checkout, lists, ...) keeps rendering
correctly, inherited unmodified from `default`.

A couple of ACP editor assets fall back the same way, with slightly different rules
(`acp/core/editors.php`):

* `dist/editor.css` falls back to `default`'s copy if your theme doesn't ship one.
* `php/tinymce-images.php` / `php/tinymce-links.php` fall back to `default`'s the same way.
* `dist/tinyMCE_config.js` does __not__ fall back - if your theme doesn't ship one, tinyMCE
  simply uses its own built-in configuration instead of `default`'s.

What does __not__ fall back at all: your main frontend assets. `head.tpl` links
`dist/default.css` and `dist/theme.js` with `{$se_template}` baked directly into the URL, with no
existence check - if your theme doesn't ship those two files, the links are just broken, they do
not silently resolve to `default`'s versions. Every theme therefore needs its own
`dist/default.css` and `dist/theme.js`, even if either one is trivial (e.g. just `@import`ing
`default`'s stylesheet) - see
[Building the theme assets](09-01-00-themes.md#building-the-theme-assets).

## Template file overview

This lists every `.tpl` file the `default` theme ships, grouped by area, with a short note on
what renders it and (where useful) which variables it relies on. For the full, authoritative set
of variables a given template receives, see [Template variables](#template-variables) below.

### Layout & global

| File                     | Purpose                                                                                    |
|--------------------------|---------------------------------------------------------------------------------------------|
| `index.tpl`               | The first template loaded for every request; includes `head.tpl` and, via the layout, everything else. |
| `layout_default.tpl`      | The default page layout (header, content column, sidebar, footer). Selectable per page via `page_template_layout`. |
| `header.tpl`               | Site header: logo, theme switcher, top actions.                                             |
| `navigation.tpl`           | The main navigation menu.                                                                   |
| `content.tpl`              | Renders whichever of `$msg_content`, `$products_content` or `$page_content` is set, plus comments. |
| `sidebar.tpl`              | Includes `sidebar-categories.tpl`, `sidebar-filter.tpl` and `sidebar-toc.tpl`, plus the sidebar snippet. |
| `sidebar-categories.tpl`   | Category navigation block in the sidebar.                                                   |
| `sidebar-filter.tpl`       | Shop filter form in the sidebar (price range, options, tags, ...).                          |
| `sidebar-toc.tpl`          | Table-of-contents block for pages with sub-pages (`$arr_submenue`).                         |
| `footer.tpl`               | Site footer, including the breadcrumb navigation.                                           |
| `head.tpl`                 | `<title>`, meta tags, canonical URL, `<base href>` - included inside `index.tpl`'s `<head>`. |
| `socialmedia.tpl`          | Renders configured social media links.                                                      |
| `status_message.tpl`       | System/flash messages (e.g. "saved successfully").                                          |
| `statusbox.tpl`             | Links to the ACP (administrators) or profile (logged-in users).                             |
| `admin_helpers.tpl`         | Quick-edit links shown to logged-in administrators (edit this page, etc.).                  |
| `maintenance.tpl`           | Shown site-wide while maintenance mode is active.                                           |
| `sitemap.tpl`                | The XML/HTML sitemap output.                                                                |
| `page_psw_input.tpl`        | Password prompt for password-protected pages.                                               |

### Account & authentication

| File                          | Purpose                                              |
|--------------------------------|-------------------------------------------------------|
| `loginbox.tpl`                  | Login form (can be disabled in the ACP).             |
| `registerform.tpl`              | Registration form for new users.                     |
| `password.tpl`                  | "Reset password" form.                               |
| `profile_main.tpl`              | Main profile page (contact info, includes the files below). |
| `profile/address.tpl`           | Delivery address form.                               |
| `profile/address-ba.tpl`        | Billing address form.                                |
| `profile/address-sa.tpl`        | Shipping address form.                               |
| `profile/address-mail.tpl`      | Change/verify the account e-mail address.            |
| `profile/change-mail.tpl`       | E-mail change confirmation flow.                      |
| `profile/change-password.tpl`   | Change password form.                                |
| `profile/avatar.tpl`            | Avatar display.                                       |
| `profile/avatar-form.tpl`       | Avatar upload form.                                    |

### Search

| File                | Purpose                          |
|---------------------|-----------------------------------|
| `search.tpl`          | The search input form.           |
| `searchresults.tpl`   | Search result listing.           |

### Blog (posts)

| File                  | Purpose                                          |
|-----------------------|----------------------------------------------------|
| `posts-list.tpl`       | Post listing (all post types: message, image, gallery, video, link, download). |
| `posts-display.tpl`    | Single post detail view.                          |

### Shop

| File                       | Purpose                                                          |
|-----------------------------|--------------------------------------------------------------------|
| `products-list.tpl`          | Product listing/catalog page.                                     |
| `products-display.tpl`       | Single product detail page (also renders `$product_plugin_actions`, see [Hooks](09-01-00-themes.md#hooks)). |
| `shopping_cart.tpl`           | Shopping cart page wrapper.                                        |
| `shopping_cart_form.tpl`      | The cart's checkout/request form.                                  |
| `shopping_cart_table.tpl`     | The line-item table inside the cart.                               |
| `orders.tpl`                   | "My orders" overview page.                                         |
| `orders-list.tpl`              | The paginated order list (HTMX partial).                           |
| `order-item.tpl`               | Single order detail (modal).                                       |
| `order-withdrawal.tpl`         | The order withdrawal form (EU right-of-withdrawal).                |

### Lists (wishlist)

See [Lists](05-00-shop.md#lists) for the user-facing feature.

| File                          | Purpose                                                        |
|--------------------------------|--------------------------------------------------------------------|
| `wishlist_overview.tpl`         | "My lists" overview for a logged-in customer.                    |
| `wishlist_detail.tpl`            | A single list's contents (owner view), incl. drag-reorder.       |
| `wishlist_public.tpl`            | Public, read-only view of a shared list (with "add to cart").    |
| `wishlist_picker.tpl`            | The "save to list" picker modal opened from a product page.      |
| `wishlist_list_column.tpl`       | One list row inside the picker/overview.                          |

### Events

| File                  | Purpose                    |
|-----------------------|------------------------------|
| `events-list.tpl`      | Event listing.              |
| `events-display.tpl`   | Single event detail view.   |

### Comments

| File                  | Purpose                              |
|-----------------------|-----------------------------------------|
| `comment_entry.tpl`    | A single rendered comment (incl. replies). |
| `comment_form.tpl`     | The comment submission form.               |

### Snippets

| File                   | Purpose                                                     |
|-------------------------|-----------------------------------------------------------------|
| `snippet.tpl`             | Plain-text snippet output.                                      |
| `snippet_card.tpl`        | Snippet rendered as a card (title + text).                      |
| `snippet_card_img.tpl`    | Snippet rendered as a card with an image and optional link button. |

### Misc / utility

| File                       | Purpose                                          |
|------------------------------|-----------------------------------------------------|
| `404.tpl`                    | Shown for the HTTP status code 404 (page not found). |
| `download.tpl`                | Download/attachment detail page.                     |
| `image.tpl`                   | Single image detail page.                             |
| `alert/alert-danger.tpl`      | Bootstrap "danger" alert box.                         |
| `alert/alert-info.tpl`         | Bootstrap "info" alert box.                            |
| `alert/alert-success.tpl`      | Bootstrap "success" alert box.                          |
| `alert/alert-warning.tpl`      | Bootstrap "warning" alert box.                          |

## Mail templates {#mail-templates}

`templates-mail/` is a separate, simpler template mechanism used for outgoing e-mails
(order confirmations, order withdrawal requests, password resets, ...). It is built with plain
PHP `str_replace()` in `se_build_html_file()` (`app/functions/functions.php`) - __not__ Smarty.
Do not use Smarty syntax (`{$var}`, `{if}`, `{foreach}`, ...) in these files, only the literal
placeholders listed below.

* `mail.tpl` - the outer HTML wrapper (`<html>`, inline styles, header/footer) used for every
  mail unless a different `tpl` is requested. Its placeholders: `{styles}` (contents of
  `styles.css`), `{mail_title}`, `{mail_preheader}`, `{mail_salutation}`, `{mail_body}`,
  `{mail_subject}`, `{mail_footer}`.
* `styles.css` - inlined into `{styles}` in `mail.tpl`.
* Body templates - a specific mail's body content, substituted into `mail.tpl`'s `{mail_body}`
  placeholder. Each defines its own additional placeholders that the calling code fills in
  (e.g. `order-withdrawal-request.tpl` uses `{lang_label_order_nbr}`, `{order_nbr}`,
  `{order_mail}`, `{order_reason}`, ...):
    * `order-withdrawal-request.tpl`
    * `send-order-request.tpl`
    * `send-order-status.tpl`

If you add a new transactional e-mail from PHP, you supply your own body template file name and
placeholder values via the `$data` array passed to `se_build_html_file()` - see its docblock in
`app/functions/functions.php` for the full parameter list.

## Template variables {#template-variables}

There is no single global reference of "all variables available in all templates" - each
template only receives whatever the PHP handler responsible for that page assigns via
`$smarty->assign(...)` right before rendering it. You don't have to go hunting for them by hand
though - Smarty ships a debug console for exactly this.

The quickest option: put `{debug}` anywhere in a `.tpl` file (temporarily, just for
inspection) and load the page. It pops up a list of every variable assigned in the current
template's scope, without touching any PHP. For a page-wide overview instead of a single
template, you can turn on Smarty's debug console for the whole request by setting
`$smarty->debugging = true;` in `app/smarty.php` - or, without editing core files, add
`$smarty->debugging_ctrl = 'URL';` there once, which then lets you enable it per-request by
appending `?SMARTY_DEBUG` to the URL. Remove/revert these before deploying, they're for local
development only.

There is deliberately no setting for this in the ACP: the debug console dumps every assigned
variable as-is, which can include session or database data, so it must not be something that can
be switched on for a live site by mistake. Editing `app/smarty.php` directly keeps it a
conscious, local-only decision.

If you'd rather read the source directly (e.g. because you need to change what's assigned, not
just see it), search the responsible handler for `smarty->assign` calls. Some starting points:

| Template(s)                                    | Assigned by                          |
|--------------------------------------------------|-----------------------------------------|
| `products-list.tpl`                                | `app/handlers/products-list.php`        |
| `products-display.tpl`                             | `app/handlers/products-display.php`     |
| `posts-list.tpl`                                    | `app/handlers/posts-list.php`           |
| `posts-display.tpl`                                 | `app/handlers/posts-display.php`        |
| `events-list.tpl`                                    | `app/handlers/events-list.php`          |
| `events-display.tpl`                                 | `app/handlers/events-display.php`       |
| `wishlist_overview.tpl` / `wishlist_public.tpl`    | `app/handlers/wishlist.php`             |
| `orders.tpl` / `orders-list.tpl`                   | `app/handlers/orders.php`               |
| everything else (page content, layout variables)  | `app/app.php`                            |

As an example, `products-list.tpl` can rely on (among others): `$products` (the current page of
results), `$product_filter`, `$sort_urls`, `$pagination`, `$active_filter_tags` /
`$has_active_filters`, `$show_shopping_cart`, `$wishlist_login_uri`, `$categories`. `wishlist_public.tpl`
can rely on `$wishlist`, `$wishlist_items`, `$wishlist_is_owner` and `$form_action`. Variables
assigned with the third `$smarty->assign()` argument set to `true` (nocache) are safe to use
even when Smarty caching is enabled for that page.
