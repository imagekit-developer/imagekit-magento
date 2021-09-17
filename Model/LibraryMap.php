<?php

namespace ImageKit\ImageKitMagento\Model;

class LibraryMap extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct(){
        $this->_init(\ImageKit\ImageKitMagento\Model\ResourceModel\LibraryMap::class);
    }
}