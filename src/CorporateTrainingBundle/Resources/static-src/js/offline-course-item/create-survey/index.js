
export default class Create {
  constructor() {
    this.$form = $('#offline-course-task-create-form');
    this.$modal = this.$form.parents('.modal');
    this.init();
  }
  
  init() {
    this.initValidator();
  }
  
  initValidator() {
    this.validator = this.$form.validate({
      rules: {
        title: {
          maxlength: 60,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
          course_title: true
        },
        mediaId: {
          required: true,
        },
      },
      messages: {
        mediaId: {
          required: Translator.trans('offline_course.choose_survey'),
          trim: Translator.trans('offline_course.choose_survey'),
        },
      }
    });
  
    $('#create-form-submit').on('click', (e) => {
      if (this.validator.form()) {
        $('#create-form-submit').button('loading');
        this.$form.submit();
      }
    });
  }
  
}

new Create();
