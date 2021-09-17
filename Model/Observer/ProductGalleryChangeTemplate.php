<?php

namespace ImageKit\ImageKitMagento\Model\Observer;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductGalleryChangeTemplate implements ObserverInterface
{

    protected $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function execute(Observer $observer)
    {
        if (!$this->configuration->isEnabled()) {
            return $this;
        }
        $observer->getBlock()->setTemplate('ImageKit_ImageKitMagento::catalog/product/helper/gallery.phtml');
    }
}
