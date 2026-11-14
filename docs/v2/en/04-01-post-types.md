---
title: Blog - Post types
description: The six kinds of blog entries and which fields they have
btn: Post types
group: backend
priority: 300
---

# Post types

<kbd>Backend</kbd> ▶ <kbd>Blog</kbd> ▶ <kbd>New</kbd>

Every blog entry has one of six types. The type determines which fields the form has and how the
entry is displayed in the frontend. It is set when you save the entry for the first time.

| Type      | Used for                                     | Own fields                                  | Detail page in sitemap |
|-----------|----------------------------------------------|---------------------------------------------|------------------------|
| Message   | Classic blog article with intro and text     | Text (Content tab)                          | yes                    |
| Image     | A single image with a description            | -                                           | yes                    |
| Gallery   | Several uploaded images                      | Image upload                                | yes                    |
| Video     | An embedded YouTube video                    | Video URL                                   | yes                    |
| Link      | A pointer to an external website             | URL, link text                              | no                     |
| Download  | A file to download                           | File, external file, version, license       | yes                    |

All types share the <kbd>SEO</kbd>, <kbd>Settings</kbd> and <kbd>Addons</kbd> tabs and the sidebar
(see [Options](04-02-post-options.md) and [SEO, URLs & RSS](04-03-post-seo.md)).


## Common fields

These fields exist for (almost) all types:

### Title

The headline of the entry - in the list and on the detail page. If you don't set your own
[slug](04-03-post-seo.md#slug), it is created from the title when you save for the first time.
If no meta title is entered, the title is also used as the page title (`<title>`).

### Description

The introductory text (teaser), formatted with the editor. It is shown in the list and appears on
the detail page above the actual content. For all types except *Message* and *Download*, the
description is the only text of the entry.

If no meta description is entered on the <kbd>SEO</kbd> tab, the description (without HTML) is
used for it.

### Images

With the image picker you select images from your uploads. The **first** selected image is used:

* as the thumbnail in the list in the frontend and in the backend,
* on the detail page (for *Message* and *Image*),
* as the preview image when shared on social networks (`og:image`).

If an entry has no image, the thumbnail of the page or the global thumbnail is used for sharing.
The gallery has no image picker, it uses its uploaded images.


## Message {#message}

The default type for articles. The form has two tabs:

* <kbd>Intro</kbd> - title, description and images.
* <kbd>Content</kbd> - the actual text of the article.

The list shows image, title and description with a button to read more. The detail page shows
image, description and text one after another.


## Image {#image}

For an entry that centers on one image. There is only the <kbd>Content</kbd> tab with title,
description and image picker. The first image is shown large together with the description in the
list and on the detail page.


## Gallery {#gallery}

For a collection of images. Unlike the other types, you don't pick the images but upload them
directly into the gallery.

1. Enter title and description and **save** the entry. Only then is the <kbd>Upload</kbd> button
   active, because the images are assigned to the ID of the gallery.
2. Upload the images via <kbd>Upload</kbd> (several at once are possible).
3. The uploaded images appear under *Uploads*. There you can change their order and delete
   individual images.

When uploading, every image is scaled down to the maximum image and thumbnail size set under
<kbd>Settings</kbd> ▶ <kbd>General</kbd>. The images are stored in
`/assets/galleries/<year>/gallery<ID>/` - the year is the year the entry was created.

In the frontend, the list shows the first five thumbnails and a button like *"Show 12 Images"*.
The detail page shows all images of the gallery.

When the gallery is deleted, all of its images are deleted too.


## Video {#video}

For a YouTube video. The <kbd>Content</kbd> tab has the additional field **URL (YouTube)**.

Simply enter the address of the video as you copy it on YouTube. These forms are recognized:

* `https://www.youtube.com/watch?v=VIDEO-ID`
* `https://youtu.be/VIDEO-ID`
* `https://www.youtube.com/embed/VIDEO-ID`
* `https://www.youtube.com/shorts/VIDEO-ID`

The video ID is read from the address and the video is embedded in the list and on the detail page.
If no valid ID is found, the video player stays empty.

The selected image serves as the thumbnail, e.g. when sharing. Videos have no *Source* field.


## Link {#link}

For a pointer to another website. The <kbd>Content</kbd> tab additionally has:

* **URL** - the target address of the link.
* **Text** - the label of the link button, e.g. *"Read the article"*.

Visitors are not sent to the target directly but through a redirect by SwiftyEdit. Every click is
counted; you see the number in the overview in the backend. Link entries are not included in the
XML sitemap and have no *Source* field.


## Download {#file}

For a file visitors can download. Besides <kbd>Intro</kbd> and <kbd>Content</kbd> (as for the
message) there is the <kbd>Files</kbd> tab:

| Field          | Meaning                                                                                                    |
|----------------|------------------------------------------------------------------------------------------------------------|
| Select a file  | A file from your uploads (directory `/assets/files/`). Only allowed file types are offered.               |
| Version        | Shown on the download button, e.g. *"Download 1.2.0"*.                                                     |
| External file  | The full URL of a file on another server. If this field is filled, it takes precedence over the selected file. |
| License        | A note about the license, shown below the download button.                                                |

Every download is counted - also for external files, which are redirected through SwiftyEdit.
You see the number in the overview in the backend.

You have to upload the file via <kbd>Uploads</kbd> first so that it appears in the selection.

The list shows the description and the download button. The detail page additionally shows the text
from the <kbd>Content</kbd> tab, e.g. for detailed instructions or a changelog.
