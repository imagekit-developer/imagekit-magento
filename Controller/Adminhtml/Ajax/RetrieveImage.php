<?php

namespace ImageKit\ImageKitMagento\Controller\Adminhtml\Ajax;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use ImageKit\ImageKitMagento\Model\Configuration;
use ImageKit\ImageKitMagento\Model\LibraryMapFactory;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Backend\App\Action\Context;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\AllowedProtocols;
use Magento\Catalog\Model\Product\Media\Config as ProductMediaConfig;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory as ImageAdapterFactory;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Magento\MediaStorage\Model\ResourceModel\File\Storage\File as FileUtility;
use Magento\Store\Model\StoreManagerInterface;

class RetrieveImage extends \Magento\Backend\App\Action
{

    private $remoteFileUrl;

    private $configuration;

    private $resultRawFactory;

    private $protocolValidator;

    private $productMediaConfig;

    private $fileSystem;

    private $extensionValidator;

    private $curl;

    private $fileUtility;

    private $imageAdapter;

    private $storeManager;

    private $libraryMap;

    public function __construct(
        Context $context,
        ConfigurationInterface $configuration,
        ResultRawFactory $resultRawFactory,
        AllowedProtocols $protocolValidator,
        ProductMediaConfig $productMediaConfig,
        FileSystem $fileSystem,
        NotProtectedExtension $extensionValidator,
        Curl $curl,
        FileUtility $fileUtility,
        ImageAdapterFactory $imageAdapterFactory,
        StoreManagerInterface $storeManager,
        LibraryMapFactory $libraryMapFactory
    ) {
        parent::__construct($context);
        $this->configuration = $configuration;
        $this->resultRawFactory = $resultRawFactory;
        $this->protocolValidator = $protocolValidator;
        $this->productMediaConfig = $productMediaConfig;
        $this->fileSystem = $fileSystem;
        $this->extensionValidator = $extensionValidator;
        $this->curl = $curl;
        $this->fileUtility = $fileUtility;
        $this->imageAdapter = $imageAdapterFactory->create();
        $this->storeManager = $storeManager;
        $this->libraryMap = $libraryMapFactory->create();
    }

    public function execute()
    {
        try {
            $fileData = $this->getRequest()->getParam('file');
            $localFileName = $fileData['name'];
            $this->remoteFileUrl = $fileData['url'];
            $this->validateRemoteFile();
            $baseTmpMediaPath = $this->getBaseTmpMediaPath();
            $localFilePath = $this->getLocalTmpFileName($localFileName);
            $localFilePath = $this->appendNewFileName($baseTmpMediaPath . $localFilePath);

            $this->validateFileExtensions($localFilePath);
            $this->retrieveRemoteImage($this->remoteFileUrl, $localFilePath);
            $localFileFullPath = $this->appendAbsoluteFileSystemPath($localFilePath);
            $this->imageAdapter->validateUploadFile($localFileFullPath);
            $result = $this->appendResultSaveRemoteImage($localFilePath, $baseTmpMediaPath);
            
            $this->saveMapping(sprintf('catalog/product%s', $result['file']), $fileData['filePath']);

        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $fileWriter = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
            if (isset($localFileFullPath) && $fileWriter->isExist($localFileFullPath)) {
                $fileWriter->delete($localFileFullPath);
            }
        }
        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }

    protected function getBaseTmpMediaPath()
    {
        $baseTmpMediaPath = $this->productMediaConfig->getBaseTmpMediaPath();
        if (!$baseTmpMediaPath) {
            throw new LocalizedException(__("Empty baseTmpMediaPath"));
        }
        return $baseTmpMediaPath;
    }

    protected function getLocalTmpFileName($remoteFileUrl)
    {
        $localFileName = Uploader::getCorrectFileName(basename($remoteFileUrl));
        $localTmpFileName = Uploader::getDispretionPath($localFileName) . DIRECTORY_SEPARATOR . $localFileName;
        return $localTmpFileName;
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

    private function validateFileExtensions($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!$this->extensionValidator->isValid($extension)) {
            throw new ValidatorException(__('Disallowed file type.'));
        }
    }

    protected function appendNewFileName($localFilePath)
    {
        $destinationFile = $this->appendAbsoluteFileSystemPath($localFilePath);
        $fileName = Uploader::getNewFileName($destinationFile);
        $fileInfo = pathinfo($localFilePath);
        return $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileName;
    }

    protected function appendAbsoluteFileSystemPath($localTmpFile)
    {
        /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $pathToSave = $mediaDirectory->getAbsolutePath();
        return $pathToSave . $localTmpFile;
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

    protected function appendResultSaveRemoteImage($localFilePath, $baseTmpMediaPath)
    {
        $tmpFileName = $localFilePath;
        if (substr($tmpFileName, 0, strlen($baseTmpMediaPath)) == $baseTmpMediaPath) {
            $tmpFileName = substr($tmpFileName, strlen($baseTmpMediaPath));
        }
        $result['name'] = basename($localFilePath);
        $result['type'] = $this->imageAdapter->getMimeType();
        $result['error'] = 0;
        $result['size'] = filesize($this->appendAbsoluteFileSystemPath($localFilePath));
        $result['url'] = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $localFilePath;
        $result['tmp_name'] = $this->appendAbsoluteFileSystemPath($localFilePath);
        $result['file'] = $tmpFileName;
        return $result;
    }

    protected function saveMapping($localFilePath, $remoteFilePath)
    {

        return $this->libraryMap
            ->setImagePath(
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
