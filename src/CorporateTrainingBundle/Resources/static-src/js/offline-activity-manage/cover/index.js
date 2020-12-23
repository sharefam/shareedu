import EsWebUploader from 'common/es-webuploader.js';
import notify from 'common/notify';

class Cover {
  constructor() {
    this.init();
  }

  init() {
    new EsWebUploader({
      element: '#upload-picture-btn',
      onUploadSuccess: function(file, response) {
        let url = $('#upload-picture-btn').data('gotoUrl');
        notify('success', Translator.trans('offline_activity.cover.upload.success_hint'), 1);
        document.location.href = url;
      }
    });
  }
}

new Cover();
