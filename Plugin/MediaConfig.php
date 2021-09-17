<?php

namespace ImageKit\ImageKitMagento\Plugin;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use ImageKit\ImageKitMagento\Core\ImageKitImageProvider;
use Magento\Catalog\Model\Product\Media\Config as CatalogMediaConfig;

class MediaConfig
{
    private $imageKitImageProvider;

    private $configuration;

    public function __construct(ImageKitImageProvider $imageKitImageProvider, ConfigurationInterface $configuration)
    {
        $this->imageKitImageProvider = $imageKitImageProvider;
        $this->configuration = $configuration;
    }

    public function aroundGetMediaUrl(CatalogMediaConfig $mediaConfig, \Closure $originalMethod, $file)
    {
        if (!$this->configuration->isEnabled()) {
            return $originalMethod($file);
        }

        $image = $mediaConfig->getBaseMediaPath() . $file;

        return $this->imageKitImageProvider->retrieve($image, $originalMethod($file));
    }
}
