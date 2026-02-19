<?php

namespace ImageKit\ImageKitMagento\Controller\Adminhtml\Cms\Wysiwyg\Images;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use ImageKit\ImageKitMagento\Model\LibraryMapFactory;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Filesystem\DirectoryResolver;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Registry;
use Magento\Framework\Validator\AllowedProtocols;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Magento\MediaStorage\Model\ResourceModel\File\Storage\File;
use Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\Upload as ImagesUpload;
use Magento\Framework\File\Uploader;

class Upload extends ImagesUpload
{

    /**
     * @var string|null
     */
    private $remoteFileUrl;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Config
     */
    protected $mediaConfig;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var AbstractAdapter
     */
    protected $imageAdapter;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var File
     */
    protected $fileUtility;

    /**
     * AllowedProtocols validator
     *
     * @var AllowedProtocols
     */
    private $protocolValidator;

    /**
     * @var NotProtectedExtension
     */
    private $extensionValidator;

    /**
     * @var FileIo
     */
    private $file;

    private $libraryMap;

    private $configuration;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        JsonFactory $resultJsonFactory,
        DirectoryList $directoryList,
        Config $mediaConfig,
        Filesystem $fileSystem,
        AdapterFactory $imageAdapterFactory,
        Curl $curl,
        File $fileUtility,
        AllowedProtocols $protocolValidator,
        NotProtectedExtension $extensionValidator,
        FileIo $file,
        LibraryMapFactory $libraryMapFactory,
        ConfigurationInterface $configuration,
        ?DirectoryResolver $directoryResolver = null,
    ) {
        parent::__construct($context, $coreRegistry, $resultJsonFactory, $directoryResolver);
        $this->directoryList = $directoryList;
        $this->mediaConfig = $mediaConfig;
        $this->fileSystem = $fileSystem;
        $this->imageAdapter = $imageAdapterFactory->create();
        $this->curl = $curl;
        $this->fileUtility = $fileUtility;
        $this->extensionValidator = $extensionValidator;
        $this->protocolValidator = $protocolValidator;
        $this->file = $file;
        $this->libraryMap = $libraryMapFactory->create();
        $this->configuration = $configuration;
    }

    public function execute()
    {
        try {
            $this->_initAction();
            $path = $this->getStorage()->getSession()->getCurrentPath();
            if (!$this->validatePath($path, DirectoryList::MEDIA)) {
                throw new LocalizedException(
                    __('Directory %1 is not under storage root path.', $path)
                );
            }
            $fileData = $this->getRequest()->getParam('file');

            $localFileName = $fileData['name'];
            $this->remoteFileUrl = $fileData['url'];
            $this->validateRemoteFile();

            $localFileName = $this->addFallbackExtension($localFileName, $fileData);
            $ikUniqId = $this->configuration->generateIkuniqid();
            $localFileName = $this->configuration->addUniquePrefixToBasename($localFileName, $ikUniqId);
            $localFileName = Uploader::getCorrectFileName($localFileName);
            $localFilePath = $this->appendNewFileName($path . DIRECTORY_SEPARATOR . $localFileName);
            $this->validateRemoteFileExtensions($localFilePath);

            $this->retrieveRemoteImage($this->remoteFileUrl, $localFilePath);
            $this->getStorage()->resizeFile($localFilePath, true);

            $this->imageAdapter->validateUploadFile($localFilePath);

            $result = $this->appendResultSaveRemoteImage($localFilePath);

            $this->saveMapping($ikUniqId, $fileData['filePath']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode(), 'trace' => $e->getTraceAsString()];
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($result);
    }

    private function addFallbackExtension($localFileName, $fileData)
    {
        $fileType = $fileData['fileType'];

        if ($fileType === "image") {
            $pathInfo = $this->file->getPathInfo($localFileName);
            if (!isset($pathInfo['extension'])) {
                $localFileName = $localFileName . ".jpg";
            }
        }

        return $localFileName;
    }

    private function validateRemoteFile()
    {
        if (!$this->protocolValidator->isValid($this->remoteFileUrl)) {
            throw new LocalizedException(
                __("Protocol isn't allowed")
            );
        }

        return $this;
    }

    private function validatePath($path, $directoryConfig = DirectoryList::MEDIA)
    {
        $directory = $this->fileSystem->getDirectoryWrite($directoryConfig);
        $realPath = $directory->getDriver()->getRealPathSafety($path);
        $root = $this->directoryList->getPath($directoryConfig);

        return strpos($realPath, $root) === 0;
    }

    private function validateRemoteFileExtensions($filePath)
    {
        $extension = $this->file->getPathInfo($filePath)['extension'];
        $allowedExtensions = (array) $this->getStorage()->getAllowedExtensions($this->getRequest()->getParam('type'));
        if (!$this->extensionValidator->isValid($extension) || !in_array($extension, $allowedExtensions)) {
            throw new ValidatorException(__('Disallowed file type.'));
        }
    }

    protected function retrieveRemoteImage($fileUrl, $localFilePath)
    {
        $this->curl->setConfig(['header' => false]);
        $this->curl->write('GET', $fileUrl);
        $image = $this->curl->read();
        if (empty($image)) {
            throw new LocalizedException(
                __('The preview image information is unavailable. Check your connection and try again.')
            );
        }
        $this->fileUtility->saveFile($localFilePath, $image);
    }

    protected function appendResultSaveRemoteImage($filePath)
    {
        $fileInfo = $this->file->getPathInfo($filePath);
        $result['name'] = $fileInfo['basename'];
        $result['type'] = $this->imageAdapter->getMimeType();
        $result['error'] = 0;
        $result['size'] = filesize($filePath); // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $result['url'] = $this->getRequest()->getParam('remote_image');
        $result['file'] = $filePath;
        return $result;
    }

    protected function appendNewFileName($localFilePath)
    {
        $filename = Uploader::getNewFileName($localFilePath);
        $fileInfo = $this->file->getPathInfo($localFilePath);
        return $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $filename;
    }

    protected function saveMapping($localFilePath, $remoteFilePath)
    {
        return $this->libraryMap->setImagePath(
            str_replace(
                $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(),
                '',
                $localFilePath
            )
        )
            ->setIkPath($remoteFilePath)
            ->save();
    }
}
