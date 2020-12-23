export default class DepartmentSelector {
  constructor(selector) {
    this.$elem = $(selector);
    this.itemType = 'post';
    this.$checkbox = $(selector).find('.js-tab-block-wrap .checkbox');
    this.init();
  }

  setOpts({ addCb = function() {}, removeCb = function() {} }) {
    this.addCb = addCb;
    this.removeCb = removeCb;
  }

  getItemObj($elem) {
    return  {
      key: `icon-2/${$elem.data('id')}`,
      item: $elem.get(0),
      id: $elem.data('id'),
      type: this.itemType,
      iconClassName: 'es-icon-post',
      name: $elem.data('name')
    };
  }

  addItem(dom) {
    let $input = $(dom).find('input');
    $input.prop('checked', true);
  }

  removeItem(dom) {
    let $input = $(dom).find('input');
    $input.prop('checked', false);
  }

  addGroup(e) {
    let $elem = $(e.target).parents('.js-department-select-item');
    let elemArr = [];
   
    $elem.find('.checkbox').each((index, item) => {
      if (!$(item).find('input').prop('checked')) {
        elemArr.push($(item));
        let obj = this.getItemObj($(item));
        this.addCb && this.addCb(obj);
      }
    });

    let arr = this.allSelect({type: 'add',elemArr});
    for(let i=0; i<arr.length;i++){
      this.addItem(arr[i]);
    }
  }

  toggleItem(e) {
    let $elem = $(e.currentTarget);
    let $input = $(e.target);
    let $allInput = this.$checkbox;
    let itemArr = this.singleSelect($elem,$allInput);
    if ($input.prop('checked')) {
      for(let i=0; i<itemArr.length; i++){
        $(itemArr[i]).find('.checkbox-item').prop('checked',true);
      }
      let obj = this.getItemObj($elem);
      this.addCb && this.addCb(obj);
    } else {
      for(let i=0; i<itemArr.length; i++){
        $(itemArr[i]).find('.checkbox-item').prop('checked',false);
        this.removeCb && this.removeCb(this.getItemObj(itemArr[i]));
      }
    }
  }
  singleSelect($elem,$allInput){
    let itemArr = [];
    $allInput.each((index,item)=>{
      if( $elem.data('id') === $(item).data('id')){
        itemArr.push($(item));
      }
    });
    return itemArr;
  }

  allSelect(obj){
    let arr = [];
    let $checkbox = this.$checkbox;
    let type = obj.type;
    if(type ==='add' ){
      let elemArr = obj.elemArr;
      $checkbox.each((index,item)=>{
        for(let i=0; i<elemArr.length; i++){
          if($(elemArr[i]).data('id') === $(item).data('id')){
            arr.push($(item));
          }
        }
      });
    }else{
      let $Input = obj.$Input;
      $checkbox.each((index,item)=>{
        $Input.each((i,list)=>{
          if($(list).data('id') === $(item).data('id')){
            arr.push($(item));
          }
        });
      });
    }
    return arr;
  }

  toggleBtn(e) {
    let index = $(e.target).index();
    let $elem = this.$elem.find('.post-group');
    let $btn = $(e.target).parent().children('a');
    $btn.removeClass('btn-primary');
    $btn.eq(index).addClass('btn-primary');
    $elem.hide();
    $elem.eq(index).show();
  }
  init() {
    this.initEvent();
  }

  initSelect2() {
    let that = this;
    let $postContainer = $('#postModalSelectInput');
    let dataSource = [];
    let $checkboxs = that.$elem.find('.post-group').eq(0).find('.checkbox');
    let $allCheckboxs = that.$elem.find('.checkbox');
    $('.post-select-item').each((index, item) => {
      dataSource.push({id: $(item).data('id') + '', text: $(item).data('name')});
    });
    let $select = $postContainer.select2({
      data: dataSource,
      formatSelection: function (item) {
        return item.text;
      },
      formatResult: function (item) {
        return item.text;
      },
      formatNoMatches: function() {
        return Translator.trans('advanced_user_select.bone_match_hint');
      },
      maximumSelectionSize: 20,
      allowClear: true,
      width: 'off',
      placeholder: Translator.trans('advanced_user_select.post_input'),
      multiple: true
    });
    $select.on('change', (e) => {
  
      that.selectEvent($checkboxs,e,function(obj){
        that.addCb && that.addCb(obj);
      });
  
      that.selectEvent($allCheckboxs,e,function(obj){
        that.addItem(obj.item);
      });
      $select.select2('val', '');
    });
  }
  selectEvent($elem,event,fn){
    let that = this;
    $elem.each((index, item) => {
      let $item = $(item);
      if ($item.data('name') == event.added.text) {
        let obj = that.getItemObj($item);
        let isChecked = $(obj.item).find('input').prop('checked');

        if (!isChecked) {
          fn(obj);
        }
      }
    });
  }
  initEvent() {
    this.$elem.on('click', '.checkbox', (e) => {
      this.toggleItem(e);
    });
    this.$elem.on('click', '.btn-post-select_group', (e) => {
      this.toggleBtn(e);
    });
    this.initSelect2();
    this.$elem.on('click', '.js-select-all', (e) => {
      let groupId = $(e.currentTarget).data('id');
      let $Input = $(e.currentTarget).parent().next().children('.checkbox');
      let length = $Input.length;
      let checked_count = 0;
      $Input.each(function (index, item) {
        if ($(item).find('.checkbox-item').is(':checked')) {
          checked_count++;
        }
      });
      if (checked_count == length) {
        let arr = this.allSelect({type:'remove',$Input});
        for(let i=0; i<arr.length; i++){
          $(arr[i]).find('.checkbox-item').prop('checked', false);
          this.removeCb && this.removeCb(this.getItemObj($(arr[i])));
        }
      } else {
        this.addGroup(e);
      }
    });
  }
}
