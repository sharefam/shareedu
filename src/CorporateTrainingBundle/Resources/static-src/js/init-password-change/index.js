
let $form = $('#password-reset-form');

let validator = $form.validate({
  rules: {
    newPassword: {
      required: true,
      check_password: true,
    },
    confirmPassword: {
      required: true,
      equalTo: '#newPassword'
    }
  }
});

$('[type="submit"]').click(()=> {
  if(validator.form()) {
    $form.submit();
  }
});

