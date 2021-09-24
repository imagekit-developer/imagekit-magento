<?php

namespace ImageKit\ImageKitMagento\Core;

use ImageKit\ImageKitMagento\Model\LibraryMapFactory;

class ImageKitImageProvider implements ImageProviderInterface
{
    private $imageKitClient;

    private $configuration;

    private $libraryMapFactory;

    public function __construct(
        ImageKitClient $imageKitClient,
        ConfigurationInterface $configuration,
        LibraryMapFactory $libraryMapFactory
    ) {
        $this->imageKitClient = $imageKitClient;
        $this->configuration = $configuration;
        $this->libraryMapFactory = $libraryMapFactory;
    }

    public function retrieveTransformed(string $image, array $transformations, string $originalUrl)
    {

        if ($transformations === null || empty($transformations)) {
            $transformations = [];
        }

        preg_match('/(ik_[A-Za-z0-9]{13}_).+$/i', $image, $ikUniqid);
        if ($ikUniqid && isset($ikUniqid[1])) {
            $mapped = $this->libraryMapFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('image_path', $ikUniqid)
                ->setPageSize(1)
                ->getFirstItem();
            if ($mapped->getIkPath()) {
                $image = $mapped->getIkPath();
            } elseif (!$this->configuration->isOriginConfigured()) {
                return $originalUrl;
            } else {
                $image = $this->configuration->getPath($image);
            }
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
