# ImageKit Magento 2 Extension

The ImageKit Magento extension links your Magento website to your ImageKit account, allowing you to serve all your images directly from ImageKit and leveraging all the powerful features that ImageKit.io has to offer.

Before you install the extension, make sure you have a ImageKit account. You can start by [signing up](https://imagekit.io/registration) for a free plan. When your requirements grow, you can upgrade to a [plan](https://imagekit.io/plans/) that best fits your needs.

## Installation

You can install it via composer by running the following commands under your Magento 2 root dir:

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

![ImageKit Logo](https://ik.imagekit.io/ikmedia/tr:w-200/logo/light3x_T4-2dKENMe.png)
