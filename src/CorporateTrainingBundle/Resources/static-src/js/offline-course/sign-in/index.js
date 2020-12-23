initEvent();

function initEvent() {
  $('.js-sign-in').focus((e) => {
    let $target = $(e.target);
    $target.prop('placeholder', '');
  }).blur((e) => {
    let $target = $(e.target);
    if ($target.text() === '') {
      $target.prop('placeholder', Translator.trans('offline_course.sign_in.placeholder'));
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
      required: Translator.trans('offline_course.sign_in.placeholder'),
      trim: Translator.trans('offline_course.sign_in.placeholder'),
    }
  }
});


$('[type="submit"]').click(()=> {
  if(validator.form()) {
    $form.submit();
  }
});
