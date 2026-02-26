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

        if (empty($transformations)) {
            $transformations = [];
        }

        $ikPath = $this->resolveImageKitPath($image);

        if ($ikPath !== null && preg_match('#^https?://#i', $ikPath)) {
            $endpoint = rtrim((string) $this->configuration->getEndpoint(), '/');

            if (empty($endpoint) || stripos($ikPath, $endpoint) !== 0) {
                return $ikPath;
            }

            $ikPath = substr($ikPath, strlen($endpoint));

            // Otherwise it's a native ImageKit asset — apply transformations.
            return $this->imageKitClient->getClient()->url(
                [
                    "path" => $ikPath,
                    "transformation" => $transformations,
                ]
            );
        }

        // Mapped relative path — use it directly as the image path.
        if ($ikPath !== null) {
            $image = $ikPath;
        } elseif (!$this->configuration->isOriginConfigured()) {
            return $originalUrl;
        } else {
            $image = $this->configuration->getPath($image);
        }

        return $this->imageKitClient->getClient()->url(
            [
                "path" => $image,
                "transformation" => $transformations,
            ]
        );
    }

    public function retrieve(string $image, string $originalUrl)
    {
        return $this->retrieveTransformed($image, [], $originalUrl);
    }

    /**
     * Look up the ImageKit path from the library map.
     *
     * @return string|null The mapped ikPath, or null if no mapping exists.
     */
    private function resolveImageKitPath(string $image): ?string
    {
        preg_match('/(ik_[A-Za-z0-9]{13}_).+$/i', $image, $ikUniqid);

        if (empty($ikUniqid[1])) {
            return null;
        }

        $mapped = $this->libraryMapFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('image_path', $ikUniqid[1])
            ->setPageSize(1)
            ->getFirstItem();

        return $mapped->getIkPath() ?: null;
    }
}
