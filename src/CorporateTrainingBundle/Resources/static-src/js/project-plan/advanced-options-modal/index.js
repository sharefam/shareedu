import notify from 'common/notify';
let num = 2;

const $form = $('#project-plan-advanced-options-form');

const rules = {
  remarkRequirement: {
    required: true,
    byte_maxlength: 30,
  },
  materialRequirement: {
    required: true,
    byte_maxlength: 30,
  },
  material: {
    required:true,
    byte_maxlength: 30,
  },
  accessOrg: {
    access_org_check: true
  },
  days: {
    min: 1,
    max: 9999,
    digits: true,
  },
};

const validator = $form.validate({});

$('.js-data-popover').popover({
  html: true,
  trigger: 'hover',
  placement: 'top',
  template: '<div class="popover tata-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
  content: function () {
    return $('.popover-content').html();
  }
});

function updateRules() {
  $('[name="accessOrg"]').rules('add', rules['accessOrg']);

  if ($('#requireRemark').is(':checked')) {
    $('[name="remarkRequirement"]').rules('add', rules['remarkRequirement']);
  } else {
    $('[name="remarkRequirement"]').rules('remove');
  }

  if ($('#requireMaterial').is(':checked')) {
    $('[name="materialRequirement"]').rules('add', rules['materialRequirement']);
    $('.material').each(function(index, item) {
      $(this).rules('add', rules['material']);
    });
  } else {
    $('[name="materialRequirement"]').rules('remove');
    $('.material').each(function(index, item) {
      $(this).rules('remove');
    });
  }
}

(function itemToggleInit() {
  $('#requireRemark').change(function () {
    let requireAudit = $('[name="requireRemark"]');
    if ($('#requireRemark').is(':checked')) {
      $('#remarkSetting').removeClass('hidden');
      requireAudit.val('1');
    } else {
      $('#remarkSetting').addClass('hidden');
      requireAudit.val('0');
    }
  });

  $('#requireMaterial').change(function () {
    let requireAudit = $('[name="requireMaterial"]');
    if ($('#requireMaterial').is(':checked')) {
      $('#materialSetting').removeClass('hidden');
      requireAudit.val('1');
    } else {
      requireAudit.val('0');
      $('#materialSetting').addClass('hidden');
    }
  });
})();


$('#create-material').on('click', (e) => {
  if ($('.material').length <= 2) {
    let number = num ++;
    $('.material-container').append(`<div class="form-group"><label  class="col-md-2 control-label hidden" for="material${number}" >${Translator.trans('project_plan.advanced_options.material')}</label><div class="col-md-offset-2 col-sm-8 controls"><input type="text" name="material${number}" class="form-control material" placeholder="${Translator.trans('project_plan.advanced_options.material_placeholder')}"></div><span class="es-icon es-icon-delete mrm dis-i-b mtm delete-material"></span></div>`);
  } else {
    notify('danger', Translator.trans('project_plan.advanced_options.material_danger_info'));
  }
});

$($('.materials').val().split(',')).each(function () {
  if(this.length!==0){
    let number = num ++;
    $('.material-container').append(`<div class="form-group"><label  class="col-md-2 control-label hidden" for="material${number}">${Translator.trans('project_plan.advanced_options.material')}</label><div class="col-md-offset-2 col-sm-8 controls "><input type="text"  name="material${number}" class="form-control material" placeholder="${Translator.trans('project_plan.advanced_options.material_placeholder')}"></div><span class="es-icon es-icon-delete mrm dis-i-b mtm delete-material"></span></div>`);
    $($('.material')[$('.material').length-1]).val(this);
  }

});

$('.material-container').on('click', '.delete-material', (e) => {
  $(e.target).parent('.form-group')
    .find('.material').rules('remove');
  $(e.target).parent('.form-group').remove();
});
let $modal = $form.parents('.modal');
$('#advanced-options-btn').on('click', (e) => {
  let materials = [];
  for (let i = 1; i < $('.material').length; i++) {
    materials.push($($('.material')[i]).val());
  }

  $('[name="materials"]').val(JSON.stringify(materials));

  updateRules();
  if (validator.form()) {
    $.post($form.attr('action'), $form.serialize(), function (json) {
      $modal.modal('hide');
      notify('success', Translator.trans('project_plan.advanced_options.save'));
    }, 'json');

  }

});

$('[data-toggle=\'popover\']').popover();
updateRules();

jQuery.validator.addMethod('access_org_check', function () {
  let conditionalAccess = $('[name = conditionalAccess]').val();
  let AccessOrg = $('[name = accessOrg]').val();
  if(conditionalAccess == 0 || (AccessOrg.length>0 && conditionalAccess == 1)){
    return  true;
  }
  return  false;
},  Translator.trans('source.source_publish.select_org'));

