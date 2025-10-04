#!/bin/bash
# load env overrides if available
[ -f dev.env ] && source dev.env

BUILD=$("${PHP_BIN:-php}" -r "echo json_decode(file_get_contents('version.json'))->build;")

mkdir -p dist
mkdir -p dist/$BUILD
echo "Created directory for build $BUILD"

rsync -a --delete --exclude-from=.deployignore ./ dist/$BUILD/

# Plugins
mkdir -p dist/$BUILD/plugins/se_cash-pay dist/$BUILD/plugins/se_invoice-pay dist/$BUILD/plugins/se_paypal-pay
rsync -a plugins/se_cash-pay/ dist/$BUILD/plugins/se_cash-pay/
rsync -a plugins/se_invoice-pay/ dist/$BUILD/plugins/se_invoice-pay/
rsync -a plugins/se_paypal-pay/ dist/$BUILD/plugins/se_paypal-pay/

# Themes
rsync -a public/assets/themes/administration/ dist/$BUILD/public/assets/themes/administration/
rsync -a public/assets/themes/default/ dist/$BUILD/public/assets/themes/default/

# files for whitelist.json
BUILD_DIR="dist/$BUILD"
# check jq
if ! command -v jq &> /dev/null; then
    echo "Error: jq could not be found. Please install jq to create whitelist.json."
    exit 1
fi

find "${BUILD_DIR}/acp" "${BUILD_DIR}/app" "${BUILD_DIR}/install" "${BUILD_DIR}/languages" "${BUILD_DIR}/vendor" -type f | jq -R -s -c 'split("\n")[:-1]' > "${BUILD_DIR}/whitelist.json"

# clean up
find "${BUILD_DIR}/public/assets/themes/" -type d \( -name node_modules -o -name src \) -exec rm -rf '{}' +
find "${BUILD_DIR}/public/assets/themes/" -type f \( -name package.json -o -name package-lock.json -o -name webpack.config.js \) -delete
find "${BUILD_DIR}/plugins/" -name '*config.php' -type f -delete

echo "Build $BUILD ready with whitelist.json"