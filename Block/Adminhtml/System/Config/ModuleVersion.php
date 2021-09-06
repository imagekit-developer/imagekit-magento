<?php

namespace ImageKit\ImageKitMagento\Block\Adminhtml\System\Config;

use ImageKit\ImageKitMagento\Model\Configuration;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ModuleVersion extends Field
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @method __construct
     * @param  Context                $context
     * @param  array                  $data
     */
    public function __construct(
        Context $context,
        Configuration $configuration,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configuration = $configuration;
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return "<div>{$this->configuration->getModuleVersion()}</div>";
    }
}
