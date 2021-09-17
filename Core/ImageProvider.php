<?php

namespace ImageKit\ImageKitMagento\Core;

interface ImageProvider {
    public function retrieve(string $image, string $originalUrl);
    public function retrieveTransformed(string $image, array $transformations, string $originalUrl);
}
