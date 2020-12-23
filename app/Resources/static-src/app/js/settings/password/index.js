import notify from 'common/notify';

$('#settings-password-form').validate({
  currentDom: '#password-save-btn',
  ajax: true,
  rules: {
    'currentPassword': {
      required: true,
    },
    'newPassword': {
      required: true,
      check_password: true,
      visible_character: true
    },
    'confirmPassword': {
      required: true,
      equalTo: '#form_newPassword',
      visible_character: true
    }
  },
  submitSuccess(data) {
    notify('success', Translator.trans(data.message));
    
    $('.modal').modal('hide');
    window.location.reload();
  },
  submitError(data) {
    notify('danger',  Translator.trans(data.responseJSON.message));
  }
});
