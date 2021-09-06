<?php

namespace ImageKit\ImageKitMagento\Plugin\Catalog\Model\Product\Image;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use ImageKit\ImageKitMagento\Core\ImageKitClient;
use Magento\Catalog\Model\Product\Image\UrlBuilder as ImageUrlBuilder;
use Magento\Catalog\Helper\Image as CatalogImageHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\ConfigInterface;

class UrlBuilder
{
    private $objectManager;

    private $presentationConfig;

    private $configuration;

    private $imageKitClient;

    private $imageParamsBuilder;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $presentationConfig,
        ConfigurationInterface $configuration,
        ImageKitClient $imageKitClient
    ) {
        $this->objectManager = $objectManager;
        $this->presentationConfig = $presentationConfig;
        $this->configuration = $configuration;
        $this->imageKitClient = $imageKitClient;
    }

    public function aroundGetUrl(
        ImageUrlBuilder $imageUrlBuilder,
        callable $proceed,
        string $baseFilePath,
        string $imageDisplayArea
    ) {
        $url = $proceed($baseFilePath, $imageDisplayArea);

        if (!$this->configuration->isEnabled()) {
            return $url;
        }

        if ($url === 'no_selection') {
            return $url;
        }
        // phpcs:ignore Magento2.PHP.LiteralNamespaces.LiteralClassUsage
        if (class_exists('\Magento\Catalog\Model\Product\Image\ParamsBuilder')) {
            // phpcs:ignore Magento2.PHP.LiteralNamespaces.LiteralClassUsage
            $this->imageParamsBuilder = $this->objectManager->get('\Magento\Catalog\Model\Product\Image\ParamsBuilder');
        } else {
            //Skip on Magento versions prior to 2.3
            return $url;
        }

        try {
            if (strpos($url, $this->configuration->getMediaBaseUrl() . 'catalog/product') === 0) {
                $imageArguments = $this->presentationConfig
                    ->getViewConfig()
                    ->getMediaAttributes(
                        'Magento_Catalog',
                        CatalogImageHelper::MEDIA_TYPE_CONFIG_NODE,
                        $imageDisplayArea
                    );
                $imageMiscParams = $this->imageParamsBuilder->build($imageArguments);

                $imagePath = preg_replace(
                    '/^' . preg_quote($this->configuration->getMediaBaseUrl(), '/') . '/',
                    '/',
                    $url
                );
                $imagePath = preg_replace('/\/catalog\/product\/cache\/[a-f0-9]{32}\//', '/', $imagePath);

                $image = $this->configuration->getPath(sprintf('catalog/product%s', $imagePath));
                $transformations = $this->createTransformation($imageMiscParams);

                $url = $this->imageKitClient->getClient()->url(
                    [
                        "path" => $image,
                        "transformation" => [$transformations]
                    ]
                );
            }
        } catch (\Exception $e) {
            $url = $proceed($baseFilePath, $imageDisplayArea);
        }

        return $url;
    }

    private function createTransformation(array $imageMiscParams)
    {
        $keepFrame = true;
        $transformations = [];
        $transformations['height'] = (isset($imageMiscParams['image_height'])) ?
            $imageMiscParams['image_height'] : null;
        $transformations['width'] = (isset($imageMiscParams['image_width'])) ? $imageMiscParams['image_width'] : null;

        $transformations['rotation'] = (isset($imageMiscParams['rotate'])) ? $imageMiscParams['rotate'] : null;
        $transformations['quality'] = (isset($imageMiscParams['quality'])) ? $imageMiscParams['quality'] : null;

        if (isset($imageMiscParams['keep_frame'])) {
            $keepFrame = $imageMiscParams['keep_frame'];
        }

        if ($keepFrame) {
            $transformations["cropMode"] = "pad_resize";
        } else {
            $transformations["crop"] = "at_max";
        }

        return array_filter($transformations);
    }
}
