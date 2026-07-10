// Build script for the TinyMCE editor plugin.
// Copies the browser assets from node_modules (and the bundled language files)
// into the web-served editor asset folder public/assets/editors/tinymce.
import fs from 'fs-extra';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const out = path.resolve(__dirname, '../../public/assets/editors/tinymce');

const copies = [
    { src: 'node_modules/tinymce',                                         dest: out },
    { src: 'node_modules/@tinymce/tinymce-jquery/dist/tinymce-jquery.js',  dest: path.join(out, 'jquery/tinymce-jquery.js') },
    { src: 'langs',                                                        dest: path.join(out, 'langs') },
];

fs.emptyDirSync(out);
for (const { src, dest } of copies) {
    fs.copySync(path.resolve(__dirname, src), dest, { overwrite: true });
    console.log(`✓ Copied ${src} → ${dest}`);
}
