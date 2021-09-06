<?php

namespace ImageKit\ImageKitMagento\Block\Adminhtml\Cms\Wysiwyg\Images;

use Magento\Backend\Block\Template;

class Panel extends Template
{

    public function getUploaderUrl()
    {

        try {
            //Try to add session param on Magento versions prior to 2.3.5
            $imageUploadUrl = $this->_urlBuilder
                ->addSessionParam()
                ->getUrl(
                    'imagekit/cms_wysiwyg_images/upload',
                    ['type' => $this->_getMediaType()]
                );
        } catch (\Exception $e) {
            //Catch deprecation error on Magento 2.3.5 and above
            $imageUploadUrl = $this->_urlBuilder
                ->getUrl(
                    'imagekit/cms_wysiwyg_images/upload',
                    ['type' => $this->_getMediaType()]
                );
        }

        return $imageUploadUrl;
    }

    protected function _getMediaType()
    {
        if ($this->hasData('media_type')) {
            return $this->_getData('media_type');
        }
        return $this->getRequest()->getParam('type');
    }
}
