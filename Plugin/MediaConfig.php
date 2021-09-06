<?php

namespace ImageKit\ImageKitMagento\Plugin;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use ImageKit\ImageKitMagento\Core\ImageKitClient;
use Magento\Catalog\Model\Product\Media\Config as CatalogMediaConfig;

class MediaConfig
{
    private $imageKitClient;

    private $configuration;

    public function __construct(ImageKitClient $imageKitClient, ConfigurationInterface $configuration)
    {
        $this->imageKitClient = $imageKitClient;
        $this->configuration = $configuration;
    }

    public function aroundGetMediaUrl(CatalogMediaConfig $mediaConfig, \Closure $originalMethod, $file)
    {
        if (!$this->configuration->isEnabled()) {
            return $originalMethod($file);
        }

        $image = $this->configuration->getPath($mediaConfig->getBaseMediaPath() . $file);

        return $this->imageKitClient->getClient()->url(
            [
                "path" => $image,
            ]
        );
    }
}
