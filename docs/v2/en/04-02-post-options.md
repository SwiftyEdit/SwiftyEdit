---
title: Blog - Options
description: Language, categories, tags, release, status, comments and other options of an entry
btn: Options
group: backend
priority: 300
---

# Options of an entry

<kbd>Backend</kbd> ▶ <kbd>Blog</kbd> ▶ <kbd>Edit</kbd>

These options exist for all [post types](04-01-post-types.md). Most of them are in the sidebar to
the right of the form, some on the <kbd>Settings</kbd> tab.


## Sidebar

| Option             | Default                   | In short                                                         |
|--------------------|---------------------------|------------------------------------------------------------------|
| Language           | current backend language  | On blog pages of which language the entry appears                |
| Categories         | none                      | Under which categories the entry is listed                       |
| Tags               | none                      | Keywords that help visitors find related content                 |
| Release date       | now                       | From when the entry is visible and how it is sorted              |
| Hide detail page   | off                       | Show the entry in the list only, without a linked detail page    |
| Priority           | 0                         | Order within one day                                             |
| Fixed on top       | off                       | The entry always stays at the very top                           |
| Status             | Draft                     | Public, or visible to administrators only                        |
| Comments           | No                        | Visitors may comment on the entry                                |
| Votings/Reactions  | from the user settings    | Thumbs up/down below the entry                                   |
| Labels             | none                      | Colored markers for internal organization                        |

### Language {#language}

An entry only appears on blog pages whose language matches the language of the entry. If you run a
multilingual website, you create separate entries for each language.

The language also determines which page is used for the URL of the detail page
(see [SEO, URLs & RSS](04-03-post-seo.md#main-category)).

### Categories {#categories}

You create categories under <kbd>Categories</kbd>. An entry can be assigned to any number of
categories. Categories are used:

* **on the blog page** - if a page is limited to certain categories, it only shows entries from
  these categories. An entry without a matching category does not appear there.
* **as navigation** - each category has its own URL below the blog page, e.g. `/blog/news/`,
  which lists only its entries.
* **in the list** - the categories of an entry are shown as links.

Categories belong to a language. A blog page only shows categories in the language of the page.

### Tags {#tags}

Tags are freely chosen keywords. You enter them separated by commas. In the frontend they are shown
below the entry as links. A click on a tag leads

* to the page with the type of use *Tag Archive*, if there is one - it shows all content (entries,
  products, events) with this tag,
* otherwise to the blog page, filtered by this tag.

Tags are not the same as the **keywords** on the <kbd>SEO</kbd> tab - keywords only end up in the
`keywords` meta tag and are not visible to visitors.

### Release date {#release-date}

The date and time from which an entry is publicly visible. New entries get the current time. If you
set a date in the future, you can write entries in advance: they appear automatically once that
time is reached - provided the status is <kbd>Public</kbd>.

The date is also

* shown with the entry in the frontend,
* used for [sorting](04-00-blog.md#sorting) (only the day, not the time),
* used as the time of the entry in the [RSS feed](04-03-post-seo.md#rss).

### Hide detail page {#detail-page}

If you enable this option, the entry is only shown in the list and the title is not linked. This is
useful for short notes that don't need an article of their own.

At the same time

* RSS is switched off for this entry,
* the entry is removed from the XML sitemap.

### Priority and fixed on top {#priority}

Entries are sorted by release date. The **priority** orders entries of the same day: the higher the
number, the higher the entry. With **Fixed on top** an entry comes before all others, regardless of
its date. If several entries are fixed, the normal sorting applies among them.

You can also change both directly in the overview (star and priority field).
Details: [Sorting in the frontend](04-00-blog.md#sorting).

### Status {#status}

| Status | Meaning                                                                                                   |
|--------|-----------------------------------------------------------------------------------------------------------|
| Public | The entry is visible to all visitors once the release date is reached.                                    |
| Draft  | Only logged-in administrators see the entry, with a notice that it is a draft. It does not appear in the sitemap. |

New entries start as drafts.

### Comments {#comments}

With <kbd>Yes</kbd>, the comment section is shown below the detail page of the entry. Who may
comment, whether comments need approval and how long an entry stays open for comments is set under
<kbd>Settings</kbd> ▶ <kbd>User</kbd>.

### Votings/Reactions {#votings}

Visitors can rate an entry with thumbs up or down.

| Setting               | Meaning                                                                    |
|-----------------------|----------------------------------------------------------------------------|
| Deactivated           | No voting buttons are shown.                                               |
| For registered users  | The buttons are shown, but only logged-in users can vote.                  |
| For all Users         | Visitors without an account can vote too.                                  |

Everyone can vote only once per entry. The default for new entries comes from
<kbd>Settings</kbd> ▶ <kbd>User</kbd>.

### Labels {#labels}

Labels are colored markers you create under <kbd>Settings</kbd> ▶ <kbd>Labels</kbd>
(see [Settings](08-00-settings.md#labels)). They are meant for internal organization (e.g.
*"Needs review"* or *"Autumn campaign"*) and are shown in the overview in the backend. They don't
appear in the frontend.


## Settings tab {#settings-tab}

| Field   | Meaning                                                                                                    |
|---------|------------------------------------------------------------------------------------------------------------|
| Author  | Free text, e.g. your name. Shown with the entry in the frontend.                                           |
| Source  | The source of the content, e.g. for adopted news. Not available for video and link. The default theme doesn't display the source. |
| RSS     | Should the entry be included in the RSS feed? See [RSS](04-03-post-seo.md#rss).                            |
| RSS URL | The link of the entry in the feed. See [RSS](04-03-post-seo.md#rss).                                       |


## Addons tab

Installed plugins can add their own fields for blog entries here. If no such plugin is active, the
tab stays empty.
