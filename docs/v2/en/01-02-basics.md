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
| Inhalt      | `wysiwyg`  | Description text for the category. Depending on the theme, this is displayed in the frontend. |

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