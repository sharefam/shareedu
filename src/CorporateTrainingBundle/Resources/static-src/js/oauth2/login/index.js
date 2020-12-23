import { enterSubmit } from 'app/common/form';

const $form = $('#third-party-login-form');
const $btn = $('.js-submit-btn');

const validateMode = {
  rules: {
    account: {
      required: true,
      maxlength: 32,
      email_or_mobile_or_nickname: true,
    },
  },
};

const ruleType = $('.js-third-party-type').data('type');
const validator = $form.validate(validateMode[ruleType]);

enterSubmit($form, $btn);

$btn.click((event) => {
  let type;
  const reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  const reg_mobile = /^1\d{10}$/;
  
  if (validator.form()) {
    $(event.target).button('loading');
    let isEmail = reg_email.test($('input[name=\'account\']').val());
    let isMobile = reg_mobile.test($('input[name=\'account\']').val());
    
    if (isEmail) {
      type = 'email';
    } else if (isMobile) {
      type = 'mobile';
    } else {
      type = 'nickname';
    }
    
    $('#accountType').val(type);
    $form.submit();
  }
});

$.validator.addMethod('email_or_mobile_or_nickname', function (value, element, params) {
  let emailOrMobileOrNickname = value;
  let reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  let reg_mobile = /^1\d{10}$/;
  let reg_nickname = /^[\u4e00-\u9fa5_a-zA-Z0-9_]{2,18}/;
  let result = false;
  let isEmail = reg_email.test(emailOrMobileOrNickname);
  let isMobile = reg_mobile.test(emailOrMobileOrNickname);
  let isNickname = reg_nickname.test(emailOrMobileOrNickname);
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
