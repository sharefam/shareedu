import notify from 'common/notify';

let $form = $('#bind-exist-form');
let $btn = $form.find('#set-bind-exist-btn');
let validator = $form.validate({
  rules: {
    emailOrMobileOrNickname: {
      required: true,
      email_or_mobile_or_nickname: true,
    },
    password: {
      required: true,
    }
  }
});

$form.keypress(function (e) {
  if (e.which == 13) {
    $btn.trigger('click');
    e.preventDefault(); // Stops enter from creating a new line
  }
});

$btn.click(() => {
  if (validator.form()) {
    $btn.button('loading');
    $('#bind-exist-form-error').hide();
    $.post($form.attr('action'), $form.serialize(), function (response) {

      console.log(response);
      if (!response.success) {
        $('#bind-exist-form-error').html(response.message).show();
        $btn.button('reset');
        return;
      }
      notify('success',Translator.trans('auth.login_bind_exist.success'));
      window.location.href = response._target_path;
    }, 'json').fail(function () {
      notify('danger',Translator.trans('auth.login_bind_exist.error'));
    }).always(function () {
      $btn.button('reset');
    });
  }
});

$.validator.addMethod('email_or_mobile_or_nickname', function (value, element, params) {
  var emailOrMobileOrNickname = value;
  var reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  var reg_mobile = /^1\d{10}$/;
  var reg_nickname = /^[\u4e00-\u9fa5_a-zA-Z0-9_]{2,18}/;
  var result = false;
  var isEmail = reg_email.test(emailOrMobileOrNickname);
  var isMobile = reg_mobile.test(emailOrMobileOrNickname);
  var isNickname = reg_nickname.test(emailOrMobileOrNickname);
  if (isMobile) {
    $('.email_mobile_msg').removeClass('hidden');
  } else {
    $('.email_mobile_msg').addClass('hidden');
  }
  if (isEmail || isMobile || isNickname) {
    result = true;
  }
  return this.optional(element) || result;
}, Translator.trans('auth.login_bind_exist.email_or_mobile_or_nickname'));

