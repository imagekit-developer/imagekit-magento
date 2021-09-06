<?php

namespace ImageKit\ImageKitMagento\Core;

use ImageKit\ImageKit;

class ImageKitClient
{
    private $configuration;

    private $client;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
        $this->client = null;
    }

    /**
     * @return ImageKit
     */
    public function getClient()
    {
        if ($this->client === null) {
            $this->client = new ImageKit(
                $this->configuration->getPublicKey(),
                $this->configuration->getPrivateKey(),
                $this->configuration->getEndpoint()
            );
        }
        return $this->client;
    }
}
