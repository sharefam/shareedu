import notify from 'common/notify';

class batchUpdate
{
  constructor() {
    this.init();
  }

  init() {
    let $form = $('#attendance-update-form');
    let $modal = $form.parents('.modal');

    $('.js-update-attendance-btn').click(function () {
      $('.js-update-attendance-btn').button('loading');
      $.post($form.data('url'), $form.serialize(),function (result) {
        if (result) {
          $modal.modal('hide');
          notify('success', Translator.trans('project_plan.save_success'));
          window.location.reload();
        } else {
          $('.js-update-attendance-btn').button('reset');
          notify('danger', Translator.trans('project_plan.save_error'));
        }
      }).error(function() {
        $('.js-update-attendance-btn').button('reset');
        notify('danger', Translator.trans('project_plan.save_error'));
      });
    });

    this.getCheckedBatchItems();
  }

  getCheckedBatchItems() {
    let ids = [];
    $('[data-role=\'batch-item\']:checked').each(function () {
      let id = $(this).val();
      let userId = id.split('-').pop();
      ids.push(userId);
    });
    if(ids.length == 0 ){
      $('#batch-setting-post-btn').addClass('disabled');
    }
    $('#batch-ids').val(ids);
  }
}

new batchUpdate();

