define([
  'uiElement',
  'jquery',
  'imagekitMediaLibrary',
  'Magento_Ui/js/modal/alert',
  'mage/backend/notification',
  'mage/translate',
], function (Element, $, ikMLWidget, uiAlert, notification, $t) {
  'use strict';

  const IKMediaLibraryWidget = ikMLWidget.ImagekitMediaLibraryWidget;

  return Element.extend({
    defaults: {
      "containerId": ".imagekit-media-library-modal",
      "ikMLContainer": "#imagekit-media-library-modal-content",
      "triggerSelector": null,
      "triggerEvent": null,
      "callback": null,
      "videoImporterUrl": null
    },
    initialize: function () {
      this._super();

      const ikMLContainer = this.ikMLContainer

      const widget = this
      const aggregatedErrorMessages = [];

      $(this.containerId).modal({
        type: 'slide',
        buttons: [],
        modalClass: 'imagekit-modal',
        title: $t('ImageKit Media Library'),
        opened: function () {
          $(".imagekit-modal .modal-inner-wrap").css("display", "flex");
          $(".imagekit-modal .modal-inner-wrap").css("flex-direction", "column");
          $(".imagekit-modal .modal-inner-wrap .modal-content").css("height", "100%");
          new IKMediaLibraryWidget({
            container: ikMLContainer,
            view: 'inline',
            renderOpenButton: false
          }, (event) => {
            if (event.eventType === "INSERT") {

              for (const data of event.data) {
                var isVideo = data.fileType && data.fileType !== 'image' &&
                    /\.(mp4|webm|ogv|mov)$/i.test(data.name);
                var uploadUrl = (isVideo && widget.videoImporterUrl)
                    ? widget.videoImporterUrl
                    : widget.imageUploaderUrl;

                $.ajax({
                  url: uploadUrl,
                  data: {
                    file: data,
                    param_name: 'image',
                    form_key: window.FORM_KEY
                  },
                  method: 'POST',
                  dataType: 'json',
                  async: false,
                  showLoader: true
                }).done((file) => {
                  if (file.file && !file.error) {
                    if (widget.triggerSelector && widget.triggerEvent) {
                      jQuery(widget.triggerSelector).last().trigger(widget.triggerEvent, file);
                    }
                    if (widget.callback && typeof widget[widget.callback] === "function") {
                      widget[widget.callback](file)
                    }
                  } else {
                    console.error(file);
                    notification().add({
                      error: true,
                      message: $t('An error occured during ' + data.fileType + ' insert (' + data.fileId + ')!') + '%s%sError: ' + file.error.replace(/File:.*$/, ''),
                      insertMethod: function (constructedMessage) {
                        aggregatedErrorMessages.push(constructedMessage.replace('%s%s', '<br>'));
                      }
                    });
                  }
                }).fail(function (response) {
                  console.error(response);
                  notification().add({
                    error: true,
                    message: $t('An error occured during ' + data.fileType + ' insert (' + data.fileId + ')!')
                  });
                })
              }

              $(widget.containerId).modal('closeModal');

            } else if (event.eventType === "CLOSE_MEDIA_LIBRARY_WIDGET") {
              $(ikMLContainer).empty()
              $(widget.containerId).modal('closeModal');
            }
          })
        },
        closed: function () {
          $(ikMLContainer).empty()
        }
      });

      return this;
    },
    notifyError: function (messages) {
      var data = {
        content: messages.join('')
      };
      if (messages.length > 1) {
        data.modalClass = '_image-box';
      }
      uiAlert(data);
      return this;
    }
  })
});