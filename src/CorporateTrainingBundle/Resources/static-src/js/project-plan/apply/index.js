import notify from 'common/notify';

initEvent();
initDelete();
let $form = $('#project-plan-apply-form');

let validator = $form.validate({
  rules: {
    remark: {
      maxlength: 200,
    }
  },
});

function initEvent() {
  $('.js-save-btn').click(() => {
    if (validator.form()) {
      $('.js-save-btn').button('loading');
      $.post($('#project-plan-apply-form').attr('action'), $('#project-plan-apply-form').serialize(), function (json) {
        window.location.reload();
      }, 'json');
    }
  });
}

function initDelete() {
  $('#project-plan-apply-form').on('click', '.js-attachment-delete', function (event) {
    let $target = $(event.currentTarget).button('loading');

    $.post($target.data('url'),{},function(response){
      if (response.msg == 'ok') {
        notify('success', Translator.trans('site.delete_success_hint'));
        $target.closest('.ct-attachment-reset').find('.js-upload-file').show();
        $target.closest('.ct-attachment-reset').find('[data-role="fileId"]').val('');
        $target.closest('div').remove();
      }
    }).error(function(response){
      notify('danger', Translator.trans('file.not_found'));
    });
  });
}
