[<img width="250" alt="ImageKit.io" src="https://raw.githubusercontent.com/imagekit-developer/imagekit-javascript/master/assets/imagekit-light-logo.svg"/>](https://imagekit.io)

# ImageKit Magento 2 Extension

[![Magento Marketplace](https://img.shields.io/badge/Magento-Marketplace-orange)](https://marketplace.magento.com/imagekit-imagekit-magento.html)
[![Packagist](https://img.shields.io/packagist/v/imagekit/imagekit-magento.svg)](https://packagist.org/packages/imagekit/imagekit-magento) 
[![Packagist](https://img.shields.io/packagist/dt/imagekit/imagekit-magento.svg)](https://packagist.org/packages/imagekit/imagekit-magento) 
[![M2 PHPStan](https://github.com/imagekit-developer/imagekit-magento/actions/workflows/phpstan.yml/badge.svg)](https://github.com/imagekit-developer/imagekit-magento/actions/workflows/phpstan.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Twitter Follow](https://img.shields.io/twitter/follow/imagekitio?label=Follow&style=social)](https://twitter.com/ImagekitIo)


The ImageKit Magento extension links your Magento website to your ImageKit account, allowing you to serve all your images directly from ImageKit and leveraging all the powerful features that ImageKit.io has to offer.

Before you install the extension, make sure you have a ImageKit account. You can start by [signing up](https://imagekit.io/registration) for a free plan. When your requirements grow, you can upgrade to a [plan](https://imagekit.io/plans/) that best fits your needs.

## Installation

You can download and install the extension from [Magento Marketplace](https://marketplace.magento.com/imagekit-imagekit-magento.html) or install it via composer by running the following commands under your Magento 2 root dir:

Follow the official step-by step guide for detailed information - https://docs.imagekit.io/platform-guides/magento.

```
composer require imagekit/imagekit-magento
php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento maintenance:disable
php bin/magento cache:flush
```


Copyright © 2021 [ImageKit](https://imagekit.io/). All rights reserved.
