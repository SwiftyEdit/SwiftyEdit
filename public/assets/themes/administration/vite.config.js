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
                { src: 'node_modules/prismjs/themes',                                    dest: 'dist/prismjs' },
            ];
            for (const { src, dest } of copies) {
                fs.copySync(src, dest, { overwrite: true });
                console.log(`✓ Copied ${src} → ${dest}`);
            }
        }
    };
}

/**
 * Write dist/THIRD-PARTY-LICENSES.txt with the license texts of all npm
 * packages in the build. Terser and cssnano drop the license comments,
 * MIT and similar licenses require the copyright notice to ship with the code.
 * Packages: everything bundled into the JS chunks plus the "dependencies" of
 * package.json (covers packages only used in SCSS or copied by copyAssets()).
 */
function thirdPartyLicenses() {
    const packageDir = (id) => {
        const match = id.replace(/\\/g, '/').match(/^(.*\/node_modules\/(?:@[^/]+\/)?[^/]+)\//);
        return match ? match[1] : null;
    };
    return {
        name: 'third-party-licenses',
        generateBundle(options, bundle) {
            const dirs = new Set();
            for (const chunk of Object.values(bundle)) {
                for (const id of chunk.moduleIds ?? []) {
                    const dir = packageDir(id);
                    if (dir) dirs.add(dir);
                }
            }
            const own = fs.readJsonSync('package.json');
            for (const name of Object.keys(own.dependencies ?? {})) {
                dirs.add(fs.realpathSync(`node_modules/${name}`));
            }

            const entries = [];
            for (const dir of dirs) {
                const pkgFile = `${dir}/package.json`;
                if (!fs.existsSync(pkgFile)) continue;
                const pkg = fs.readJsonSync(pkgFile);
                const licenseFile = fs.readdirSync(dir).find((f) => /^(licen[cs]e|copying)(\.|$)/i.test(f));
                // packages without a license file: author, license and source from package.json
                const author = typeof pkg.author === 'object' ? pkg.author?.name : pkg.author;
                const source = pkg.homepage ?? pkg.repository?.url ?? pkg.repository ?? '';
                const text = licenseFile
                    ? fs.readFileSync(`${dir}/${licenseFile}`, 'utf8').trim()
                    : `Copyright (c) ${author ?? 'the authors'}\nLicense: ${pkg.license ?? 'unknown'}\nSource: ${source}`;
                entries.push(`${pkg.name} ${pkg.version} - ${pkg.license ?? 'see below'}\n\n${text}`);
            }
            entries.sort();

            this.emitFile({
                type: 'asset',
                fileName: 'THIRD-PARTY-LICENSES.txt',
                source: 'Third-party packages included in this build\n\n'
                    + entries.join('\n\n' + '-'.repeat(72) + '\n\n') + '\n',
            });
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
                // QR code for the 2FA setup, also used on the login page (see src/js/twofa.js)
                twofa: './src/js/twofa.js',
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
        thirdPartyLicenses(),
    ],
});