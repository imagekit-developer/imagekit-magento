#!/usr/bin/env bash
set -euo pipefail

MAGENTO_ROOT="${MAGENTO_ROOT:-/var/www/magento}"
MAGENTO_VERSION="${MAGENTO_VERSION:-2.4.*}"
BASE_URL="${MAGENTO_BASE_URL:-http://localhost:8080/}"

DB_HOST="${MAGENTO_DB_HOST:-db}"
DB_NAME="${MAGENTO_DB_NAME:-magento}"
DB_USER="${MAGENTO_DB_USER:-magento}"
DB_PASSWORD="${MAGENTO_DB_PASSWORD:-magento}"
SEARCH_ENGINE="${MAGENTO_SEARCH_ENGINE:-opensearch}"
OPENSEARCH_HOST="${MAGENTO_OPENSEARCH_HOST:-opensearch}"
OPENSEARCH_PORT="${MAGENTO_OPENSEARCH_PORT:-9200}"

ADMIN_FIRSTNAME="${MAGENTO_ADMIN_FIRSTNAME:-Admin}"
ADMIN_LASTNAME="${MAGENTO_ADMIN_LASTNAME:-User}"
ADMIN_EMAIL="${MAGENTO_ADMIN_EMAIL:-admin@example.com}"
ADMIN_USER="${MAGENTO_ADMIN_USER:-admin}"
ADMIN_PASSWORD="${MAGENTO_ADMIN_PASSWORD:-Admin123!}"
ADMIN_URI="${MAGENTO_ADMIN_URI:-admin}"

MAGENTO_PUBLIC_KEY="${MAGENTO_PUBLIC_KEY:-}"
MAGENTO_PRIVATE_KEY="${MAGENTO_PRIVATE_KEY:-}"
PHP_BIN="php -d memory_limit=-1"

fix_permissions() {
  if ! id -u www-data > /dev/null 2>&1; then
    return
  fi

  chown -R www-data:www-data "${MAGENTO_ROOT}"
  find "${MAGENTO_ROOT}" -type d -exec chmod 2775 {} +
  find "${MAGENTO_ROOT}" -type f -exec chmod 664 {} +
  chown -R www-data:www-data /composer/
}

wait_for_db() {
  echo "Waiting for MariaDB at ${DB_HOST}..."
  until mysqladmin ping --silent --host="${DB_HOST}" --user="${DB_USER}" --password="${DB_PASSWORD}"; do
    sleep 2
  done
}

wait_for_opensearch() {
  if [ "${SEARCH_ENGINE}" != "opensearch" ]; then
    return
  fi

  echo "Waiting for OpenSearch at ${OPENSEARCH_HOST}:${OPENSEARCH_PORT}..."
  until curl --silent --fail "http://${OPENSEARCH_HOST}:${OPENSEARCH_PORT}" > /dev/null; do
    sleep 2
  done
}

cd "${MAGENTO_ROOT}"

mkdir -p var generated pub/static pub/media app/etc
fix_permissions

COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --prefer-dist

wait_for_db
wait_for_opensearch

if [ ! -f "${MAGENTO_ROOT}/app/etc/env.php" ]; then
  setup_install_args=(
    --base-url="${BASE_URL}"
    --db-host="${DB_HOST}"
    --db-name="${DB_NAME}"
    --db-user="${DB_USER}"
    --db-password="${DB_PASSWORD}"
    --admin-firstname="${ADMIN_FIRSTNAME}"
    --admin-lastname="${ADMIN_LASTNAME}"
    --admin-email="${ADMIN_EMAIL}"
    --admin-user="${ADMIN_USER}"
    --admin-password="${ADMIN_PASSWORD}"
    --backend-frontname="${ADMIN_URI}"
    --language=en_US
    --currency=USD
    --timezone=UTC
    --use-rewrites=1
    --search-engine="${SEARCH_ENGINE}"
  )

  if [ "${SEARCH_ENGINE}" = "opensearch" ]; then
    setup_install_args+=(
      --opensearch-host="${OPENSEARCH_HOST}"
      --opensearch-port="${OPENSEARCH_PORT}"
      --opensearch-enable-auth=0
    )
  fi

  XDEBUG_MODE=off ${PHP_BIN} bin/magento setup:install "${setup_install_args[@]}"
fi

fix_permissions

bash /var/www/magento/app/code/ImageKit/ImageKitMagento/.devcontainer/scripts/install-module.sh
bash /var/www/magento/app/code/ImageKit/ImageKitMagento/.devcontainer/scripts/disable-2fa.sh
bash /var/www/magento/app/code/ImageKit/ImageKitMagento/.devcontainer/scripts/install-sampledata.sh
bash /var/www/magento/app/code/ImageKit/ImageKitMagento/.devcontainer/scripts/set-admin-session-timeout.sh

echo
echo "Magento is ready at: ${BASE_URL}"
echo "Admin URL: ${BASE_URL}${ADMIN_URI}"
echo "Admin user: ${ADMIN_USER}"
echo "Admin password: ${ADMIN_PASSWORD}"
