initEvent();

function initEvent() {
  $('.js-sign-in').focus((e) => {
    let $target = $(e.target);
    $target.prop('placeholder', '');
  }).blur((e) => {
    let $target = $(e.target);
    if ($target.text() === '') {
      $target.prop('placeholder', 'offline_activity.student_create.field_required_error_hint');
    }
  });
}

let $form = $('#offline-sign-in-form');

let validator = $form.validate({
  rules: {
    field: {
      required: true,
      trim: true,
    }
  },
  messages: {
    field: {
      required: Translator.trans('offline_activity.student_create.field_required_error_hint'),
      trim: Translator.trans('offline_activity.student_create.field_required_error_hint'),
    }
  }
});


$('[type="submit"]').click(()=> {
  if(validator.form()) {
    $form.submit();
  }
});
