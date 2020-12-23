export default class Base {
  constructor() {
    this.init();
  }

  init() {
    this.initValidator();
    this.initTags();
  }

  initValidator() {
    const $form = $('#courseset-form');
    const validator = $form.validate({
      rules: {
        title: {
          byte_maxlength: 200,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
          course_title: true
        },
        publishOrg: {
          publish_org_check: true
        },
        accessOrg: {
          access_org_check: true
        },
        days: {
          min: 1,
          max: 9999,
          digits: true,
        },
        subtitle: {
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return false;
            }
          },
          course_title: true
        },
        maxStudentNum: {
          required: true,
          digits: true
        }
      },
      messages: {
        publishOrg: {
          publish_org_check: Translator.trans('source.source_publish.select_org'),
        },
        accessOrg: {
          access_org_check: Translator.trans('source.source_publish.select_org'),
        }
      }
    });
    $('#courseset-base-submit').click((event) => {
      if (validator.form()) {
        $(event.currentTarget).button('loading');
        $form.submit();
      }
    });
  }

  initTags() {
    const $tags = $('#tags');
    $tags.select2({
      ajax: {
        url: $tags.data('url'),
        dataType: 'json',
        quietMillis: 500,
        data (term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results (data) {
          console.log(data);
          return {
            results: data.map((item) => {
              return { id: item.name, name: item.name };
            })
          };
        }
      },
      initSelection (element, callback) {
        const data = [];
        $(element.val().split(',')).each(function () {
          data.push({
            id: this,
            name: this
          });
        });
        callback(data);
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
      multiple: true,
      maximumSelectionSize: 20,
      placeholder: Translator.trans('course_set.manage.tag_required_hint'),
      width: 'off',
      createSearchChoice () {
        return null;
      }
    });
  }
}
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
