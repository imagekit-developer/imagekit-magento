<?php

namespace ImageKit\ImageKitMagento\Plugin\Catalog\Block\Product;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use ImageKit\ImageKitMagento\Core\ImageKitImageProvider;
use Magento\Catalog\Block\Product\ImageFactory as ProductImageFactory;
use Magento\Catalog\Helper\Image as CatalogImageHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\ConfigInterface;

class ImageFactory
{
    private $objectManager;

    private $presentationConfig;

    private $configuration;

    private $imageKitImageProvider;

    private $imageParamsBuilder;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $presentationConfig,
        ConfigurationInterface $configuration,
        ImageKitImageProvider $imageKitImageProvider
    ) {
        $this->objectManager = $objectManager;
        $this->presentationConfig = $presentationConfig;
        $this->configuration = $configuration;
        $this->imageKitImageProvider = $imageKitImageProvider;
    }

    public function aroundCreate(
        ProductImageFactory $productImageFactory,
        callable $proceed,
        $product = null,
        $imageId = null,
        $attributes = null
    ) {
        $imageBlock = $proceed($product, $imageId, $attributes);

        if (!$this->configuration->isEnabled()) {
            return $imageBlock;
        }

        if ($imageBlock->getImageUrl() === 'no_selection') {
            return $imageBlock;
        }

        // phpcs:ignore Magento2.PHP.LiteralNamespaces.LiteralClassUsage
        if (is_array($product) || !class_exists('\Magento\Catalog\Model\Product\Image\ParamsBuilder')) {
            return $imageBlock;
        }

        // phpcs:ignore Magento2.PHP.LiteralNamespaces.LiteralClassUsage
        $this->imageParamsBuilder = $this->objectManager->get('\Magento\Catalog\Model\Product\Image\ParamsBuilder');

        try {
            if (strpos($imageBlock->getImageUrl(), $this->configuration->getMediaBaseUrl() . 'catalog/product') === 0) {
                $viewImageConfig = $this->presentationConfig
                    ->getViewConfig()
                    ->getMediaAttributes('Magento_Catalog', CatalogImageHelper::MEDIA_TYPE_CONFIG_NODE, $imageId);
                $imageMiscParams = $this->imageParamsBuilder->build($viewImageConfig);

                $imagePath = preg_replace(
                    '/^' . preg_quote($this->configuration->getMediaBaseUrl(), '/') . '/',
                    '/',
                    $imageBlock->getImageUrl()
                );
                $imagePath = preg_replace('/\/catalog\/product\/cache\/[a-f0-9]{32}\//', '/', $imagePath);

                $image = sprintf('catalog/product%s', $imagePath);
                $transformations = $this->createTransformation($imageMiscParams);

                $generatedImageUrl = $this->imageKitImageProvider->retrieveTransformed($image, [$transformations], $imageBlock->getImageUrl());

                $imageBlock->setOriginalImageUrl($imageBlock->setImageUrl());
                $imageBlock->setImageUrl($generatedImageUrl);
            }
        } catch (\Exception $e) {
            $imageBlock = $proceed($product, $imageId, $attributes);
        }

        return $imageBlock;
    }

    private function createTransformation(array $imageMiscParams)
    {
        $keepFrame = true;
        $transformations = [];
        $transformations['height'] = (isset($imageMiscParams['image_height'])) ?
            $imageMiscParams['image_height'] :
            null;
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
