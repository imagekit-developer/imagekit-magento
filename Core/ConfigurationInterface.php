<?php

namespace ImageKit\ImageKitMagento\Core;

interface ConfigurationInterface
{

    /**
     * @return boolean
     */
    public function isEnabled();

    /**
     * @return string
     */
    public function getEndpoint();

    /**
     * @return string
     */
    public function getPublicKey();

    /**
     * @return string
     */
    public function getPrivateKey();

    /**
     * @return string
     */
    public function isOriginConfigured();

    /**
     * @return string
     */
    public function getMediaBaseUrl();

    /**
     * @param string $file
     *
     * @return string
     */
    public function getPath($file);

    /**
     * @return string
     */
    public function generateIkuniqid();

    /**
     * @param string $filename
     * @param string $uniqid
     * 
     * @return string
     */
    public function addUniquePrefixToBasename($filename, $uniqid);
}
