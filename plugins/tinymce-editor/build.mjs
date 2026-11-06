// Build script for the TinyMCE editor plugin.
// Copies the browser assets from node_modules (and the bundled language files)
// into the web-served editor asset folder public/assets/editors/tinymce.
import fs from 'fs-extra';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const out = path.resolve(__dirname, '../../public/assets/editors/tinymce');

// Package metadata from the top level of the npm package is not needed at
// runtime and would only disclose the version. License files stay in place.
const metadataFiles = ['package.json', 'composer.json', 'bower.json', 'CHANGELOG.md'];

const copies = [
    { src: 'node_modules/tinymce',                                         dest: out, exclude: metadataFiles },
    { src: 'node_modules/@tinymce/tinymce-jquery/dist/tinymce-jquery.js',  dest: path.join(out, 'jquery/tinymce-jquery.js') },
    { src: 'langs',                                                        dest: path.join(out, 'langs') },
];

fs.emptyDirSync(out);
for (const { src, dest, exclude = [] } of copies) {
    const srcPath = path.resolve(__dirname, src);
    fs.copySync(srcPath, dest, {
        overwrite: true,
        filter: (file) => !exclude.includes(path.relative(srcPath, file)),
    });
    console.log(`✓ Copied ${src} → ${dest}`);
}
