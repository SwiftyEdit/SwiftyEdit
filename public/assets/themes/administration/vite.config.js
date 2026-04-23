import { defineConfig } from 'vite';
import autoprefixer from 'autoprefixer';
import cssnano from 'cssnano';
import fs from 'fs-extra';

function copyAssets() {
    return {
        name: 'copy-assets',
        closeBundle() {
            const copies = [
                { src: 'node_modules/bootstrap-icons/font/fonts/bootstrap-icons.woff',  dest: 'dist/fonts/bootstrap-icons.woff' },
                { src: 'node_modules/bootstrap-icons/font/fonts/bootstrap-icons.woff2', dest: 'dist/fonts/bootstrap-icons.woff2' },
                { src: 'node_modules/tinymce',                                           dest: 'dist/tinymce' },
                { src: 'node_modules/@tinymce/tinymce-jquery/dist/tinymce-jquery.js',   dest: 'dist/tinymce-jquery/tinymce-jquery.js' },
                { src: 'node_modules/ace-builds/src/mode-html.js',                      dest: 'dist/ace/mode-html.js' },
                { src: 'node_modules/ace-builds/src/theme-twilight.js',                 dest: 'dist/ace/theme-twilight.js' },
                { src: 'node_modules/ace-builds/src/theme-chrome.js',                   dest: 'dist/ace/theme-chrome.js' },
                { src: 'src/tinymce-languages',                                          dest: 'dist/tinymce-languages' },
                { src: 'node_modules/prismjs/themes',                                    dest: 'dist/prismjs' },
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
                backend: './src/js/backend.js',
            },
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name?.endsWith('.css')) return 'backend.css';
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