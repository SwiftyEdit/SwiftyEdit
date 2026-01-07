---
title: Pages
description: Manage all created pages
btn: Pages
group: backend
priority: 190
---

# Pages

<kbd>Backend</kbd> <kbd>Contents</kbd> <kbd>Show all Pages</kbd>

## Ordered pages {#ordered-pages}
The pages that contain a sorting number are listed as ordered pages.
These pages are automatically listed in the (main) navigation.
On the overview they are listed in the left column.

## Single pages {#single-pages}
The unordered pages are not included in the navigation -
but are still included in the sitemap or search results.
The unordered pages are listed in the right column.

You can filter the pages by status (public, invisible, private, draft) and language.
In addition, there is the search field.

## Edit pages

How to sort pages easily

## Single pages

- Single pages do not have a sorting function.
- They are not included in the navigation.
- The Position input field has no function here.

## Portal

- Portal pages could also be described as start pages.
- Each activated or used language should have a portal page.
- The Position input field also has no function here.

## Main menu

- Main menu pages, as the name suggests, create an entry in the main menu.
- The Position field determines where the page is sorted.

## This page is a subpage of ...

- Here, a page (as a subpage) can be added to the menu.
- The value from the Position field is automatically appended to the parent page.

### Sorting example

| Sorting | Page |
| ---------- | ----- |
| 100 | Home |
| 200 | Products |
| 200.100 | Apples |
| 200.200 | Pears |
| 300 | Contact |


#### Result:

- Home
- Products
    - Apples
    - Pears
- Contact

```html
<ul>
    <li>Home</li>
    <li>Products
        <ul>
            <li>Apples</li>
            <li>Pears</li>
        </ul>
    </li>
    <li>Contact</li>
</ul>
```


### Activate the Blog, Events or the Shop

As soon as you select a template here, the content of this page
will be replaced by one of the following Modules.

The template <kbd>message</kbd>, <kbd>image</kbd>, <kbd>gallery</kbd>, <kbd>video</kbd>,
<kbd>Link</kbd> and <kbd>Download</kbd> activate the blog.

The template <kbd>Event</kbd> activates the event module and <kbd>Product</kbd> activates the shop.


### Status {#page-status}

| Status      | Beschreibung                                                                       |
|-------------|------------------------------------------------------------------------------------|
| Public      | The pages are visible for all                                                      |
| Ghost       | The page is visible to all but is not listed in navigations, the sitemap or search |
| Private     | Only administrators or approved user groups can view the page                      |
| Draft       | Only administrators can view the page                                              |
| Redirection | The page cannot be accessed because it immediately redirects to another address    |


### Usage {#page-usage}

SwiftyEdit has a separate URL structure for each page type.
Example: /profile/ for the profile page or /checkout/ for the shopping cart.
If you want to customize these pages/URLs, you can simply create new pages and adjust the usage type accordingly.

#### Normal Page

This is the default value and is used for all pages that do not serve a specific purpose.

#### Register

This page is used to create new users.

#### Profile

Here, users can change their account details (contact information, password, etc.).

#### Search

The page for search results

#### Reset Password

#### 404 (Page not found)

#### Display Posts / Products / Events

As soon as you create a page with this usage type, all entries from the Blog, Shop, and Events modules
are displayed on this page.

For example, if you have multiple catalog pages (i.e., pages listing products from the shop)
on which identical products are listed, you can display all products on the /details/ page.

#### Imprint

#### Privacy Policy

#### Legal

#### Checkout

#### Orders