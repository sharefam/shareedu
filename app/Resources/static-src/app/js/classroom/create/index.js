import 'app/js/classroom-manage/classroom-create';

let $form = $('#classroom-create-form');

let validator = $form.validate({
  rules: {
    title: {
      required: true,
      minlength: 2,
      byte_maxlength: 200
    },
    orgCode: {
      required: true,
    }
  }
});

$form.on('click', '#classroom-create-btn', (event) => {
  const $target = $(event.target);
  if (validator && validator.form()) {
    $target.button('loading');
    $form.submit();
  }
});


new window.$.CheckTreeviewInput({
  $elem: $('#user-orgCode'),
  selectType: 'single',
});