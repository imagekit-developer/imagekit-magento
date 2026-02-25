define([
    'jquery',
    'jquery-ui-modules/widget',
    'Magento_ProductVideo/js/load-player'
], function ($) {
    'use strict';

    $.widget('mage.videoHtml5', $.mage.productVideoLoader, {

        _create: function () {
            var timestamp, id, iframe;

            this._initialize();
            timestamp = new Date().getTime();
            this._autoplay = true;

            id = 'imagekit' + timestamp;
            iframe = $('<iframe></iframe>')
                .attr('frameborder', 0)
                .attr('id', id)
                .attr('width', this._width)
                .attr('height', this._height)
                .attr('title', 'ImageKit video player')
                .attr('allow', 'accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen');

            if (this._isIkDomain(this._code)) {
                iframe.attr('src', this._buildEmbedUrl(this._code));
            } else {
                iframe.attr('srcdoc', this._buildSrcdoc(this._code));
            }

            this.element.append(iframe);
            this._playing = this._autoplay;

            this.element.find('iframe').on('load', function () {
                $('#' + id).closest('.fotorama__stage__frame')
                    .addClass('fotorama__product-video--loaded');
            });

            this.element.closest('.fotorama-video-container')
                .removeClass('video-unplayed');
        },

        _isIkDomain: function (url) {
            var a = document.createElement('a');

            a.href = url;

            return !!a.host.match(/imagekit\.io/);
        },

        _buildEmbedUrl: function (videoUrl) {
            var embedUrl, a, thumbnailUrl, queryIdx;

            embedUrl = videoUrl.replace('https://ik.imagekit.io/', 'https://imagekit.io/player/embed/');

            a = document.createElement('a');
            a.href = videoUrl;
            queryIdx = a.pathname.length;
            thumbnailUrl = a.protocol + '//' + a.host + a.pathname + '/ik-thumbnail.jpg' + a.search;

            embedUrl += (embedUrl.indexOf('?') !== -1 ? '&' : '?') +
                'controls=true' +
                '&autoplay=' + (this._autoplay ? 'true' : 'false') +
                '&loop=' + (this._loop ? 'true' : 'false') +
                '&background=%23000000' +
                '&thumbnail=' + encodeURIComponent(thumbnailUrl);

            return embedUrl;
        },

        _buildSrcdoc: function (videoUrl) {
            return '<!DOCTYPE html><html><head><style>*{margin:0;padding:0}video{width:100%;height:100vh;object-fit:contain;background:#000}</style></head>' +
                '<body><video src="' + videoUrl.replace(/"/g, '&quot;') + '"' +
                (this._autoplay ? ' autoplay' : '') +
                (this._loop ? ' loop' : '') +
                ' controls playsinline></video></body></html>';
        },

        play: function () {
            this._playing = true;
        },

        pause: function () {
            this._playing = false;
        },

        stop: function () {
            this._playing = false;
        },

        playing: function () {
            return this._playing;
        },

        _destroy: function () {
            this.stop();
        }
    });

    return $.mage.videoHtml5;
});
