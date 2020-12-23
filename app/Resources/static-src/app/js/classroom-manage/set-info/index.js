import 'app/js/classroom-manage/classroom-create';

initEditor();
const validator = initValidator();
toggleExpiryValue($('[name=expiryMode]:checked').val());

$('[name=\'expiryMode\']').change(function () {
  if (app.arguments.classroomStatus === 'published') {
    return false;
  }
  var expiryValue = $('[name=\'expiryValue\']').val();
  if (expiryValue) {
    if (expiryValue.match('-')) {
      $('[name=\'expiryValue\']').data('date', $('[name=\'expiryValue\']').val());
    } else {
      $('[name=\'expiryValue\']').data('days', $('[name=\'expiryValue\']').val());
    }
    $('[name=\'expiryValue\']').val('');
  }

  if ($(this).val() == 'forever') {
    $('.expiry-value-js').addClass('hidden');
  } else {
    $('.expiry-value-js').removeClass('hidden');
    var $esBlock = $('.expiry-value-js > .controls > .help-block');
    $esBlock.text($esBlock.data($(this).val()));
  }
  toggleExpiryValue($(this).val());
});

function initEditor() {
  CKEDITOR.replace('about', {
    allowedContent: true,
    toolbar: 'Detail',
    fileSingleSizeLimit: app.fileSingleSizeLimit,
    filebrowserImageUploadUrl: $('#about').data('imageUploadUrl'),
    filebrowserFlashUploadUrl: $('#about').data('flashUploadUrl')
  });

  $('[name="categoryId"]').select2({
    treeview: true,
    dropdownAutoWidth: true,
    treeviewInitState: 'collapsed',
    placeholderOption: 'first'
  });
}

function initValidator() {
  return $('#classroom-set-form').validate({
    rules: {
      title: {
        required: true,
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
    },
    messages: {
      accessOrg: {
        access_org_check: Translator.trans('source.source_publish.select_org'),
      }
    }
  });
}

function toggleExpiryValue(expiryMode) {
  if (!$('[name=\'expiryValue\']').val()) {
    $('[name=\'expiryValue\']').val($('[name=\'expiryValue\']').data(expiryMode));
  }
  elementRemoveRules($('[name=\'expiryValue\']'));
  switch (expiryMode) {
  case 'days':
    $('[name="expiryValue"]').datetimepicker('remove');
    $('.expiry-value-js .controls > span').removeClass('hidden');
    elementAddRules($('[name="expiryValue"]'),getExpiryModeDaysRules());
    validator.form();
    break;
  case 'date':
    if($('#classroom_expiryValue').attr('readonly') !== undefined){
      return false;
    }
    $('.expiry-value-js .controls > span').addClass('hidden');
    $('#classroom_expiryValue').datetimepicker({
      language: document.documentElement.lang,
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 'month',
      endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
    });
    $('#classroom_expiryValue').datetimepicker('setStartDate', new Date);
    elementAddRules($('[name="expiryValue"]'),getExpiryModeDateRules());
    validator.form();
    break;
  default:
    break;
  }
}

function getExpiryModeDaysRules() {
  return {
    required: true,
    digits:true,
    min: 1,
    max: 10000,
    messages: {
      required: Translator.trans('classroom.manage.expiry_mode_days_error_hint'),
    }
  };
}

function getExpiryModeDateRules() {
  return {
    required: true,
    date: true,
    after_now_date: true,
    messages: {
      required: Translator.trans('classroom.manage.expiry_mode_date_error_hint'),
    }
  };
}

function elementAddRules($element, options) {
  $element.rules('add', options);
}

function elementRemoveRules($element) {
  $element.rules('remove');
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

new window.$.CheckTreeviewInput({
  $elem: $('#user-orgCode'),
  selectType: 'single',
});
