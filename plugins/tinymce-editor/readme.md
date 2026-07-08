# TinyMCE Editor Plugin

Provides the WYSIWYG content editor (TinyMCE) for the SwiftyEdit backend.

This is a **core editor plugin**: it is bundled with SwiftyEdit and always
available (like the payment plugins). It registers itself with the ACP editor
switch under its editor id `tinymce` and appears there labelled "TinyMCE".

## Assets

Browser assets live under `public/assets/editors/tinymce/` and are produced by
the build step:

```bash
npm install
npm run build
```

`build.mjs` copies TinyMCE from `node_modules`, the `tinymce-jquery` adapter,
and the bundled language files in `langs/` into the web-served folder.

## Theme config override (optional)

The plugin ships a built-in TinyMCE config. If the **active theme** provides a
full `tinyMCE_config.js` (resolved from `dist/` › `config/` › `js/` by
`acp/core/editors.php`), that file replaces the built-in config. It is a normal
`$(function(){ $('textarea.mceEditor, textarea.mceEditor_small').tinymce({ … }); })`
script and should point `base_url` at `/assets/editors/tinymce/` and
`language_url` at `/assets/editors/tinymce/langs/`. The bundled `default` theme
ships such a file as a reference.

