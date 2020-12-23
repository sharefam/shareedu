define(function(require, exports, module) {

  let Notify = require('common/bootstrap-notify');
  let Validator = require('bootstrap.validator');
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
          Notify.success(Translator.trans('admin.discovery_column.create_success'));
        }).fail(function() {
          Notify.danger(Translator.trans('admin.discovery_column.create_error'));
        });
      }
    });

    validator.addItem({
      element: '#category-name-field',
      required: true,
      rule: 'visible_character minlength{min:1} maxlength{max:10} remote',
    });
  };
});
