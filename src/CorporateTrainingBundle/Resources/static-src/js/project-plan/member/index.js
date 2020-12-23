import notify from 'common/notify';

const $members = $('#training_member');
var selectOptions = null;
var selectedOptions = [];
var select = $members.select2({
  ajax:{
    url: $members.data('url'),
    dataType: 'json',
    quietMillis: 500,
    data (term, page) {
      return {
        q: term,
        page_limit: 9
      };
    },
    results (data) {
      selectOptions = data;
      data.map(function(item) {
        item.attributeId = item.id;
        item.id = item.id + item.attributeType;
      });
      console.log(data);
      return {
        results: data
      };
    }
  },
  formatSelection (item) {
    return item.name;
  },
  formatResult (item) {
    if (item.attributeType === 'user') {
      return '<i class="glyphicon glyphicon-user mrs"></i>' + item.name;
    }
    if (item.attributeType === 'org') {
      return '<i class="es-icon es-icon-department_management mrs"></i>' + item.name;
    }
    if (item.attributeType === 'post') {
      return '<i class="es-icon es-icon-post mrs"></i>' + item.name;
    }
    if (item.attributeType === 'userGroup') {
      return '<i class="es-icon es-icon-group_fill"></i>' + item.name;
    }
    return item.name;
  },
  formatSearching: function() {
    return Translator.trans('project_plan.item.member.search_loading');
  },
  formatInputTooShort: function(input, min) {
    var n = min - input.length;
    return Translator.trans('project_plan.item.member.input_short_info', {num: n}) ;
  },
  formatNoMatches: function() {
    return Translator.trans('project_plan.item.member.select_result_empty');
  },
  multiple: true,
  maximumSelectionSize: 20,
  minimumInputLength: 1,
  placeholder: Translator.trans('project_plan.item.member.select_input_info'),
  width: '285px',
  createSearchChoice () {
    return null;
  },
  escapeMarkup: function (m) {
    return m;
  }
});
select.on('change', function(e) {
  if (e.added) {
    selectedOptions.push(e.added);
  } else if (e.removed) {
    var option = selectedOptions.find(function (option) {
      return option.id === e.removed.id;
    });
    var index = selectedOptions.indexOf(option);
    selectedOptions.splice(index, 1);
  }
  $('#members').val(JSON.stringify(selectedOptions));
});

let validator = $('#projectplan-member-form').validate({
  rules: {
    trainingMembers: {
      required: {
        depends () {
          $(this).val($.trim($(this).val()));
          return true;
        }
      }
    }
  },
  messages: {
    trainingMembers: {
      required: Translator.trans('project_plan.item.member.training_members'),
      trim: Translator.trans('project_plan.item.member.training_members'),
    }
  }
});

$('body').on('click', '#member-submit', function() {
  if (validator.form()) {
    $.ajax({
      method: 'post',
      url: $('#member-submit').data('url'),
      data: $('#projectplan-member-form').serialize(),
      success: function (response) {
        if (response.success) {
          notify('success', Translator.trans('project_plan.item.member.member_submit_success_message'));
          location.reload();
        } else {
          notify('danger', Translator.trans('project_plan.item.member.member_submit_error_message') + response.message);
        }
      }
    });
  }
});
