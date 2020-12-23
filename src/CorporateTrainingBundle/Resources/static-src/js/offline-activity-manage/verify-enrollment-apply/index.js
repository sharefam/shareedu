initEvent();
let $form = $('#enrollment-verify-form');
$('[name=\'verifyStatus\']').change(function () {
  if ($(this).val() === 'rejected') {
    $('.js-approved').addClass('hidden');
    $('.js-reason').removeClass('hidden');
  } else {
    $('.js-approved').removeClass('hidden');
    $('.js-reason').addClass('hidden');
  }

});


let validator = $form.validate({
  rules: {
    rejectedReason: {
      maxlength: 200,
    }
  },
});

function initEvent() {
  $('.js-save-btn').click(() => {
    if(validator.form()) {
      $('.js-save-btn').button('loading');
      $.post($('#enrollment-verify-form').attr('action'),
        $('#enrollment-verify-form').serialize(), function () {
          window.location.reload();
        }
      );
    }
  });
}