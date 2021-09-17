<?php

namespace ImageKit\ImageKitMagento\Core;

use ImageKit\ImageKitMagento\Model\LibraryMapFactory;

class ImageKitImageProvider implements ImageProvider
{

    private $imagekitClient;
    private $configuration;
    private $libraryMapFactory;

    public function __construct(ImageKitClient $imageKitClient, ConfigurationInterface $configuration, LibraryMapFactory $libraryMapFactory)
    {
        $this->imageKitClient = $imageKitClient;
        $this->configuration = $configuration;
        $this->libraryMapFactory = $libraryMapFactory;
    }

    public function retrieveTransformed(string $image, array $transformations = [], string $originalUrl)
    {
        $mapped = $this->libraryMapFactory->create()->getCollection()->addFieldToFilter('image_path', $image)->setPageSize(1)->getFirstItem();
        if ($mapped->getIkPath()) {
            $image = $mapped->getIkPath();
        } elseif (!$this->configuration->isOriginConfigured()) {
            return $originalUrl;
        } else {
            $image = $this->configuration->getPath($image);
        }

        return $this->imageKitClient->getClient()->url(
            [
                "path" => $image,
                "transformation" => $transformations
            ]
        );
    }

    public function retrieve(string $image, string $originalUrl)
    {
        return $this->retrieveTransformed($image, [], $originalUrl);
    }
}
