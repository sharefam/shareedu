define(function (require, exports, module) {

  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  require('jquery.select2-css');
  require('jquery.select2');

  exports.run = function () {

    var $tags = $('#user_group_member');
    var selectOptions = null;
    var selectedOptions = [];
    var select = $tags.select2({
      ajax: {
        url: $tags.data('url'),
        dataType: 'json',
        quietMillis: 500,
        data: function(term, page) {
          return {
            q: term,
            page_limit: 9
          };
        },
        results: function(data) {
          selectOptions = data;
          data.map(function (item) {
            item.memberId = item.id;
            item.id = item.id + item.attributeType;
          });
          console.log(data);
          return {
            results: data
          };
        }
      },
      formatSelection: function(item) {
        return item.name;
      },
      formatResult: function(item) {
        if (item.attributeType === 'user') {
          return '<i class="glyphicon glyphicon-user mrs"></i>' + item.name;
        }
        if (item.attributeType === 'org') {
          return '<i class="es-icon es-icon-department_management mrs"></i>' + item.name;
        }
        if (item.attributeType === 'post') {
          return '<i class="es-icon es-icon-post mrs"></i>' + item.name;
        }
        return item.name;
      },
      formatSearching: function () {
        return Translator.trans('admin.user_group_member.select_searching');
      },
      formatInputTooShort: function () {
        return Translator.trans('admin.user_group_member.select_too_short_message');
      },
      formatNoMatches: function () {
        return Translator.trans('select.format_no_matches');
      },
      multiple: true,
      maximumSelectionSize: 20,
      minimumInputLength: 1,
      placeholder: Translator.trans('admin.user_group_member.select_too_short_message'),
      width: '500px',
      createSearchChoice: function() {
        return null;
      },
      escapeMarkup: function (m) {
        return m;
      }
    })
    select.on('change', function (e) {
      if (e.added) {
        selectedOptions.push(e.added);
      } else if (e.removed) {
        var option = selectedOptions.find(function (option) {
          return option.id === e.removed.id;
        });
        var index = selectedOptions.indexOf(option);
        selectedOptions.splice(index, 1);
      }
      $('#test').val(JSON.stringify(selectedOptions));
    });
  }
})
