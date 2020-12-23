import { enterSubmit } from 'app/common/form';
import notify from 'common/notify';

const $form = $('#third-party-bind-form');
const $btn = $('.js-submit-btn');

let validator = $form.validate({
  currentDom: $btn,
  ajax: true,
  rules: {
    password: {
      required: true,
    }
  },
  submitSuccess(data) {
    
    if(data.success === 0) {
      notify('danger', data.message);
    } else {
      window.location.href = data.url;
    }
  },
});


$('#password').focus(() => {
  $('.js-password-error').remove();
});

enterSubmit($form, $btn);