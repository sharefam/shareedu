define(function(require, exports, module) {

  const Notify = require('common/bootstrap-notify');
  const Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  exports.run = function() {
    let $form = $('#category-form');
    let $modal = $form.parents('.modal');

    let validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return ;
        }

        $.post($form.attr('action'), $form.serialize(), function(html) {
          $modal.modal('hide');
          location.reload();
          if ($form.data('mode') == 'add') {
            Notify.success(Translator.trans('admin.dictionary.add_success'));
          } else {
            Notify.success(Translator.trans('admin.dictionary.update_success'));
          }
        }).fail(function() {
          if ($form.data('mode') == 'add') {
            Notify.danger(Translator.trans('admin.dictionary.add_error'));
          } else {
            Notify.danger(Translator.trans('admin.dictionary.update_error'));
          }
        });
      }
    });

    validator.addItem({
      element: '#category-name-field',
      required: true,
      rule: 'visible_character minlength{min:1} maxlength{max:10} remote'
    });

    $('.radios').on('click', 'input[name=type]', function() {
      let selectedValue = $(this).attr('value');
      let url = $(this).data('url');

      $.get(url, function(html) {
        $('.category-ajax').html(html);
      });
      if (selectedValue == 'classroom' || selectedValue == 'course') {
        $('.order-form').removeClass('hide');
      }
      if (selectedValue == 'live') {
        $('.order-form').addClass('hide');
      }
    });
  };
});
