<?php

// Magento Colllection for LibraryMap
namespace ImageKit\ImageKitMagento\Model\ResourceModel\LibraryMap;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \ImageKit\ImageKitMagento\Model\LibraryMap::class,
            \ImageKit\ImageKitMagento\Model\ResourceModel\LibraryMap::class
        );
    }
}