define(function (require, exports, module) {

  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);


  exports.run = function () {
    var $form = $('#post-correlation-form');
    var $modal = $form.parents('.modal');
    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function (error, results, $form) {
        if (error) {
          return;
        }

        $('#post-btn').button('loading');
        $.post($form.attr('action'), $form.serialize()).done(function (html) {
          $modal.modal('hide');
          Notify.success(Translator.trans('admin.course.post_set_success'));
          $('#post-btn').button('reset');
        }).fail(function () {
          Notify.danger(Translator.trans('admin.course.post_set_error'));
          $('#post-btn').button('reset');
        });
      }
    });

    const $postNames = $('#postNames');
    $postNames.select2({
      ajax: {
        url: $postNames.data('url'),
        dataType: 'json',
        quietMillis: 500,
        data(term, page) {
          return {
            name: term,
            page_limit: 10
          };
        },
        results(data) {
          return {
            results: data.map((item) => {
              return {id: item.id, name: item.name};
            })
          };
        }
      },
      initSelection(element, callback) {
        const data = [];
        $(element.val().split(',')).each(function () {
          data.push({
            id: this,
            name: this
          });
        });
        callback(data);
      },
      formatSelection(item) {
        return item.name;
      },
      formatResult(item) {
        return item.name;
      },
      formatSearching: function () {
        return Translator.trans('site.searching_hint');
      },
      multiple: true,
      maximumSelectionSize: 20,
      placeholder: Translator.trans('admin.course.select_placeholder'),
      width: 'off',
      createSearchChoice() {
        return null;
      },
    });

    $modal.find('.post').on('click', function (e) {
      if (confirm(Translator.trans('admin.course.delete_post_confirm_message'))) {
        $.post($(this).data('url'), function (response) {
          if (response) {
            $(e.target).parent('.course_post_correlation').remove();
          }
          Notify.success(Translator.trans('admin.course.delete_post_success'));
        });
      }
    });

  };


})
