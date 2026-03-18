var config = {
  paths: {
    imagekitMediaLibrary: "//unpkg.com/imagekit-media-library-widget@2.4.1/dist/imagekit-media-library-widget.min"
  },
  config: {
    mixins: {
      'Magento_ProductVideo/js/get-video-information': {
        'ImageKit_ImageKitMagento/js/get-video-information-mixin': true
      },
      'Magento_ProductVideo/js/new-video-dialog': {
        'ImageKit_ImageKitMagento/js/new-video-dialog-mixin': true
      }
    }
  }
}