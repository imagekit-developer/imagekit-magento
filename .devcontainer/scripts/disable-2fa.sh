#!/usr/bin/env bash
set -euo pipefail

MAGENTO_ROOT="${MAGENTO_ROOT:-/var/www/magento}"
PHP_BIN="php -d memory_limit=-1"

XDEBUG_MODE=off ${PHP_BIN} bin/magento module:disable Magento_AdminAdobeImsTwoFactorAuth Magento_TwoFactorAuth
XDEBUG_MODE=off ${PHP_BIN} bin/magento cache:flush

echo "2FA disabled."
