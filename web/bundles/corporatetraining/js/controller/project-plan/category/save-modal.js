define(function (require, exports, module) {

  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {
    var $form = $('#category-form');
    var $modal = $form.parents('.modal');

    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return;
        }

        $('#category-create-btn').button('submiting').addClass('disabled');

        $.post($form.attr('action'), $form.serialize()).done(function (html) {
          $modal.modal('hide');
          Notify.success(Translator.trans('保存分类成功！'));
          window.location.reload();
        }).fail(function () {
          Notify.danger(Translator.trans('添加分类失败，请重试！'));
        });

      }
    });

    validator.addItem({
      element: '#category-name-field',
      required: true,
      rule: 'byte_maxlength{max:30}'
    });

    validator.addItem({
      element: '#category-code-field',
      required: true,
      rule: 'alphanumeric not_all_digital byte_maxlength{max:30} remote'
    });

    $modal.find('.delete-category').on('click', function () {
      if (!confirm(Translator.trans('真的要删除该分类吗？'))) {
        return;
      }

      $.post($(this).data('url'), function (response) {
        if (response === false) {
          Notify.danger(Translator.trans('该类型下已创建培训项目，删除失败！'));
        } else {
          $modal.modal('hide');
          Notify.success(Translator.trans('删除分类成功！'));
          window.location.reload();
        }
      });

    });

  };

});
