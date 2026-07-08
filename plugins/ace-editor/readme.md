# ACE Code Editor Plugin

Provides the source-code editor (ACE) for the SwiftyEdit backend. It registers
with the content editor switch under its editor id `ace` (labelled "ACE") and
also powers the read-only code viewers (snippet/template source modals,
`<textarea data-editor="...">`).

This is a **core editor plugin**: bundled with SwiftyEdit and always available.

## Assets

Browser assets live under `public/assets/editors/ace/` and are produced by the
build step:

```bash
npm install
npm run build
```

`build.mjs` copies `ace.js`, the HTML mode, both themes and the HTML worker from
`node_modules/ace-builds` into the web-served folder.
