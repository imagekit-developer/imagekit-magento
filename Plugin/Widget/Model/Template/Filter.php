<?php

namespace ImageKit\ImageKitMagento\Plugin\Widget\Model\Template;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use ImageKit\ImageKitMagento\Core\ImageKitClient;
use ImageKit\ImageKitMagento\Model\Template\Filter as ImageKitWidgetFilter;

class Filter
{
    private $configuration;

    private $imagekitWidgetFilter;

    private $imageKitClient;

    public function __construct(
        ConfigurationInterface $configuration,
        ImageKitWidgetFilter $imagekitWidgetFilter,
        ImageKitClient $imageKitClient
    ) {
        $this->configuration = $configuration;
        $this->imagekitWidgetFilter = $imagekitWidgetFilter;
        $this->imageKitClient = $imageKitClient;
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

        $image = $this->configuration->getPath($url);

        return $this->imageKitClient->getClient()->url(
            [
                "path" => $image,
            ]
        );
    }
}
