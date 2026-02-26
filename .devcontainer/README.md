# Dev Container (Magento Latest 2.4 E2E)

This dev container provides an end-to-end Magento 2.4 environment for this module.

## What is included

- PHP 8.2 + PHP-FPM
- Nginx
- MariaDB 10.6
- OpenSearch 2.x
- Xdebug
- Composer
- PHP_CodeSniffer + Magento Coding Standard
- PHPStan
- PHPUnit

## Prerequisites

To download Magento source on first bootstrap, export your Adobe Commerce Marketplace keys:

```bash
export MAGENTO_PUBLIC_KEY="<your-public-key>"
export MAGENTO_PRIVATE_KEY="<your-private-key>"
```

You can also create `.devcontainer/.env` from `.devcontainer/.env.example` and keep credentials there.

## First-time setup

1. Open this repository in VS Code.
2. Run **Dev Containers: Reopen in Container**.
3. In the container terminal, run:

```bash
.devcontainer/scripts/bootstrap-magento.sh
```

When done:

- Storefront: `http://localhost:8080/`
- Admin: `http://localhost:8080/admin`
- Default admin: `admin / Admin123!Admin123!`
- OpenSearch API: `http://localhost:9200/`

## Daily workflow

After editing this module, sync it into Magento and run upgrade:

```bash
.devcontainer/scripts/install-module.sh
```

## Useful commands

Run from inside `/var/www/magento`:

```bash
php bin/magento cache:flush
php bin/magento indexer:reindex
php bin/magento setup:di:compile
```

Run static analysis tools from the container:

```bash
phpcs --severity=9 --standard=Magento2 --ignore=*/vendor/* .
phpstan --version
phpunit --version
```
