<?php

namespace ImageKit\ImageKitMagento\Plugin;

use Magento\Catalog\Block\Product\View\Gallery;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;

class AddImagesToGalleryBlock
{
    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    public function __construct(
        DecoderInterface $jsonDecoder,
        EncoderInterface $jsonEncoder
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
    }

    public function afterGetGalleryImages(Gallery $subject, $images)
    {
        try {
            return $images;
        } catch (\Exception $e) {
            return $images;
        }
    }

    public function afterGetMediaGalleryDataJson(Gallery $subject, $result)
    {
        try {
            $mediaGalleryData = $this->jsonDecoder->decode($result);
            $galleryImages = $subject->getProduct()->getMediaGalleryImages();
            $i = 0;

            foreach ($galleryImages as $image) {
                if (isset($mediaGalleryData[$i]) && $image->getVideoProvider()) {
                    $mediaGalleryData[$i]['videoProvider'] = $image->getVideoProvider();
                }
                $i++;
            }

            return $this->jsonEncoder->encode($mediaGalleryData);
        } catch (\Exception $e) {
            return $result;
        }
    }
}
