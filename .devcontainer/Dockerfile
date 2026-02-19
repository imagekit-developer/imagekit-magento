FROM php:8.2-fpm-bullseye

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
    bash \
    git \
    curl \
    procps \
    rsync \
    unzip \
    zip \
    ca-certificates \
    default-mysql-client \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libxslt1-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
      bcmath \
      curl \
      ftp \
      gd \
      intl \
      mbstring \
      mysqli \
      pdo_mysql \
      soap \
      sockets \
      xsl \
      zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_HOME=/composer
ENV PATH="/composer/vendor/bin:${PATH}"

RUN mkdir -p /composer \
    && git config --global --add safe.directory /workspace/imagekit-module \
    && composer --version \
    && composer global config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true \
    && composer global require --no-interaction --prefer-dist \
      squizlabs/php_codesniffer:^3.9 \
      phpcompatibility/php-compatibility:^9.3 \
      magento/magento-coding-standard:^35 \
      phpstan/phpstan:^1.12 \
      phpunit/phpunit:^10.5 \
    && phpcs --config-set installed_paths "/composer/vendor/magento/magento-coding-standard,/composer/vendor/phpcompatibility/php-compatibility"

RUN { \
      echo "xdebug.mode=develop,debug"; \
      echo "xdebug.start_with_request=yes"; \
      echo "xdebug.discover_client_host=0"; \
      echo "xdebug.client_host=host.docker.internal"; \
      echo "xdebug.client_port=9003"; \
    } > /usr/local/etc/php/conf.d/99-xdebug.ini

WORKDIR /workspace/imagekit-module

CMD ["php-fpm", "-F"]
