var config = {
    map: {
        '*': {
            videoHtml5: 'ImageKit_ImageKitMagento/js/video-html5'
        }
    },
    config: {
        mixins: {
            'Magento_ProductVideo/js/fotorama-add-video-events': {
                'ImageKit_ImageKitMagento/js/fotorama-add-video-events-mixin': true
            }
        }
    }
};
