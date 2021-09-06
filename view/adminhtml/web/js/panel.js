define([
  'uiElement',
  'jquery',
  'imagekitMediaLibrary',
  'Magento_MediaGalleryUi/js/grid/columns/image/insertImageAction',
  'Magento_Ui/js/modal/alert',
  'mage/backend/notification',
  'mage/translate',
], function (Element, $, IKMediaLibraryWidget, image, uiAlert, notification, $t,) {
  'use strict';

  return Element.extend({
    defaults: {
      "containerId": ".imagekit-media-library-modal",
      "ikMLContainer": "#imagekit-media-library-modal-content"
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
              var targetElement = image.getTargetElement(window.MediabrowserUtility.targetElementId);
              if (!targetElement.length) {
                window.MediabrowserUtility.closeDialog();
                throw $t('Target element not found for content update');
              }

              for (const data of event.data) {
                $.ajax({
                  url: widget.imageUploaderUrl,
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
                    jQuery("#contents-uploader").trigger("fileuploaddone");
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
                  if (aggregatedErrorMessages.length) {
                    widget.notifyError(aggregatedErrorMessages);
                  }
                }).fail(function (response) {
                  console.error(response);
                  notification().add({
                    error: true,
                    message: $t('An error occured during ' + data.fileType + ' insert (' + data.fileId + ')!')
                  });
                  if (aggregatedErrorMessages.length) {
                    widget.notifyError(aggregatedErrorMessages);
                  }
                })
              }

              $(widget.containerId).modal('closeModal');

            }
          })
        },
        closed: function () {
          $(ikMLContainer).empty()
        }
      }).bind(this);

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
    },
  })
});