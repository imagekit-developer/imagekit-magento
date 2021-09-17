<?php

namespace ImageKit\ImageKitMagento\Model\ResourceModel;

class LibraryMap extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('imagekit_library_map', 'id');
    }
}