define(['jquery'], function ($) {
    'use strict';

    return function (target) {
        var originalValidateURL = $.mage.videoData.prototype._validateURL;
        var originalOnRequestHandler = $.mage.videoData.prototype._onRequestHandler;

        $.mage.videoData.prototype._validateURL = function (href, forceVideo) {
            var result = originalValidateURL.call(this, href, forceVideo);

            if (result) {
                return result;
            }

            if (typeof href === 'string') {
                var a = this._parseHref(href);

                if (a.host.match(/imagekit\.io/)) {
                    return {
                        id: href,
                        type: 'imagekit',
                        s: a.search.replace(/^\?/, '')
                    };
                }
            }

            return result;
        };

        $.mage.videoData.prototype._onRequestHandler = function () {
            var url = this.element.val(),
                videoInfo;

            if (this._currentVideoUrl === url) {
                return;
            }

            this._currentVideoUrl = url;

            this.element.trigger(this._REQUEST_VIDEO_INFORMATION_TRIGGER, {
                url: url
            });

            if (!url) {
                return;
            }

            videoInfo = this._validateURL(url);

            if (!videoInfo) {
                this._onRequestError($.mage.__('Invalid video url'));

                return;
            }

            if (videoInfo.type === 'imagekit') {
                var a = document.createElement('a');

                a.href = url;

                var cleanPath = a.pathname.replace(/\/$/, ''),
                    filename = cleanPath.split('/').pop(),
                    title = filename.replace(/\.[^.]+$/, ''),
                    thumbnail = a.protocol + '//' + a.host + cleanPath + '/ik-thumbnail.jpg' + (a.search || '');

                var respData = {
                    title: title,
                    description: '',
                    thumbnail: thumbnail,
                    videoId: videoInfo.id,
                    videoProvider: 'imagekit'
                };

                this._videoInformation = respData;
                this.element.trigger(this._UPDATE_VIDEO_INFORMATION_TRIGGER, respData);
                this.element.trigger(this._FINISH_UPDATE_INFORMATION_TRIGGER, true);

                return;
            }

            this._currentVideoUrl = null;
            originalOnRequestHandler.call(this);
        };

        return target;
    };
});
