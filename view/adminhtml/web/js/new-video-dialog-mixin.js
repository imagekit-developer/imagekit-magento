define(['jquery'], function ($) {
    'use strict';

    return function (target) {
        var originalOnGetVideoInformationSuccess =
            $.mage.newVideoDialog.prototype._onGetVideoInformationSuccess;

        $.mage.newVideoDialog.prototype._onGetVideoInformationSuccess = function (e, data) {
            if (data && data.videoProvider === 'imagekit') {
                this._videoRequestComplete = true;
                this._blockActionButtons(false);

                return;
            }

            return originalOnGetVideoInformationSuccess.call(this, e, data);
        };

        return target;
    };
});
