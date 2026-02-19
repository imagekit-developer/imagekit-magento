#!/usr/bin/env bash
set -euo pipefail

MODULE_SOURCE="/workspace/imagekit-module"
MAGENTO_ROOT="${MAGENTO_ROOT:-/var/www/magento}"
MODULE_TARGET="${MAGENTO_ROOT}/app/code/ImageKit/ImageKitMagento"
PHP_BIN="php -d memory_limit=-1"

fix_permissions() {
  if ! id -u www-data > /dev/null 2>&1; then
    return
  fi

  chown -R www-data:www-data "${MAGENTO_ROOT}"
  find "${MAGENTO_ROOT}" -type d -exec chmod 2775 {} +
  find "${MAGENTO_ROOT}" -type f -exec chmod 664 {} +
  chmod -R 775 "$MAGENTO_ROOT/var" "$MAGENTO_ROOT/generated" "$MAGENTO_ROOT/pub/static" "$MAGENTO_ROOT/pub/media" "$MAGENTO_ROOT/app/etc"
}

if [ ! -d "${MAGENTO_ROOT}" ]; then
  echo "Magento root not found: ${MAGENTO_ROOT}"
  exit 1
fi

if [ ! -f "${MAGENTO_ROOT}/bin/magento" ]; then
  echo "Magento CLI not found at ${MAGENTO_ROOT}/bin/magento"
  exit 1
fi

mkdir -p "$(dirname "${MODULE_TARGET}")"
rm -rf "${MODULE_TARGET}"
mkdir -p "${MODULE_TARGET}"

rsync -a --delete \
  --exclude '.git/' \
  --exclude '.github/' \
  --exclude '.devcontainer/' \
  --exclude 'vendor/' \
  --exclude '.DS_Store' \
  "${MODULE_SOURCE}/" "${MODULE_TARGET}/"

cd "${MAGENTO_ROOT}"

mkdir -p var generated pub/static pub/media app/etc
fix_permissions

if ! composer show imagekit/imagekit > /dev/null 2>&1; then
  COMPOSER_MEMORY_LIMIT=-1 composer require --no-interaction imagekit/imagekit:^4.0
fi

XDEBUG_MODE=off ${PHP_BIN} bin/magento module:enable ImageKit_ImageKitMagento
XDEBUG_MODE=off ${PHP_BIN} bin/magento setup:upgrade
XDEBUG_MODE=off ${PHP_BIN} bin/magento setup:di:compile
XDEBUG_MODE=off ${PHP_BIN} bin/magento cache:flush

fix_permissions

echo "ImageKit module synced and enabled."
