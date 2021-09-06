<?php

namespace ImageKit\ImageKitMagento\Plugin;

use Magento\Catalog\Block\Product\View\Gallery;

class AddImagesToGalleryBlock
{
    public function afterGetGalleryImages(Gallery $subject, $images)
    {
        try {
            // foreach ($images as $key => $value) {
            //   print_r($value);
            // }
            return $images;
        } catch (\Exception $e) {
            return $images;
        }
    }
}
