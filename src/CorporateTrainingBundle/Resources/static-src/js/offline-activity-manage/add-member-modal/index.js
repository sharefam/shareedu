import notify from 'common/notify';

let $form = $('#member-create-form');
let $modal = $form.parents('.modal');
let $btn = $('#member-create-btn');

let validator = $form.validate({
  onkeyup: false,
  rules: {
    queryfield: {
      required: true,
      remote: {
        url: $('#queryfield').data('url'),
        type: 'get',
        data: {
          'value': function () {
            return $('#queryfield').val();
          }
        }
      }
    }
  },
  messages: {
    queryfield: {
      remote: Translator.trans('offline_activity.student_create.field_required_error_hint')
    }
  }
});

const formValidate = () => {
  if (validator.form()) {
    $btn.button('submiting').addClass('disabled');
    $.post($form.prop('action'), $form.serialize(), function(result){
      if (result.success) {
        $modal.modal('hide');
        notify('success', Translator.trans('offline_activity.student_create_add_success_hint'));
        window.location.reload();
      } else {
        notify('danger', Translator.trans('offline_activity.student_create_add_failed_hint'));
        $btn.button('reset').removeClass('disabled');
      }
    }).error(function() {
      notify('danger', Translator.trans('offline_activity.student_create_add_failed_hint'));
      $btn.button('reset').removeClass('disabled');
    });
  }
};

$('.js-add-member').keydown((event) => {
  if (event.keyCode == 13) {
    formValidate();
  }
});

$btn.click(() => {
  formValidate();
});
