define(function (require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  require('jquery.select2-css');
  require('jquery.select2');
  exports.run = function () {
    var ids = [];
    $('[data-role=batch-item]:checked').each(function () {
      var id = $(this).parents('tr').attr('id');
      var userId = id.split('-').pop();
      if ($('#module').val() == 'user' && userId == $('#appUserId').val()) {
        $(this).prop('checked', false);
        $('.js-user-help').removeClass('hidden');
        return;
      }
      ids.push(userId);
    });
    if(ids.length == 0 ){
      $('#teacher-batch-setting-btn').addClass('disabled');
    }
    $('#batch-ids').val(ids);

    var $modal = $('#teacher-batch-setting-form').parents('.modal');
    var validator = new Validator({
      element: '#teacher-batch-setting-form',
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return false;
        }
        $('#teacher-batch-setting-btn').addClass('disabled');
        $.post($form.attr('action'), $form.serialize(), function(result) {
          if(result.message){
            Notify.danger(Translator.trans(result.message));
            $('#teacher-batch-setting-btn').removeClass('disabled');
          }else{
            $modal.modal('hide');
            Notify.success(Translator.trans('admin.teacher.batch_setting.success'));
            setTimeout(function(){
              window.location.reload();
            },1000);
          }
        }).error(function() {
          Notify.danger(Translator.trans('admin.teacher.batch_setting.error'));
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

});
