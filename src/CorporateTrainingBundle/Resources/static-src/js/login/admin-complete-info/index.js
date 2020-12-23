class Prefect {
  constructor() {
    this.init();
  }

  init() {
    const $form = $('#admin-complete-info-form');
    const validator = $form.validate({
      rules: {
        applicant: {
          byte_maxlength: 30,
          required: true,
        },
        companyName: {
          required: true,
        },
        province: {
          required: true,
        },
        city: {
          required: true,
        },
        industry: {
          required: true,
        },
        employeeNumber: {
          unsigned_integer: true,
          min:1,
          max:1000000,
          required: true,
        },
      },
      messages: {
        province: {
          required: Translator.trans('请选择省'),
          trim: Translator.trans('请选择省'),
        },
        city: {
          required: Translator.trans('请选择市'),
          trim: Translator.trans('请选择市'),
        },
        industry: {
          required: Translator.trans('请选择行业'),
          trim: Translator.trans('请选择行业'),
        },
      }
    });

    $('select#province').on('change', function(index, item){
      $.get( $('#city').data('url'),{ id:$('#province').val() }, function (html) {
        $('#city').empty();
        $('select#city').append('<option value=\'\'>'+'-- 市 --'+'</option>');
        html.map(function(val, index, array) {
          $('select#city').append('<option value=\''+val.id+'\'>'+val.name+'</option>');
        });
      });
    });

    $('#form-submit').click((event) => {

      if (validator.form()) {
        $(event.currentTarget).button('loading');
        $form.submit();
      }
    });

  }

}

new Prefect();