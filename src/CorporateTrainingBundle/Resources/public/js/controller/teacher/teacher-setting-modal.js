define(function(require, exports, module) {

  let Notify = require('common/bootstrap-notify');
  let Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  require('jquery.select2-css');
  require('jquery.select2');

  exports.run = function() {
    var $form = $('#teacher-setting-form');
    var $modal = $form.parents('.modal');
    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return ;
        }

        $('#teacher-setting-btn').button('loading');
        $.post($form.attr('action'), $form.serialize()).done(function(html) {
          if(html.success){
            $modal.modal('hide');
            Notify.success(Translator.trans('admin.teacher.setting_success'));
            window.location.reload();
            $('#teacher-setting-btn').button('reset');
          }else{
            Notify.danger(Translator.trans(html.message));
            $('#teacher-setting-btn').button('reset');
          }
        }).fail(function(e) {
          Notify.danger(Translator.trans('admin.teacher.setting_error'));
          $('#teacher-setting-btn').button('reset');
        });
      }
    });

    const $teacherProfessionFieldIds = $('#teacherProfessionFieldIds');
    $teacherProfessionFieldIds.select2({
      ajax: {
        url: $teacherProfessionFieldIds.data('url'),
        dataType: 'json',
        quietMillis: 500,
        data (term, page) {
          return {
            name: term,
            page_limit: 10
          };
        },
        results (data) {
          return {
            results: data.map((item) => {
              return {id: item.id, name: item.name};
            })
          };
        }
      },
      initSelection (element, callback) {
        $('#teacherProfessionFieldIds').val('');
        callback(callback(element.data('profession')));
      },
      formatSelection (item) {
        return item.name;
      },
      formatResult (item) {
        return item.name;
      },
      formatSearching: function() {
        return Translator.trans('site.searching_hint');
      },
      formatNoMatches: function() {
        return Translator.trans('select.format_no_matches');
      },
      multiple: true,
      maximumSelectionSize: 20,
      placeholder: Translator.trans('admin.teacher.setting.field_select_placeholder'),
      width: 'off',
      createSearchChoice () {
        return null;
      },
    });

    const $levelId = $('#levelId');
    $levelId.select2({
      ajax: {
        url: $levelId.data('url'),
        dataType: 'json',
        quietMillis: 500,
        data (term, page) {
          return {
            name: term,
            page_limit: 10
          };
        },
        results (data) {
          return {
            results: data.map((item) => {
              return {id: item.id, name: item.name};
            })
          };
        }
      },
      initSelection (element, callback) {
        callback({id:element.data('level').id,name: element.data('level').name});
      },
      formatSelection (item) {
        return item.name;
      },
      formatResult (item) {
        return item.name;
      },
      formatSearching: function() {
        return Translator.trans('site.searching_hint');
      },
      formatNoMatches: function() {
        return Translator.trans('select.format_no_matches');
      },
      multiple: false,
      maximumSelectionSize: 20,
      placeholder: Translator.trans('admin.teacher.setting.level_select_placeholder'),
      width: 'off',
      createSearchChoice () {
        return null;
      },
    });
  };


})
