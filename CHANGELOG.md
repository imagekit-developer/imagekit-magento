# Changelog

## [1.5.0](https://github.com/imagekit-developer/imagekit-magento/compare/1.4.2...v1.5.0) (2026-06-25)


### Features

* add release automation configuration and version management for module updates ([455de1d](https://github.com/imagekit-developer/imagekit-magento/commit/455de1d2ea4a05438848ad949575719dd46ce266))
* add workflow to update module versions in release PR ([d6311b6](https://github.com/imagekit-developer/imagekit-magento/commit/d6311b65148fcfc84b20b57572d1f2a38d137d6f))
* enhance ImageKit video handling by adding title and thumbnail support in video information and dialog ([#20](https://github.com/imagekit-developer/imagekit-magento/issues/20)) ([26bd6ff](https://github.com/imagekit-developer/imagekit-magento/commit/26bd6ff42e4d7004caee7256d5c2a425628356f6))


### Bug Fixes

* ensure workflow runs only for the correct repository in release-please.yml ([fd0466e](https://github.com/imagekit-developer/imagekit-magento/commit/fd0466e8a2772cb94b0f8dda37963feb8ef76e01))
* guard against null image path in Plugin/Helper/Image.php ([#21](https://github.com/imagekit-developer/imagekit-magento/issues/21)) ([a1b68ca](https://github.com/imagekit-developer/imagekit-magento/commit/a1b68ca3220a5cebfb38ff761210acb69679394d))
* update workflow trigger to use pull_request for version updates in release PR ([a68d1de](https://github.com/imagekit-developer/imagekit-magento/commit/a68d1def91f2148365860f34d869e4f98eadbecf))


### Miscellaneous Chores

* **ci:** update CI and release workflows to improve package creation and upload process ([3b79d32](https://github.com/imagekit-developer/imagekit-magento/commit/3b79d32db25182f03901f80aea5284c476fcdeef))
* update PHP version to 8.3 and improve package installation in Dockerfile ([931e19f](https://github.com/imagekit-developer/imagekit-magento/commit/931e19fe3531303f90ed17e2eb519b5d574d1911))
* update PHPStan action version and PHP version to 8.3 in CI workflow ([ed513f2](https://github.com/imagekit-developer/imagekit-magento/commit/ed513f20620592c46e02995eb1097f09bfe8ad84))
* update PHPStan action version to 8.3 in CI workflow ([71131b5](https://github.com/imagekit-developer/imagekit-magento/commit/71131b5167dd32ebe0dd0d4abe262bd6d9a6e9b6))
