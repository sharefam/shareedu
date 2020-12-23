import notify  from 'common/notify';
import Cookies from 'js-cookie';

let deleteVacancy = function(array) {
  $.each(array, function(index, value){
    if (value == '' || value == null) {
      array.splice(index, 1);
    }
  });

  return array;
};

let initChecked = function(array)
{
  let length = $('.select-item').length;
  let checked_count = 0;
  recordIds = deleteVacancy(array);

  $('#selected-count').text(array.length);
  $('#recordIds').val(Cookies.get('recordIds'));

  $.each(array, function(index, value) {
    $('#batch-item-'+value).prop('checked', true);
  });

  $('.select-item').each(function(){
    if ($(this).is(':checked')) {
      checked_count++;
    }

    if (length == checked_count) {
      $('.batch-select').prop('checked', true);
    } else {
      $('.batch-select').prop('checked', false);
    }
  });
};

let recordIds = new Array();
if (typeof(Cookies.get('recordIds')) != 'undefined') {
  recordIds = deleteVacancy(Cookies.get('recordIds').split(','));
}
Cookies.set('recordIds', recordIds.join(','));

if (Cookies.get('recordIds').length > 0) {
  initChecked(Cookies.get('recordIds').split(','));
}

$('#quiz-table').on('click', '.batch-select',function() {
  let $selectdElement = $(this);

  if (Cookies.get('recordIds').length > 0) {
    recordIds = deleteVacancy(Cookies.get('recordIds').split(','));
  }

  if ($selectdElement.prop('checked') == true) {
    $('.select-item').prop('checked', true);
    $('.select-item').each(function(index, el){
      pushArrayValue(recordIds, $(this).val());
    });
  } else {
    $('.select-item').prop('checked', false);
    $('.select-item').each(function(index, el){
      popArrayValue(recordIds, $(this).val());
    });
  }

  $('#selected-count').text(recordIds.length);
  Cookies.set('recordIds', recordIds.join(','));
  $('#recordIds').val(Cookies.get('recordIds'));
});

$('#quiz-table').on('click', '.select-item',function() {
  let length = $('.select-item').length;
  let checked_count = 0;

  if (Cookies.get('recordIds').length > 0) {
    recordIds = deleteVacancy(Cookies.get('recordIds').split(','));
  }

  if ($(this).prop('checked') == true) {
    pushArrayValue(recordIds, $(this).val());
  } else {
    popArrayValue(recordIds, $(this).val());
  }

  $('.select-item').each(function(){
    if ($(this).is(':checked')) {
      checked_count++;
    }

    if (length == checked_count) {
      $('.batch-select').prop('checked', true);
    } else {
      $('.batch-select').prop('checked', false);
    }
  });

  $('#selected-count').text(recordIds.length);
  Cookies.set('recordIds', recordIds.join(','));
  console.log(deleteVacancy(Cookies.get('recordIds').split(',')));
  $('#recordIds').val(Cookies.get('recordIds'));
});

$('#clear-cookies').click(function(){
  recordIds = Cookies.get('recordIds').split(',');
  recordIds.splice(0, recordIds.length);
  Cookies.set('recordIds', recordIds.join(','));
  $('#selected-count').text(0);
  $('#recordIds').val(null);
  $('input[type=checkbox]').prop('checked', false);
});

$('#batch-audit').click(function(e) {
  if (existChecked($('.select-item'))) {
    notify('warning', Translator.trans('offline_activity.verify.choose_user'));
    e.stopImmediatePropagation();
    return;
  }
});

let pushArrayValue = function(array, targetValue){
  let isExist = false;
  $.each(array, function(index, value){
    if (value == targetValue) {
      isExist = true;
      return;
    }
  });

  if (!isExist && !isNaN(targetValue)) {
    array.push(targetValue);
  }
};

let popArrayValue = function(array, targetValue){
  $.each(array, function(index, value){
    if (value == targetValue) {
      array.splice(index, 1);
    }
  });
};

let existChecked = function(element) {
  let status = true;
  $(element).each(function(){
    if ($(this).prop('checked') == true) {
      status = false;
      return;
    }
  });

  return status;
};