<?php

namespace ImageKit\ImageKitMagento\Block\Adminhtml\System\Config;

use ImageKit\ImageKitMagento\Model\Configuration;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;

class OriginConfiguration extends Field
{
    protected $storeManager;

    public function __construct(Context $context, StoreManagerInterface $storeManager, array $data = [])
    {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    public function _getElementHtml(AbstractElement $element)
    {

        $url = $this->storeManager->getStore()->getBaseUrl();

        return <<<COMMENT
 <p>
            <strong>Step 1: </strong>
            Go to the
            <a href="https://imagekit.io/dashboard/external-storage" target="_blank" rel="noopener noreferrer">
                external storage section
            </a>
            in your ImageKit.io dashboard, and under the Origins section, click
            on the
            <strong>Add origin</strong> button.
        </p>
        <p>
            <strong>Step 2: </strong>
            Choose 
            <strong>
                <a href="https://docs.imagekit.io/integration/configure-origin/web-server-origin">Web server</a>
            </strong> 
            from the origin type dropdown.
        </p>
        <p>
            <strong>Step 3: </strong>
            Give your origin a name, it will appear in the
            list of origins you have added. For example - <strong>My Magento Website</strong>.
        </p>
        <p>
            <strong>Step 4: </strong>
            Fill out the base URL as <strong><a href="$url">$url</a></strong>.
        </p>
        <p>
            <strong>Step 5: </strong>
            To configure advanced options, refer
            <a href="https://docs.imagekit.io/integration/configure-origin/web-server-origin#advanced-options-for-web-server-origin">here</a>.
        </p>
        <p>
            <strong>Step 6: </strong>
            Click on Submit button. </p>
COMMENT;
    }
}
