#!/usr/bin/env bash
set -euo pipefail

MAGENTO_ROOT="${MAGENTO_ROOT:-/var/www/magento}"
PHP_BIN="php -d memory_limit=-1"

XDEBUG_MODE=off ${PHP_BIN} bin/magento config:set admin/security/session_lifetime 31536000

echo "Admin session timeout set to 1 year."
