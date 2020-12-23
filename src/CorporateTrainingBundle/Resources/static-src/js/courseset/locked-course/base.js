export default class Base {
  constructor() {
    this.init();
  }

  init() {
    this.initValidator();
  }

  initValidator() {
    const $form = $('#locked-courseset-form');
    const validator = $form.validate({
      rules: {
        publishOrg: {
          publish_org_check: true
        },
        accessOrg: {
          access_org_check: true
        },
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
    $('#locked-courseset-submit').click((event) => {
      if (validator.form()) {
        $(event.currentTarget).button('loading');
        $.post($form.attr('action'), $form.serialize(), function (response) {
          window.location.reload();
        });
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
