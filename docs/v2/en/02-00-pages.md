---
title: Pages
description: Manage all created pages
btn: Pages
group: backend
priority: 190
---

# Pages

<kbd>Backend</kbd> ▶ <kbd>Pages</kbd>

## Ordered pages {#ordered-pages}
Pages placed in the navigation tree (see [Sorting](#sorting) below) are listed as ordered pages.
These pages are automatically listed in the (main) navigation.
On the overview they are listed in the left column.

## Single pages {#single-pages}
Single pages are not included in the navigation -
but are still included in the sitemap or search results.
They are listed in the right column.

You can filter the pages by status (public, invisible, private, draft) and language.
In addition, there is the search field.

## Sorting {#sorting}

How to place a page in the navigation, on the *Position* tab.

Three mutually exclusive roles decide what a page is:

### Single page

- Single pages are not part of the navigation tree and don't appear in the main menu.
- They can still show up in the sitemap or search results.
- With "Single page" selected, the "Parent page" / "Insert position" fields below have no effect.

### Portal

- The portal page is a language's start page.
- Each activated language should have exactly one portal page.
- With "Portal" selected, "Parent page" / "Insert position" also have no effect.

### Main menu

- Places the page in the navigation tree - as a top-level entry, or nested under another page.
- **Parent page**: choose which page this one becomes a child of, or leave it at
  "Top level" for a top-level main menu entry directly under the portal page.
- **Insert position**: choose which of the parent's existing children this page is placed
  directly after (the arrow icon marks that) - or "At the beginning" to make it the first child.
  Picking a parent or an insert position automatically selects "Main menu" above, since
  otherwise the choice wouldn't have any effect on save.

### Example

Setting Flyer's parent page to "Home" (top level) and Posters' parent page to
"Flyer", inserted after it, results in:

- Home
- Flyer
    - Posters
- Contact

```html
<ul>
    <li>Home</li>
    <li>Flyer
        <ul>
            <li>Posters</li>
        </ul>
    </li>
    <li>Contact</li>
</ul>
```


### Activate the Blog, Events or the Shop

By selecting the post types here, you determine which entries are displayed on this page.
The actual page content is then replaced by these entries.

The post types <kbd>Message</kbd>, <kbd>Image</kbd>, <kbd>Gallery</kbd>, <kbd>Video</kbd>,
<kbd>Link</kbd> and <kbd>Download</kbd> activate the blog.

The post type <kbd>Event</kbd> activates the event module and <kbd>Product</kbd> activates the shop.


### Status {#page-status}

| Status      | Description                                                                        |
|-------------|------------------------------------------------------------------------------------|
| Public      | The pages are visible for all                                                      |
| Ghost       | The page is visible to all but is not listed in navigations, the sitemap or search |
| Private     | Only administrators or approved user groups can view the page                      |
| Draft       | Only administrators can view the page                                              |


### Redirection {#redirects}

The redirection is a separate field (not a status). If you enter a target address here,
the page immediately redirects to it. You can set the HTTP status code (e.g. 301 or 302)
via the corresponding select field.


### Usage {#page-usage}

SwiftyEdit ships with built-in default URLs for the most important functional pages
(registration, profile, search, password reset, checkout, orders, order withdrawal, lists,
displaying posts/products/events, and the 404 page) — for example /profile/ for the profile
page or /checkout/ for the shopping cart. These work automatically even if you haven't created
a page for them.

If you create your own page with the matching usage type instead, that page (with its own
permalink and surrounding content) replaces the default URL SwiftyEdit provides. This lets you,
for example, replace /profile/ with /my-account/.

Imprint, Privacy Policy, and Legal, on the other hand, have **no** built-in default URL — you
need to create a page with the corresponding usage type yourself so it can, for example, be
linked in the footer.

| Usage type                      | Default URL*                                  | Description                                                                                                    |
|----------------------------------|------------------------------------------------|------------------------------------------------------------------------------------------------------------------|
| Normal Page                     | –                                               | The default value, used for all pages that do not serve a specific purpose.                                    |
| Register                        | `/register/`                                    | Used to create new users.                                                                                       |
| Profile                         | `/profile/`                                     | Here, users can change their account details (contact information, password, etc.).                            |
| Search                          | `/search/`                                      | The page for search results.                                                                                    |
| Reset Password                  | `/password/`                                    | Used when a user needs to reset their password.                                                                 |
| 404 (Page not found)            | *(automatically catches any unknown URL)*       | Displayed when a page cannot be found, i.e. when the requested URL does not exist.                              |
| Display Posts / Products / Events | – (no fixed default path)                     | Displays all entries from the Blog, Shop, and Events modules on this page. Useful e.g. for extra catalog pages such as `/details/`. |
| Imprint                         | – (no default URL)                              | Used for the imprint.                                                                                           |
| Privacy Policy                  | – (no default URL)                              | Used for the privacy policy information.                                                                        |
| Legal                           | – (no default URL)                              | For any other legal information.                                                                                |
| Checkout                        | `/checkout/`                                    | For the shopping cart.                                                                                          |
| Orders                          | `/orders/`                                      | Here the user can view their orders.                                                                            |
| Order Withdrawal                | `/order_withdrawal/`                            | A form to withdraw from an order, see [below](#page-usage-order-withdrawal).                                    |
| Lists                           | `/wishlist/`                                    | The page for the [wishlist / lists feature](05-00-shop.md#lists), see [below](#page-usage-lists).               |

\* The default URL only applies as long as no page with this usage type has been created.
If you create a page with this usage type, its permalink replaces the default URL
(see explanation above).

#### Order Withdrawal {#page-usage-order-withdrawal}

A form customers can use to withdraw from an order (e.g. to fulfil the EU right of
withdrawal). The customer must enter the order number and the e-mail address stored
on the order; the request is then sent to the site administrator by e-mail. From the
"Orders" page, customers can open this form pre-filled for a specific order.

#### Lists {#page-usage-lists}

The page for the [wishlist / lists feature](05-00-shop.md#lists). Logged-in customers
use it to view and manage their personal lists; a single public list is also shown here
when opened via its share link. This usage type is only relevant if lists are enabled
in Settings → Shop.

---

## Sorting search results

Search results are sorted by relevance. The aim is to display pages that match the search term as closely as possible first.

The following criteria are taken into account in this order:

1.    __URL / permalink__<br>
      Pages whose URL contains the search term appear at the top of the results.
2.    __Meta keywords (exact match)__<br>
      Pages with an exact match of the search term in the meta keywords are ranked higher.
3.    __Meta keywords (partial match)__<br>
      Pages where the search term appears as part of the keywords (e.g., beginning of a word or part of a word) follow next.
4.    __Meta description__<br>
      Hits in the meta description are taken into account because they summarize the page content in a targeted manner.
5.    __Page title__<br>
      Pages whose titles contain the search term are also ranked higher.
6.    __Page content__<br>
      Hits in the actual page content are also taken into account, but after the URL, SEO data, and title.
7.    __Page priority__<br>
      If several pages are equally relevant, the manually assigned page priority decides.
      Pages with higher priority appear higher up.

In short: the closer the search term is to the URL, SEO data, and title,
the more relevant the result is.
The page content serves as a supplementary criterion.
If the relevance is the same, the page priority is decisive.