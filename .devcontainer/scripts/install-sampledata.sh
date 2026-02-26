#!/usr/bin/env bash
set -euo pipefail

MAGENTO_ROOT="${MAGENTO_ROOT:-/var/www/magento}"
PHP_BIN="php -d memory_limit=-1"

XDEBUG_MODE=off ${PHP_BIN} bin/magento sampledata:deploy
XDEBUG_MODE=off ${PHP_BIN} bin/magento module:enable Magento_CatalogSampleData Magento_BundleSampleData Magento_GroupedProductSampleData Magento_DownloadableSampleData Magento_ThemeSampleData Magento_ConfigurableSampleData Magento_ReviewSampleData Magento_OfflineShippingSampleData Magento_CatalogRuleSampleData Magento_TaxSampleData Magento_SalesRuleSampleData Magento_SwatchesSampleData Magento_MsrpSampleData Magento_CustomerSampleData Magento_CmsSampleData Magento_AdminAdobeImsTwoFactorAuth Magento_SalesSampleData Magento_ProductLinksSampleData Magento_WidgetSampleData Magento_WishlistSampleData
XDEBUG_MODE=off ${PHP_BIN} bin/magento setup:upgrade
XDEBUG_MODE=off ${PHP_BIN} bin/magento setup:di:compile
XDEBUG_MODE=off ${PHP_BIN} bin/magento indexer:reindex
XDEBUG_MODE=off ${PHP_BIN} bin/magento cache:flush

echo "Sample data installed."
