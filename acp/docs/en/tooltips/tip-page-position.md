---
title: Position
description: How to sort pages easily
group: tips
priority: 0
---

# How to sort pages easily

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

Translated with DeepL.com (free version)