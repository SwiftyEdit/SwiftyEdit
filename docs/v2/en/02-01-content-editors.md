---
title: Pages - Editors
description: How to switch between editors when editing page content
btn: Editors
group: backend
priority: 190
---

# Editors when editing content

<kbd>Backend</kbd> ▶ <kbd>Pages</kbd> ▶ <kbd>Edit page</kbd>

Two independent things can come into play when editing a page's content field:
**which input tool** you're using, and **which format** the content is stored in. Most
of the time you'll only ever see the first one - the second only appears once an
additional format plugin is installed.

## Input tool: WYSIWYG, code, or plain text

At the top right of the content field you'll find a switch bar (e.g. "TinyMCE", "ACE",
"Text"). It only decides which tool you're currently typing with - the stored content
stays ordinary HTML in all three cases. You can switch between these tools at any time
without losing anything.

## Switching format (only shown if installed)

If at least one **format editor** plugin is installed and activated in addition to the
normal HTML editor (e.g. a Markdown editor or a drag & drop block builder), a second
dropdown appears next to the tool switch bar, listing that editor's name alongside
"Legacy (HTML)".

This decides which *format* the page's content is stored in at all - not just which
tool you use to edit it. A format editor can store and edit content in a fundamentally
different way than the normal HTML editor (e.g. as Markdown text, or as a tree of
individual building blocks).

**Important:** Switching format **cannot be undone** without rewriting the content:

- Switching from Legacy HTML (or another format) to a format editor starts you with
  **empty** content - your previous text/HTML is not carried over automatically. You'll
  be asked to confirm before this happens.
- Switching **to** "Legacy (HTML)", on the other hand, carries your existing content
  over as finished, static HTML - so you don't lose anything visible. Afterwards,
  though, the page can no longer be edited with the previous format editor, only as
  ordinary HTML.

You still need to **save** the page afterwards for the new state to persist - until you
do, you can undo a format switch at any time by leaving the page without saving.

## Fullscreen mode

For format editors (e.g. a block builder with multiple columns), the normal form width
can get cramped quickly. The icon next to the format dropdown opens the editor in a
fullscreen window - editing itself works exactly the same, you just get more room.

## What happens if a format editor is uninstalled?

Pages created with a format editor keep rendering normally on the frontend even after
the corresponding plugin is later deactivated or removed - the last saved content was
already captured as HTML. Only **editing** that page with that specific format editor
stops being possible while the plugin is missing. Reactivating the plugin later lets you
continue editing normally.

If you want to permanently detach a page from its format editor (e.g. before removing
the plugin), just switch its format to "Legacy (HTML)" and save - see above.
