define([
    'jquery',
    'videoHtml5'
], function ($) {
    'use strict';

    return function (target) {
        var originalCreateVideoData = $.mage.AddFotoramaVideoEvents.prototype._createVideoData;

        $.mage.AddFotoramaVideoEvents.prototype._createVideoData = function (inputData, isJSON) {
            var videoData = originalCreateVideoData.call(this, inputData, isJSON),
                parsedInput = isJSON ? JSON.parse(inputData) : inputData,
                i;

            for (i = 0; i < videoData.length; i++) {
                if (parsedInput[i] && parsedInput[i].videoUrl && parsedInput[i].videoProvider === 'imagekit') {
                    videoData[i].id = parsedInput[i].videoUrl;
                    videoData[i].provider = 'imagekit';
                    videoData[i].videoUrl = parsedInput[i].videoUrl;
                }
            }

            return videoData;
        };

        // Override productVideoLoader._create to handle ImageKit videos
        // with an HTML5 <video> player instead of YouTube/Vimeo iframe.
        var originalCreate = $.mage.productVideoLoader.prototype._create;

        $.mage.productVideoLoader.prototype._create = function () {
            if (this.element.data('type') === 'imagekit') {
                this.element.videoHtml5();
                this._player = this.element.data('mageVideoHtml5');
                return;
            }
            originalCreate.call(this);
        };

        return target;
    };
});
