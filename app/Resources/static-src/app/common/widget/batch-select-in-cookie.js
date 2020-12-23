import Cookies from 'js-cookie';

export default class BatchSelectInCookie {
  constructor($element, $name) {
    this.$element = $element;
    this.name = $name;
    this.itemIds = [];
    this.initEvent();
  }

  initEvent() {
    if (typeof(Cookies.get(this.name)) != 'undefined') {
      this.initChecked(Cookies.get(this.name).split(','));
    } else {
      Cookies.set(this.name, this.itemIds.join(','));
    }
    this.$element.on('click', '[data-role="batch-select"]', event=>this._batchSelectItem(event));
    this.$element.find('[data-role="batch-item"]').on('click', event=>this._selectItem(event));
    this.$element.find('#clear-cookie').on('click', event=>this._clearCookie(event));
    this.$element.parent().parent().find('.close').on('click', event=>this._clearCookie(event));
  }

  initChecked(array) {
    let length = this.$element.find('[data-role="batch-item"]').length;
    let checked_count = 0;
    this.itemIds = deleteVacancy(array);

    this.$element.find('#selected-count').text(array.length);
    this.$element.find('#itemIds').val(Cookies.get(this.name));
    $.each(array, function(index, value) {
      $('[data-item-id="' + value + '"]', window.parent.document).prop('checked', true);
    });

    this.$element.find('[data-role="batch-item"]').each(function(){
      if ($(this).is(':checked')) {
        checked_count++;
      }

      if (length == checked_count) {
        $('[data-role="batch-select"]').prop('checked', true);
      } else {
        $('[data-role="batch-select"]').prop('checked', false);
      }
    });
  }

  _batchSelectItem(event) {
    let $selectedElement = $(event.currentTarget);
    console.log(Cookies.get(this.name));
    if (Cookies.get(this.name).length > 0) {
      this.itemIds = deleteVacancy(Cookies.get(this.name).split(','));
    }
    let $itemIds = this.itemIds;
    if ($selectedElement.prop('checked') == true) {
      this.$element.find('[data-role="batch-item"]').prop('checked', true);
      this.$element.find('[data-role="batch-item"]').each(function(index, el){
        pushArrayValue($itemIds, $(this).val());
      });
    } else {
      this.$element.find('[data-role="batch-item"]').prop('checked', false);
      this.$element.find('[data-role="batch-item"]').each(function(index, el){
        popArrayValue($itemIds, $(this).val());
      });
    }

    this.$element.find('#selected-count').text($itemIds.length);
    Cookies.set(this.name, $itemIds.join(','));
    this.$element.find('#itemIds').val(Cookies.get(this.name));
  }

  _selectItem(event) {
    let $selectedElement = $(event.currentTarget);
    let length = Cookies.get(this.name).length;
    let checked_count = 0;

    if (Cookies.get(this.name).length > 0) {
      this.itemIds = deleteVacancy(Cookies.get(this.name).split(','));
    }

    let $itemIds = this.itemIds;
    console.log(this.name, Cookies.get(this.name).length, $itemIds);
    if ($selectedElement.prop('checked') == true) {
      pushArrayValue($itemIds, $selectedElement.val());
    } else {
      popArrayValue($itemIds, $selectedElement.val());
    }

    this.$element.find('[data-role="batch-item"]').each(function(){
      if ($(this).is(':checked')) {
        checked_count++;
      }

      if (length == checked_count) {
        $('[data-role="batch-select"]').prop('checked', true);
      } else {
        $('[data-role="batch-select"]').prop('checked', false);
      }
    });

    $('#selected-count', window.parent.document).text(this.itemIds.length);
    Cookies.set(this.name, this.itemIds.join(','));
    $('#itemIds').val(Cookies.get(this.name));
  }

  _clearCookie(event) {
    this.itemIds = Cookies.get(this.name).split(',');
    this.itemIds.splice(0, this.itemIds.length);
    Cookies.set(this.name, this.itemIds.join(','));
    $('#selected-count').text(0);
    $('#itemIds').val(null);
    $('input[type=checkbox]').prop('checked', false);
  }
}

let deleteVacancy = function(array){
  $.each(array, function(index, value){
    if (value == '' || value == null) {
      array.splice(index, 1);
    }
  });
  return array;
};

let pushArrayValue = function(array, targetValue){
  let isExist = false;
  $.each(array, function(index, value){
    if (value == targetValue) {
      isExist = true;
      return;
    }
  });
  console.log(array);
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