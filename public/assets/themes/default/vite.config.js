import { defineConfig } from 'vite';
import autoprefixer from 'autoprefixer';
import cssnano from 'cssnano';
import fs from 'fs-extra';
import path from 'path';

/**
 * Every *.js file directly under src/js/components/ becomes its own build
 * entry (and therefore a selectable "theme component" - see
 * php/theme-components.php for the runtime side of this convention).
 * Helper modules that are only ever imported by an entry (theme_switch.js,
 * wishlist.js, ...) must not live in this folder - they belong in
 * src/js/lib/ instead, or they'd incorrectly show up as components too.
 */
function discoverComponentEntries() {
    const dir = 'src/js/components';
    const entries = {};
    for (const file of fs.readdirSync(dir)) {
        if (file.endsWith('.js')) {
            entries[path.basename(file, '.js')] = `./${dir}/${file}`;
        }
    }
    return entries;
}

function copyAssets() {
    return {
        name: 'copy-assets',
        closeBundle() {
            const copies = [
                { src: 'node_modules/bootstrap-icons/font/fonts/bootstrap-icons.woff',  dest: 'dist/fonts/bootstrap-icons.woff' },
                { src: 'node_modules/bootstrap-icons/font/fonts/bootstrap-icons.woff2', dest: 'dist/fonts/bootstrap-icons.woff2' },
                { src: 'src/editor.css',        dest: 'dist/editor.css' },
                { src: 'src/tinyMCE_config.js', dest: 'dist/tinyMCE_config.js' },
            ];
            for (const { src, dest } of copies) {
                fs.copySync(src, dest, { overwrite: true });
                console.log(`✓ Copied ${src} → ${dest}`);
            }
        }
    };
}


export default defineConfig({
    build: {
        outDir: 'dist',
        emptyOutDir: true,
        minify: 'terser',
        terserOptions: {
            keep_classnames: true,
            keep_fnames: true,
        },
        rollupOptions: {
            input: {
                core: './src/js/frontend.js',
                ...discoverComponentEntries(),
            },
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name?.endsWith('.css')) return '[name].css';
                    return 'assets/[name][extname]';
                },
                format: 'es',
            },
        },
    },
    css: {
        preprocessorOptions: {
            scss: {},
        },
        postcss: {
            plugins: [
                autoprefixer,
                cssnano({ preset: 'default' }),
            ],
        },
    },
    plugins: [
        copyAssets(),
    ],
});