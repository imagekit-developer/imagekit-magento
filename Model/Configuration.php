<?php

namespace ImageKit\ImageKitMagento\Model;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Configuration implements ConfigurationInterface
{
    const MODULE_NAME = 'ImageKit_ImageKitMagento';

    const CONFIG_PATH_ENABLED = 'imagekit/general/enabled';
    const CONFIG_PATH_ENDPOINT = 'imagekit/setup/endpoint';
    const CONFIG_PATH_PUBLIC_KEY = 'imagekit/setup/public_key';
    const CONFIG_PATH_PRIVATE_KEY = 'imagekit/setup/private_key';
    const CONFIG_PATH_ORIGIN_CONFIGURED = 'imagekit/origin/configured';

    const IK_UNIQ_PREFIX = "ik_";

    /**
     * @var ScopeConfigInterface
     */
    private $configReader;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @method __construct
     * @param  ScopeConfigInterface             $configReader
     * @param  WriterInterface                  $configWriter
     * @param  ModuleListInterface              $moduleList
     * @param  StoreManagerInterface            $storeManager
     * @param  registry                         $coreRegistry
     */
    public function __construct(
        ScopeConfigInterface $configReader,
        WriterInterface $configWriter,
        ModuleListInterface $moduleList,
        StoreManagerInterface $storeManager,
        Registry $registry
    ) {
        $this->configReader = $configReader;
        $this->moduleList = $moduleList;
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $registry;
    }

    public function isEnabled()
    {
        return $this->coreRegistry->registry(self::CONFIG_PATH_ENABLED) ||
            $this->configReader->isSetFlag(self::CONFIG_PATH_ENABLED);
    }

    public function getEndpoint()
    {
        return $this->configReader->getValue(self::CONFIG_PATH_ENDPOINT);
    }

    // Public key and Private key are required for ImageKit PHP SDK 1.2.2.
    // Planned to remove this requirement in future SDK Updates

    // Provision to get Public key from User provided for future updates
    public function getPublicKey()
    {
        return "dummy_public_key";
        // return $this->configReader->getValue(self::CONFIG_PATH_PUBLIC_KEY);
    }

    // Provision to get Private key from User provided for future updates
    public function getPrivateKey()
    {
        return "dummy_private_key";
        // return $this->configReader->getValue(self::CONFIG_PATH_PRIVATE_KEY);
    }

    public function isOriginConfigured()
    {
        return $this->configReader->getValue(self::CONFIG_PATH_ORIGIN_CONFIGURED);
    }

    public function getModuleVersion()
    {
        return $this->moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }

    public function getPath($file)
    {
        return preg_match(
            "#^" . preg_quote(DirectoryList::MEDIA . DIRECTORY_SEPARATOR, '/') . "#i",
            $file
        ) ? $file : sprintf('%s/%s', DirectoryList::MEDIA, $file);
    }

    public function getMediaBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    public function generateIkuniqid()
    {
        return strtolower(uniqid(self::IK_UNIQ_PREFIX)) . '_';
    }

    public function addUniquePrefixToBasename($filename, $uniqid = null)
    {
        $uniqid = $uniqid ? $uniqid : $this->generateIkuniqid();

        if (dirname($filename) === '.') {
            return $uniqid . basename($filename);
        }
        return dirname($filename) . '/' . $uniqid . basename($filename);
    }
}
