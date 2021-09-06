<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace ImageKit\ImageKitMagento\Plugin;

use ImageKit\ImageKitMagento\Model\Configuration;
use Magento\Backend\Block\Widget\Container;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\LayoutInterface;

class AddFromImageKitButton
{

    /**
     * @var Configuration
     */

    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function beforeSetLayout(Container $subject, LayoutInterface $layout): void
    {
        if ($this->configuration->isEnabled()) {
            $subject->addButton(
                'add_from_imagekit',
                [
                    'class' => 'action-secondary',
                    'label' => __('Add from ImageKit'),
                    'type' => 'button',
                    'onclick' => 'jQuery(".imagekit-media-library-modal").trigger("openModal");'
                ],
                0,
                0,
                'header'
            );
        }
    }
}
