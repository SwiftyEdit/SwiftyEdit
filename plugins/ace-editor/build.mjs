// Build script for the ACE editor plugin.
// Copies the standalone ACE browser assets from node_modules into the
// web-served editor asset folder public/assets/editors/ace.
import fs from 'fs-extra';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const out = path.resolve(__dirname, '../../public/assets/editors/ace');
const src = path.resolve(__dirname, 'node_modules/ace-builds/src-noconflict');

// Only the files actually used by the backend: core, HTML + Markdown modes,
// both themes and the HTML worker (workers are disabled at runtime, but
// shipped for safety).
const files = [
    'ace.js',
    'mode-html.js',
    'mode-markdown.js',
    'theme-chrome.js',
    'theme-twilight.js',
    'worker-html.js',
];

fs.emptyDirSync(out);
for (const file of files) {
    fs.copySync(path.join(src, file), path.join(out, file), { overwrite: true });
    console.log(`✓ Copied ${file} → ${path.join(out, file)}`);
}
