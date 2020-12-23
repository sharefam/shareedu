export default class Base {
  constructor() {
    this.init();
    this.initDateTimePicker();
    this.initCkeditor();
  }

  init() {
    const $form = $('#project-plan-base-form');
    this.data = {};

    const validator = $form.validate({
      rules: {
        name: {
          maxlength: 200,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
          course_title: true
        },
        startDateTime: {
          required: true,
        },
        endDateTime: {
          required: true,
          endDate_check: true,
        },
        enrollmentStartDate: {
          enrollmentStartDate_required_check: true,
        },
        enrollmentEndDate: {
          enrollmentEndDate_check: true,
          enrollmentEndDate_required_check: true,
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
        }
      },
      messages: {
        name: {
          required: Translator.trans('project_plan.base.name.required_message'),
          trim: Translator.trans('project_plan.base.name.required_message'),
        },
        startDateTime: {
          required: Translator.trans('project_plan.base.start_time.required_message'),
        },
        endDateTime: {
          required: Translator.trans('project_plan.base.end_time.required_message'),
        },
        maxStudentNum: {
          unsigned_integer:Translator.trans('project_plan.base.max_student_num.unsigned_integer'),
          max: Translator.trans('project_plan.base.max_student_num.unsigned_integer'),
          required: Translator.trans('project_plan.base.max_student_num.required_message'),
          trim: Translator.trans('project_plan.base.max_student_num.required_message'),
        },
        publishOrg: {
          publish_org_check: Translator.trans('source.source_publish.select_org'),
        }
      }
    });

    $('#project-plan-base-submit').on('click', (e) => {
      if (validator.form()) {
        $('#project-plan-base-submit').button('loading');
        $form.submit();
      }
    });

    $('#requireAudit').change(function(){
      let requireAudit = $('[name="requireAudit"]');
      if ($('#requireAudit').is(':checked')) {
        requireAudit.val('1');
      } else {
        requireAudit.val('0');
      }
    });

    $form.on('click','.js-tab-link',function () {
      if($('.js-showable-open').data('permission') ===1){
        let requireEnrollment = $('[name="requireEnrollment"]');
        if($(this).hasClass('js-showable-open')){
          $('#registration-setting').addClass('hidden');
          requireEnrollment.val(0);
          $('[name="maxStudentNum"]').val(0);
        }else{
          $('#registration-setting').removeClass('hidden');
          requireEnrollment.val(1);
        }

      }
    });
  }

  initCkeditor() {
    CKEDITOR.replace('summary', {
      allowedContent: true,
      toolbar: 'Detail',
      filebrowserImageUploadUrl: $('#project-plan-description').data('imageUploadUrl')
    });
  }

  initDateTimePicker() {
    $('#startDateTime, #endDateTime,#enrollmentStartDate,#enrollmentEndDate').datetimepicker({
      format: 'yyyy-mm-dd',
      language: document.documentElement.lang,
      minView: 2,
      autoclose: true,
      startView: 2
    });

    $('#startDateTime').on('changeDate',function(){
      $('#endDateTime').datetimepicker('setStartDate',$('#startDateTime').val().substring(0,16));
    });

    $('#endDateTime').on('changeDate',function(){
      $('#startDateTime').datetimepicker('setEndDate',$('#endDateTime').val().substring(0,16));
    });

    $('#enrollmentStartDate').on('changeDate',function(){
      $('#enrollmentEndDate').datetimepicker('setStartDate',$('#enrollmentStartDate').val().substring(0,16));
    });

    $('#enrollmentEndDate').on('changeDate',function(){
      $('#enrollmentStartDate').datetimepicker('setEndDate',$('#enrollmentEndDate').val().substring(0,16));
    });
  }

}


jQuery.validator.addMethod('endDate_check', function () {
  let startDate = $('[name="startDateTime"]').val();
  let endDate = $('[name="endDateTime"]').val();
  return (startDate <= endDate);
},  Translator.trans('project_plan.base.end_date_check'));

jQuery.validator.addMethod('enrollmentEndDate_check', function () {
  let startDate = $('[name="enrollmentStartDate"]').val();
  let endDate = $('[name="enrollmentEndDate"]').val();
  return (startDate <= endDate);
}, Translator.trans('project_plan.base.enrollment_end_date_check'));

jQuery.validator.addMethod('enrollmentStartDate_required_check', function () {
  let startDate = $('[name="enrollmentStartDate"]').val();

  if ($('[name="showable"]').val()>0 && startDate === '') {
    return false;
  }
  return true;
}, Translator.trans('project_plan.base.enrollment_start_date_required_message'));

jQuery.validator.addMethod('enrollmentEndDate_required_check', function () {
  let endDate = $('[name="enrollmentEndDate"]').val();
  if ($('[name="showable"]').val()>0 && endDate === '') {
    return false;
  }
  return true;
}, Translator.trans('project_plan.base.enrollment_end_date_required_message'));

jQuery.validator.addMethod('publish_org_check', function () {
  let showable = $('[name = showable]').val();
  let publishOrg = $('[name = publishOrg]').val();
  if(showable == 0 || (publishOrg.length>0&&showable == 1)){
    return  true;
  }
  return  false;
},  Translator.trans('source.source_publish.select_org'));
