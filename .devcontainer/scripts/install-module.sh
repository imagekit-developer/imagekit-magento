#!/usr/bin/env bash
set -euo pipefail

MAGENTO_ROOT="${MAGENTO_ROOT:-/var/www/magento}"
PHP_BIN="php -d memory_limit=-1"

if [ ! -f "${MAGENTO_ROOT}/bin/magento" ]; then
  echo "Magento CLI not found at ${MAGENTO_ROOT}/bin/magento"
  exit 1
fi

cd "${MAGENTO_ROOT}"

if ! composer show imagekit/imagekit > /dev/null 2>&1; then
  COMPOSER_MEMORY_LIMIT=-1 composer require --no-interaction imagekit/imagekit:^4.0
fi

XDEBUG_MODE=off ${PHP_BIN} bin/magento module:enable ImageKit_ImageKitMagento
XDEBUG_MODE=off ${PHP_BIN} bin/magento setup:upgrade
XDEBUG_MODE=off ${PHP_BIN} bin/magento setup:di:compile
XDEBUG_MODE=off ${PHP_BIN} bin/magento cache:flush

echo "ImageKit module installed and enabled."
