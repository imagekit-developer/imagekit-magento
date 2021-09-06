<?php

namespace ImageKit\ImageKitMagento\Model\Template;


class Filter extends \Magento\Catalog\Model\Template\Filter
{
    public function getParams($value){
        return $this->getParameters($value);
    }
}