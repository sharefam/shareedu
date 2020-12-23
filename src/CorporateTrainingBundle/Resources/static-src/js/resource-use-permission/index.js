import notify from 'common/notify';

const $userIds= $('#userIds');

let select = $userIds.select2({
  ajax: {
    url: $userIds.data('url'),
    dataType: 'json',
    quietMillis: 500,
    data(term, page) {
      return {
        q: term,
        page_limit: 9
      };
    },
    results(data) {
      data.map(function (item) {
        item.itemId = item.id;
      });
      return {
        results: data
      };
    }
  },
  formatSelection(item) {

    return item.truename+`(${item.nickname})`;
  },
  formatResult(item) {
    return item.truename+`(${item.nickname})`;
  },
  formatSearching: function () {
    return Translator.trans('site.searching_hint');
  },
  formatInputTooShort:function() {
    return Translator.trans('use_authorization.select_placeholder');
  },
  formatNoMatches: function() {
    return Translator.trans('select.format_no_matches');
  },
  multiple: true,
  maximumSelectionSize: 20,
  minimumInputLength: 1,
  placeholder: Translator.trans('use_authorization.select_placeholder'),
  createSearchChoice() {
    return null;
  },
  escapeMarkup: function (m) {
    return m;
  }
});


const $form = $('#use-authorization-set-form');


const validator = $form.validate({
  rules: {
    userIds: {
      required: {
        depends () {
          $(this).val($.trim($(this).val()));
          return true;
        }
      },
    },
  },
  messages: {
    userIds: {
      required: Translator.trans('use_authorization.user_ids.required_message'),
    },

  }
});


let $modal = $form.parents('.modal');
$('#use-authorization-set-btn').on('click', (e) => {

  if (validator.form()) {
    $('#use-authorization-set-btn').button('loading');
    $.post($form.attr('action'), $form.serialize(), function (json) {
      $('.js-record-count',window.parent.document).html(json);
      $modal.modal('hide');
      notify('success', Translator.trans('use_authorization.setting.save'));
    }, 'json');
  }
});

jQuery.validator.addMethod('publish_org_check', function () {
  let showable = $('[name = showable]').val();
  let publishOrg = $('[name = publishOrg]').val();
  if(showable == 0 || (publishOrg.length>0&&showable == 1)){
    return  true;
  }
  return  false;
},  Translator.trans('source.source_publish.select_org'));

jQuery.validator.addMethod('access_org_check', function () {
  let conditionalAccess = $('[name = conditionalAccess]').val();
  let AccessOrg = $('[name = accessOrg]').val();
  if(conditionalAccess == 0 || (AccessOrg.length>0 && conditionalAccess == 1)){
    return  true;
  }
  return  false;
},  Translator.trans('source.source_publish.select_org'));

