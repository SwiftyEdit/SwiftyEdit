---
title: Blog - SEO, URLs & RSS
description: Metadata, slug, main category page, canonical URL, RSS feed and sitemap of blog entries
btn: SEO, URLs & RSS
group: backend
priority: 300
---

# SEO, URLs & RSS

<kbd>Backend</kbd> ▶ <kbd>Blog</kbd> ▶ <kbd>Edit</kbd> ▶ <kbd>SEO</kbd>

On the <kbd>SEO</kbd> tab you define how an entry appears in search engines and under which URL it
can be reached. All fields are optional - if you leave them empty, SwiftyEdit creates sensible
values.


## Metadata

| Field       | Used for                                       | If empty                                                  |
|-------------|------------------------------------------------|-----------------------------------------------------------|
| Title       | Page title (`<title>`) of the detail page      | The title of the entry                                    |
| Description | Meta description of the detail page            | The description (teaser) of the entry without HTML        |
| Keywords    | `keywords` meta tag of the detail page         | No keywords tag                                           |

The fallback values for title and description are filled in when you save. If you later change the
title or the description of the entry, the metadata stays unchanged - clear the fields if they
should be taken over again.

The keywords are also searched by the search in the backend. They have nothing to do with the
[tags](04-02-post-options.md#tags) in the sidebar.


## Slug {#slug}

The slug is the readable part of the URL, e.g. `my-first-entry`. If you leave the field empty, it is
created from the title when saving. The full URL of the detail page is:

```
/<page>/<slug>-<ID>.html
```

Because the ID is part of the URL, the slug doesn't have to be unique.


## Main category page {#main-category}

An entry can appear on several blog pages, e.g. on `/blog/` and on `/news/`. With the main category
page you define which of these pages is the authoritative one. All pages that display blog entries
are available for selection.

With <kbd>Use default</kbd>, SwiftyEdit takes, in this order:

1. the page with the type of use *Display Posts / Products / Events* in the language of the entry,
2. otherwise the first blog page in the language of the entry that displays messages.

The [canonical URL](#canonical-url) and the [RSS URL](#rss) are built from the main category page
and the slug.


## Canonical URL {#canonical-url}

The canonical URL tells search engines which URL is the authoritative one for an entry - useful
when an entry also appears on other pages or under several categories.

If the field is empty, SwiftyEdit builds the canonical URL on save from the
[main category page](#main-category), the slug and the ID, e.g.
`https://example.com/blog/my-first-entry-12.html`. As long as you don't change the automatically
built value, it is adjusted when you later change the slug or the main category page.

If you need the canonical URL to point somewhere else instead - for example a dedicated landing page
you built for it, or a URL the entry used before it was renamed - enter that URL in the
*Canonical URL* field. A URL you entered yourself stays unchanged. If you clear the field, the
automatic value is used again on save.


## RSS {#rss}

On the <kbd>Settings</kbd> tab you decide whether an entry is included in the RSS feed of your
website. The feed is available at `/rss.php` (RSS) or `/rss.php?type=atom` (Atom).

| Field   | Meaning                                                                                                |
|---------|--------------------------------------------------------------------------------------------------------|
| RSS     | With <kbd>Yes</kbd>, the entry is added to the feed (or updated there) when saving, with <kbd>No</kbd> it is removed from the feed. Default: <kbd>No</kbd>. |
| RSS URL | The link the feed item points to. It is built and adjusted the same way as the [canonical URL](#canonical-url). |

The feed shows the title and the description of the entry. Its time is the
[release date](04-02-post-options.md#release-date): an entry with a date in the future only appears
in the feed afterwards. Additionally you can set an *RSS Time offset* under <kbd>Settings</kbd> ▶
<kbd>General</kbd> to hold entries back for a while after saving.

Good to know:

* Feed items older than 30 days are removed automatically.
* If the entry is deleted, it disappears from the feed immediately.
* [Hide detail page](04-02-post-options.md#detail-page) switches RSS off automatically.


## XML sitemap

The sitemap of the blog entries (`/sitemap-posts.xml`) is regenerated every time an entry is saved
or deleted. It contains all public entries, except

* entries of the type *Link* (they have no content of their own),
* entries with [Hide detail page](04-02-post-options.md#detail-page).

The canonical URL is used as the URL, as long as it points to your own website.
