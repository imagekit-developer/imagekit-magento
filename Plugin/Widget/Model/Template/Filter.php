<?php

namespace ImageKit\ImageKitMagento\Plugin\Widget\Model\Template;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use ImageKit\ImageKitMagento\Core\ImageKitImageProvider;
use ImageKit\ImageKitMagento\Model\Template\Filter as ImageKitWidgetFilter;

class Filter
{
    private $configuration;

    private $imagekitWidgetFilter;

    private $imageKitImageProvider;

    public function __construct(
        ConfigurationInterface $configuration,
        ImageKitWidgetFilter $imagekitWidgetFilter,
        ImageKitImageProvider $imageKitImageProvider
    ) {
        $this->configuration = $configuration;
        $this->imagekitWidgetFilter = $imagekitWidgetFilter;
        $this->imageKitImageProvider = $imageKitImageProvider;
    }

    public function aroundMediaDirective(
        \Magento\Widget\Model\Template\Filter $widgetFilter,
        callable $proceed,
        $construction
    ) {
        if (!$this->configuration->isEnabled()) {
            return $proceed($construction);
        }

        $params = $this->imagekitWidgetFilter->getParams($construction[2]);

        if (!isset($params['url'])) {
            return $proceed($construction);
        }

        $url = (preg_match('/^&quot;.+&quot;$/', $params['url'])) ?
            preg_replace('/(^&quot;)|(&quot;$)/', '', $params['url']) : $params['url'];

        $original_url = $proceed($construction);

        return $this->imageKitImageProvider->retrieve($url, $original_url);
    }
}
