<?php

namespace ImageKit\ImageKitMagento\Controller\Adminhtml\Ajax;

use ImageKit\ImageKitMagento\Core\ConfigurationInterface;
use ImageKit\ImageKitMagento\Model\LibraryMapFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Media\Config as ProductMediaConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Image\AdapterFactory as ImageAdapterFactory;
use Magento\Framework\File\Uploader;
use Magento\Framework\UrlInterface;
use Magento\Framework\Validator\AllowedProtocols;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Magento\MediaStorage\Model\ResourceModel\File\Storage\File as FileUtility;
use Magento\Store\Model\StoreManagerInterface;

class ImportVideo extends Action
{
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var ResultRawFactory
     */
    protected $resultRawFactory;

    /**
     * @var ProductMediaConfig
     */
    protected $mediaConfig;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var ImageAdapterFactory
     */
    protected $imageAdapterFactory;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var FileUtility
     */
    protected $fileUtility;

    /**
     * @var AllowedProtocols
     */
    private $protocolValidator;

    /**
     * @var NotProtectedExtension
     */
    private $extensionValidator;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var LibraryMapFactory
     */
    private $libraryMapFactory;

    public function __construct(
        Context $context,
        ResultRawFactory $resultRawFactory,
        ProductMediaConfig $mediaConfig,
        Filesystem $fileSystem,
        ImageAdapterFactory $imageAdapterFactory,
        Curl $curl,
        FileUtility $fileUtility,
        AllowedProtocols $protocolValidator,
        NotProtectedExtension $extensionValidator,
        StoreManagerInterface $storeManager,
        ConfigurationInterface $configuration,
        LibraryMapFactory $libraryMapFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->mediaConfig = $mediaConfig;
        $this->fileSystem = $fileSystem;
        $this->imageAdapterFactory = $imageAdapterFactory;
        $this->curl = $curl;
        $this->fileUtility = $fileUtility;
        $this->protocolValidator = $protocolValidator;
        $this->extensionValidator = $extensionValidator;
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
        $this->libraryMapFactory = $libraryMapFactory;
    }

    public function execute()
    {
        try {
            $fileData = $this->getRequest()->getParam('file');
            if (!isset($fileData['url'])) {
                throw new LocalizedException(__('Missing video URL.'));
            }

            $videoUrl = $fileData['url'];
            $parsedUrl = parse_url($videoUrl);
            if ($parsedUrl === false || !isset($parsedUrl['scheme'], $parsedUrl['host'])) {
                throw new LocalizedException(__('Invalid video URL.'));
            }
            $videoPath = $parsedUrl['path'] ?? '';
            $endpoint = (string) $this->configuration->getEndpoint();
            $parsedEndpoint = parse_url($endpoint) ?: [];
            $endpointPath = $parsedEndpoint['path'] ?? '';

            $videoPathParts = array_values(array_filter(explode('/', ltrim($videoPath, '/'))));
            $endpointPathParts = array_values(array_filter(explode('/', ltrim($endpointPath, '/'))));

            // If the URL path starts with the same leading segment as the configured endpoint path
            // (e.g. endpoint: /<cloud_name>/..., asset URL: /<cloud_name>/file.mp4), strip it so
            // the stored mapping path remains relative to the endpoint.
            if (!empty($videoPathParts) && !empty($endpointPathParts) && $videoPathParts[0] === $endpointPathParts[0]) {
                array_shift($videoPathParts);
            }

            $thumbnailUrl = $fileData['thumbnail']
                ?? ($parsedUrl['scheme'] . '://' . $parsedUrl['host'] . rtrim($videoPath, '/') . '/ik-thumbnail.jpg'
                    . (!empty($parsedUrl['query']) ? '?' . $parsedUrl['query'] : ''));
            $videoName = $fileData['name'] ?? basename($videoUrl);

            if (!$this->protocolValidator->isValid($thumbnailUrl)) {
                throw new LocalizedException(__("Protocol isn't allowed"));
            }

            $baseTmpMediaPath = $this->mediaConfig->getBaseTmpMediaPath();
            $thumbnailName = pathinfo($videoName, PATHINFO_FILENAME) . '.jpg';
            $ikUniqId = $this->configuration->generateIkuniqid();
            $thumbnailName = $this->configuration->addUniquePrefixToBasename($thumbnailName, $ikUniqId);
            $localFileName = Uploader::getCorrectFileName($thumbnailName);
            $localTmpFile = Uploader::getDispretionPath($localFileName)
                . DIRECTORY_SEPARATOR . $localFileName;
            $localFilePath = $baseTmpMediaPath . $localTmpFile;

            $destinationFile = $this->appendAbsoluteFileSystemPath($localFilePath);
            $fileName = Uploader::getNewFileName($destinationFile);
            $fileInfo = pathinfo($localFilePath);
            $localFilePath = $fileInfo['dirname'] . DIRECTORY_SEPARATOR . $fileName;

            $this->retrieveRemoteImage($thumbnailUrl, $localFilePath);

            $localFileFullPath = $this->appendAbsoluteFileSystemPath($localFilePath);
            $imageAdapter = $this->imageAdapterFactory->create();
            $imageAdapter->validateUploadFile($localFileFullPath);

            $extension = pathinfo($localFileFullPath, PATHINFO_EXTENSION);
            if (!$this->extensionValidator->isValid($extension)) {
                throw new LocalizedException(__('Disallowed file type.'));
            }

            $tmpFileName = $localFilePath;
            if (substr($tmpFileName, 0, strlen($baseTmpMediaPath)) == $baseTmpMediaPath) {
                $tmpFileName = substr($tmpFileName, strlen($baseTmpMediaPath));
            }

            $result = [];
            $result['name'] = basename($localFilePath);
            $result['type'] = $imageAdapter->getMimeType();
            $result['error'] = 0;
            $result['size'] = filesize($localFileFullPath);
            $result['url'] = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $localFilePath;
            $result['tmp_name'] = $localFileFullPath;
            $result['file'] = $tmpFileName;

            $result['media_type'] = 'external-video';
            $result['video_provider'] = 'imagekit';
            $result['video_url'] = $videoUrl;
            $result['video_title'] = pathinfo($videoName, PATHINFO_FILENAME);
            $result['video_description'] = '';

            $parsedThumbUrl = parse_url($thumbnailUrl);
            if ($parsedThumbUrl === false || !isset($parsedThumbUrl['scheme'], $parsedThumbUrl['host'], $parsedThumbUrl['path'])) {
                throw new LocalizedException(__('Invalid thumbnail URL.'));
            }
            $cleanThumbnailUrl = $parsedThumbUrl['scheme'] . '://' . $parsedThumbUrl['host']
                . $parsedThumbUrl['path'];
            $this->saveMapping($ikUniqId, $cleanThumbnailUrl, 'video-thumbnail');
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $fileWriter = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
            if (isset($localFileFullPath) && $fileWriter->isExist($localFileFullPath)) {
                $fileWriter->delete($localFileFullPath);
            }
        }

        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-Type', 'application/json');
        $response->setContents(json_encode($result));
        return $response;
    }

    protected function retrieveRemoteImage($fileUrl, $localFilePath, $maxRetries = 5, $retryDelaySec = 3)
    {
        $attempts = 0;

        while ($attempts < $maxRetries) {
            $this->curl->setConfig(['header' => false]);
            $this->curl->write('GET', $fileUrl);
            $body = $this->curl->read();
            $statusCode = (int) $this->curl->getInfo(CURLINFO_HTTP_CODE);
            $this->curl->close();

            if ($statusCode === 200 && !empty($body)) {
                $this->fileUtility->saveFile($localFilePath, $body);
                return;
            }

            if ($statusCode === 202) {
                $attempts++;
                if ($attempts < $maxRetries) {
                    sleep($retryDelaySec);
                }
                continue;
            }

            if (empty($body)) {
                throw new LocalizedException(
                    __('The preview image information is unavailable. Check your connection and try again.')
                );
            }

            throw new LocalizedException(
                __('Failed to retrieve thumbnail. HTTP status: %1', $statusCode)
            );
        }

        throw new LocalizedException(
            __('Thumbnail generation timed out after %1 attempts. Please try again later.', $maxRetries)
        );
    }

    protected function appendAbsoluteFileSystemPath($localTmpFile)
    {
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        return $mediaDirectory->getAbsolutePath() . $localTmpFile;
    }

    protected function saveMapping($ikUniqId, $remoteFilePath, $assetType = 'image')
    {
        return $this->libraryMapFactory->create()
            ->setImagePath($ikUniqId)
            ->setIkPath($remoteFilePath)
            ->setAssetType($assetType)
            ->save();
    }
}
