<?php

namespace ImageKit\ImageKitMagento\Block\Adminhtml\Product\Helper\Form\Gallery;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content as GalleryContent;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Json\EncoderInterface;

class Content extends GalleryContent
{
    protected $_template = 'ImageKit_ImageKitMagento::catalog/product/helper/gallery.phtml';

    protected $configuration;

    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Config $mediaConfig,
        ConfigurationInterface $configuration,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $mediaConfig, $data);
        $this->configuration = $configuration;
    }

    public function getUploaderUrl()
    {

        if (!$this->configuration->isEnabled()) {
            return null;
        }

        try {
            //Try to add session param on Magento versions prior to 2.3.5
            $imageUploadUrl = $this->_urlBuilder->addSessionParam()->getUrl('imagekit/ajax/retrieveImage');
        } catch (\Exception $e) {
            //Catch deprecation error on Magento 2.3.5 and above
            $imageUploadUrl = $this->_urlBuilder->getUrl('imagekit/ajax/retrieveImage');
        }

        return $imageUploadUrl;
    }

    public function getVideoImporterUrl()
    {
        if (!$this->configuration->isEnabled()) {
            return null;
        }

        try {
            $videoImportUrl = $this->_urlBuilder->addSessionParam()->getUrl('imagekit/ajax/importVideo');
        } catch (\Exception $e) {
            $videoImportUrl = $this->_urlBuilder->getUrl('imagekit/ajax/importVideo');
        }

        return $videoImportUrl;
    }
}
