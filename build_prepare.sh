#!/bin/bash
# load env overrides if available
[ -f dev.env ] && source dev.env

BUILD=$("${PHP_BIN:-php}" -r "echo json_decode(file_get_contents('version.json'))->build;")

mkdir -p dist
mkdir -p dist/$BUILD
echo "Created directory for build $BUILD"

rsync -a --delete --exclude-from=.deployignore ./ dist/$BUILD/
rsync -a config.php dist/$BUILD/config.php

# Plugins
mkdir -p dist/$BUILD/plugins/se_cash-pay dist/$BUILD/plugins/se_invoice-pay dist/$BUILD/plugins/se_paypal-pay
rsync -a plugins/se_cash-pay/ dist/$BUILD/plugins/se_cash-pay/
rsync -a plugins/se_invoice-pay/ dist/$BUILD/plugins/se_invoice-pay/
rsync -a plugins/se_paypal-pay/ dist/$BUILD/plugins/se_paypal-pay/

# Editor plugins (bundled and always available, like the payment plugins).
# Ship only the runtime files; the build tooling stays out of the release.
EDITOR_EXCLUDES="--exclude node_modules --exclude package.json --exclude package-lock.json --exclude build.mjs"
mkdir -p dist/$BUILD/plugins/tinymce-editor dist/$BUILD/plugins/ace-editor
rsync -a $EDITOR_EXCLUDES plugins/tinymce-editor/ dist/$BUILD/plugins/tinymce-editor/
rsync -a $EDITOR_EXCLUDES plugins/ace-editor/     dist/$BUILD/plugins/ace-editor/

# Themes
# The compiled dist/ assets (CSS/JS) of each theme are gitignored build
# artifacts, so they have to be (re-)built here rather than relying on
# whatever happens to already sit in the working tree.
if ! command -v npm &> /dev/null; then
    echo "Error: npm could not be found. Please install Node.js/npm to build theme assets."
    exit 1
fi

for theme in administration default; do
    theme_dir="public/assets/themes/$theme"
    echo "Building theme assets: $theme"
    if [ -f "$theme_dir/package-lock.json" ]; then
        (cd "$theme_dir" && npm ci)
    else
        (cd "$theme_dir" && npm install)
    fi
    if [ $? -ne 0 ]; then
        echo "Error: npm install failed for theme $theme."
        exit 1
    fi
    (cd "$theme_dir" && npm run build)
    if [ $? -ne 0 ]; then
        echo "Error: npm run build failed for theme $theme."
        exit 1
    fi
done

rsync -a public/assets/themes/administration/ dist/$BUILD/public/assets/themes/administration/
rsync -a public/assets/themes/default/ dist/$BUILD/public/assets/themes/default/

# Editor browser assets (served from /assets/editors)
mkdir -p dist/$BUILD/public/assets/editors
rsync -a public/assets/editors/ dist/$BUILD/public/assets/editors/

# files for whitelist.json
BUILD_DIR="dist/$BUILD"
# check jq
if ! command -v jq &> /dev/null; then
    echo "Error: jq could not be found. Please install jq to create whitelist.json."
    exit 1
fi

# clean up (must run BEFORE the whitelist is generated, so build tooling
# files like node_modules/src/package.json never end up in whitelist.json)
find "${BUILD_DIR}/public/assets/themes/" -type d \( -name node_modules -o -name src \) -exec rm -rf '{}' +
find "${BUILD_DIR}/public/assets/themes/" -type f \( -name package.json -o -name package-lock.json -o -name webpack.config.js \) -delete
find "${BUILD_DIR}/plugins/" -name '*config.php' -type f -delete

# The bundled themes (administration, default) are core parts of the release
# just like acp/app/languages/vendor, so their files are whitelisted too -
# this lets the updater clean up stale/renamed theme files on update.
# User-installed/custom themes are NOT part of the whitelist and stay
# untouched by the updater's cleanup.
find "${BUILD_DIR}/acp" "${BUILD_DIR}/app" "${BUILD_DIR}/install" "${BUILD_DIR}/languages" "${BUILD_DIR}/vendor" "${BUILD_DIR}/public/assets/themes/administration" "${BUILD_DIR}/public/assets/themes/default" -type f | sed "s|^${BUILD_DIR}/||" | jq -R -s -c 'split("\n")[:-1]' > "${BUILD_DIR}/whitelist.json"

echo "Build $BUILD ready with whitelist.json"