<?php

namespace ImageKit\ImageKitMagento\Plugin\Helper;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use ImageKit\ImageKitMagento\Core\ImageKitClient;
use Magento\Catalog\Helper\Image as CatalogImageHelper;

class Image
{

    private $configuration;

    private $imageKitClient;

    private $product;

    private $transformations;

    private $imageFile;

    private $keepFrame;

    public function __construct(ConfigurationInterface $configuration, ImageKitClient $imageKitClient)
    {
        $this->configuration = $configuration;
        $this->imageKitClient = $imageKitClient;
    }

    public function beforeInit(CatalogImageHelper $helper, $product, $imageId, $attributes = [])
    {
        $this->product = $product;
        $this->transformations = [];
        $this->imageFile = null;
        $this->keepFrame = true;
        return [$product, $imageId, $attributes];
    }

    public function beforeSetImageFile(CatalogImageHelper $helper, $file)
    {
        $this->imageFile = $file;
        return [$file];
    }

    public function beforeResize(CatalogImageHelper $helper, $width, $height = null)
    {
        $this->transformations["height"] = $height !== null ? (string) round($height) : null;
        $this->transformations["width"] = $width !== null ? (string) round($width) : null;
        return [$width, $height];
    }

    public function beforeKeepFrame(CatalogImageHelper $helper, $flag)
    {
        $this->keepFrame = (bool)$flag;
    }

    public function aroundGetUrl(CatalogImageHelper $helper, \Closure $originalMethod)
    {

        if (!$this->configuration->isEnabled()) {
            return $originalMethod();
        }

        $imagePath = $this->imageFile ?: $this->product->getData($helper->getType());
        $image = $this->configuration->getPath(sprintf('catalog/product%s', $imagePath));
        $this->createTransformation($helper);

        return $this->imageKitClient->getClient()->url(
            [
                "path" => $image,
                "transformation" => [$this->transformations]
            ]
        );
    }

    private function createTransformation(CatalogImageHelper $helper)
    {
        if (array_key_exists("height", $this->transformations) && $this->transformations["height"] === null) {
            $this->transformations["height"] = $helper->getHeight();
        } else {
            $this->transformations["height"] = $helper->getHeight();
        }
        if (array_key_exists("width", $this->transformations) && $this->transformations["width"] === null) {
            $this->transformations["width"] = $helper->getWidth();
        } else {
            $this->transformations["width"] = $helper->getWidth();
        }

        if ($this->keepFrame) {
            $this->transformations["cropMode"] = "pad_resize";
        } else {
            $this->transformations["crop"] = "at_max";
        }
    }
}
