export default class Create {
  constructor($element) {
    this.$element = $element;
    this.init();
  }

  init() {
    this.validator = this.$element.validate({
      rules: {
        name: {
          byte_maxlength: 200,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
          course_title: true
        },
        orgCode: {
          required: true,
        },
      },
      messages: {
        name: {
          required: Translator.trans('project_plan.create.name.required_message'),
          trim: Translator.trans('project_plan.create.name.required_message'),
        },
      }
    });

    $('#project-plan-create-btn').on('click', (e) => {
      let $target = $(e.target);
      if (this.validator.form()) {
        $('#project-plan-create-btn').button('loading').addClass('disabled');
        this.$element.submit();
      }
    });
  }

}

