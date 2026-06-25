define(['jquery'], function ($) {
    'use strict';

    return function (target) {
        // Allow the admin productVideoLoader to render an ImageKit iframe,
        // mirroring what fotorama-add-video-events-mixin.js does on the frontend.
        var originalProductVideoLoaderCreate = $.mage.productVideoLoader.prototype._create;

        $.mage.productVideoLoader.prototype._create = function () {
            if (this.element.data('type') !== 'imagekit') {
                return originalProductVideoLoaderCreate.call(this);
            }

            this._initialize();

            var code = this._code,
                embedUrl, iframe, id, timestamp;

            timestamp = new Date().getTime();
            id = 'imagekit' + timestamp;

            if (code && code.indexOf('ik.imagekit.io') !== -1) {
                embedUrl = code.replace('https://ik.imagekit.io/', 'https://imagekit.io/player/embed/');
                embedUrl += (embedUrl.indexOf('?') !== -1 ? '&' : '?') +
                    'controls=true&autoplay=true&background=%23000000';
            } else {
                // Non-IK domain: wrap in an srcdoc iframe
                embedUrl = null;
            }

            iframe = $('<iframe></iframe>')
                .attr('id', id)
                .attr('frameborder', 0)
                .attr('width', this._width || '100%')
                .attr('height', this._height || '100%')
                .attr('title', 'ImageKit video player')
                .attr('allow', 'accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen');

            if (embedUrl) {
                iframe.attr('src', embedUrl);
            } else {
                iframe.attr('srcdoc',
                    '<!DOCTYPE html><html><head><style>*{margin:0;padding:0}' +
                    'video{width:100%;height:100vh;object-fit:contain;background:#000}</style></head>' +
                    '<body><video src="' + code.replace(/"/g, '&quot;') + '" autoplay controls playsinline></video></body></html>'
                );
            }

            this.element.append(iframe);
            this._playing = true;
        };

        var originalOnGetVideoInformationSuccess =
            $.mage.newVideoDialog.prototype._onGetVideoInformationSuccess;

        $.mage.newVideoDialog.prototype._onGetVideoInformationSuccess = function (e, data) {
            if (data && data.videoProvider === 'imagekit') {
                var self = this;

                // Populate title / description form fields
                self.element.updateInputFields({
                    reset: false,
                    data: {
                        title: data.title,
                        description: data.description
                    }
                });

                // Load the thumbnail into the preview image slot
                if (data.thumbnail) {
                    self._loadRemotePreview(data.thumbnail);
                }

                // Render the video preview panel
                self.element
                    .on('finish_update_video finish_create_video', $.proxy(function () {
                        // no-op: field population already handled above
                    }, self))
                    .createVideoPlayer({
                        videoId: data.videoId,
                        videoProvider: data.videoProvider,
                        reset: false,
                        metaData: {
                            DOM: {
                                title: '.video-information.title span',
                                uploaded: '.video-information.uploaded span',
                                uploader: '.video-information.uploader span',
                                duration: '.video-information.duration span',
                                all: '.video-information span',
                                wrapper: '.video-information'
                            },
                            data: {
                                title: data.title,
                                uploaded: '',
                                uploader: '',
                                duration: '',
                                uploaderUrl: ''
                            }
                        }
                    })
                    .off('finish_update_video finish_create_video');

                self._videoRequestComplete = true;
                self._blockActionButtons(false);

                return;
            }

            return originalOnGetVideoInformationSuccess.call(this, e, data);
        };

        return target;
    };
});
