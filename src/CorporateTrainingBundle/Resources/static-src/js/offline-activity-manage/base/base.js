export default class Base {
  constructor() {
    this.init();
  }

  init() {
    const $form = $('#offlineActivity-form');
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
        },
        categoryId: {
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            },
          },
        },
        startDate: {
          startDate_check:true,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            },
          },
        },
        endDate: {
          endDate_check:true,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            },
          },
        },
        enrollmentEndDate: {
          enrollmentEndDate_before_check:true,
          enrollmentEndDate_after_check:true,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            },
          },
        },
        enrollmentStartDate: {
          enrollmentStartDate_check:true,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            },
          },
        },
        address: {
          byte_maxlength: 60,
          chinese_alphanumeric: true,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
        },
        maxStudentNum: {
          max:10000,
          unsigned_integer:true,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
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
        title: {
          required: Translator.trans('offline_activity.base.title_required_error_hint'),
          trim: Translator.trans('offline_activity.base.title_required_error_hint'),
        },
        categoryId: {
          required: Translator.trans('offline_activity.base.category_required_error_hint'),
          trim: Translator.trans('offline_activity.base.category_required_error_hint'),
        },
        address: {
          required: Translator.trans('offline_activity.base.place_required_error_hint'),
          trim: Translator.trans('offline_activity.base.place_required_error_hint'),
        },
        maxStudentNum: {
          unsigned_integer:Translator.trans('offline_activity.base.max_student_num.unsigned_integer'),
          max: Translator.trans('offline_activity.base.max_student_num.unsigned_integer'),
          required: Translator.trans('offline_activity.base.max_student_num.required_message'),
          trim: Translator.trans('offline_activity.base.max_student_num.required_message'),
        },
        startDate: {
          required: Translator.trans('offline_activity.base.startTime.required_error_hint'),
          trim: Translator.trans('offline_activity.base.startTime.required_error_hint'),
        },
        enrollmentStartDate: {
          required: Translator.trans('offline_activity.base.enrollment_startTime.required_error_hint'),
          trim: Translator.trans('offline_activity.base.enrollment_startTime.required_error_hint'),
        },
        enrollmentEndDate: {
          required: Translator.trans('offline_activity.base.enrollment_endTime.required_error_hint'),
          trim: Translator.trans('offline_activity.base.enrollment_endTime.required_error_hint'),
        },
        endDate: {
          required: Translator.trans('offline_activity.base.endTime.required_error_hint'),
          trim: Translator.trans('offline_activity.base.endTime.required_error_hint'),
        },
        publishOrg: {
          publish_org_check: Translator.trans('source.source_publish.select_org'),
        },
        accessOrg: {
          access_org_check: Translator.trans('source.source_publish.select_org'),
        }
      }
    });
    this.initDatePicker('#startDate');
    this.initDatePicker('#endDate');
    this.initDatePicker('#enrollmentStartDate');
    this.initDatePicker('#enrollmentEndDate');
    $('#requireAudit').change(function(){
      let requireAudit = $('[name="requireAudit"]');
      if ($('#requireAudit').is(':checked')) {
        requireAudit.val('1');
      } else {
        requireAudit.val('0');
      }
    });
    $('#offlineActivity-base-submit').click((event) => {

      if (validator.form()) {
        $(event.currentTarget).button('loading');
        $form.submit();
      }
    });

    let editor = CKEDITOR.replace('summary', {
      toolbar: 'Thread',
      filebrowserImageUploadUrl: $('#summary').data('imageUploadUrl'),
      allowedContent: true,
      height: 300
    });

    editor.on('change', () => {
      $('#summary').val(editor.getData());
    });

    editor.on('blur', () => {
      $('#summary').val(editor.getData());
    });
  }

  initDatePicker($id) {
    let $picker = $($id);
    $picker.datetimepicker({
      format: 'yyyy-mm-dd hh:ii',
      language: document.documentElement.lang,
      minView: 0, //month
      autoclose: true,
      endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
    });
    $picker.datetimepicker('setStartDate', new Date());
  }
  initExpiryMode() {
    let $startDate = $('[name="startDate"]');
    let $endDate = $('[name="endDate"]');
    let $enrollmentEndDate = $('[name="enrollmentEndDate"]');
    let $enrollmentStartDate = $('[name="enrollmentStartDate"]');

    this.elementRemoveRules($startDate);
    this.elementRemoveRules($endDate);
    this.elementRemoveRules($enrollmentStartDate);
    this.elementRemoveRules($enrollmentEndDate);
  }

}

jQuery.validator.addMethod('startDate_check', function() {
  var startDate = $('[name="startTime"]').val();
  var timestamp = Date.parse(new Date());
  var date = new Date(startDate.replace(/-/g, '/'));
  return (timestamp <= date.getTime());
}, Translator.trans('offline_activity.base.startTime_error_hint'));

jQuery.validator.addMethod('endDate_check', function() {
  var startDate = $('[name="startTime"]').val();
  var endDate = $('[name="endTime"]').val();
  return (startDate <= endDate);
}, Translator.trans('offline_activity.base.endTime_error_hint'));

jQuery.validator.addMethod('enrollmentStartDate_check', function() {
  var enrollmentStartDate = $('[name="enrollmentStartDate"]').val();
  var timestamp = Date.parse(new Date());
  var date = new Date(enrollmentStartDate.replace(/-/g, '/'));
  return (timestamp <= date.getTime());
}, Translator.trans('offline_activity.base.enrollment_startTime_error_hint'));

jQuery.validator.addMethod('enrollmentEndDate_before_check', function () {
  var enrollmentEndDate = $('[name="enrollmentEndDate"]').val();
  var endDate = $('[name="endTime"]').val();
  return (enrollmentEndDate <= endDate);
}, Translator.trans('offline_activity.base.enrollment_deadline_error_hint'));

jQuery.validator.addMethod('enrollmentEndDate_after_check', function () {
  let startDate = $('[name="enrollmentStartDate"]').val();
  let endDate = $('[name="enrollmentEndDate"]').val();
  return (startDate <= endDate);
}, Translator.trans('offline_activity.base.enrollment_endTime_error_hint'));

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


