---
title: Basics
description: SwiftyEdit basics
btn: Basics
group: administrators
priority: 100
---

# Dashboard

Here you can find all the information about your website at a glance.

The latest entries are listed on the left-hand side.
You can click directly on the individual entries to edit them.
You can also create new entries directly.

On the right-hand side you will find a log, current warnings and information on the software used.

Tipp: If you are working on a theme or similar and have not activated the “Smarty Compile Check” option,
you can also clear the Smarty cache here.

---

### Categories {#categories}

To structure your site, you can create an unlimited number of categories.
[Pages](02-00-pages.md), blog posts, products and events can be assigned to these categories.

The categories are multilingual. 
This means that the language of a category must match the language of the assigned data record.

#### Input fields

| Field       | Type       | Description                                                               |
|-------------|------------|---------------------------------------------------------------------------|
| Title       | `Text`     | Category title                                                            |
| Priority    | `Number`   | The priority (and the language) determines the sorting of the categories. |
| Language    | `Select`   | The language of the category                                              |
| Thumbnail   | `File`     | An image for this category can be selected here.                          |
| Description | `Textarea` | The description of the category                                           |
