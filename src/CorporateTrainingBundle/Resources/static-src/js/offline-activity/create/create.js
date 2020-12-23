export default class Create {
  constructor($element) {
    this.$element = $element;
    this.init();
  }

  init() {
    this.validator = this.$element.validate({
      currentDom: '#offline-activity-create-btn',
      rules: {
        title: {
          byte_maxlength: 200,
          required: true,
          trim: true,
        },
        categoryId: {
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          }
        }
      },
      messages: {
        title: {
          required: Translator.trans('offline_activity.base.title_required_error_hint'),
          trim: Translator.trans('offline_activity.base.title_required_error_hint'),
        },
        categoryId: {
          required: Translator.trans('offline_activity.base.category_required_error_hint'),
          trim: Translator.trans('offline_activity.base.category_required_error_hint'),
        }
      }
    });
    $('#offline-activity-create-btn').on('click', (e) => {
      let $target = $(e.target);
      if (this.validator.form()) {
        this.$element.submit();
      }
    });
  }
}
