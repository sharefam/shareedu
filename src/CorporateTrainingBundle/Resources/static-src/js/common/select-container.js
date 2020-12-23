export default class SelectContainer {
  constructor(selector) {
    this.$elem = $(selector);
    this.index = 0;
    this.items = [];
    this.$checkbox = $('.js-search-by-post').find('.js-tab-block-wrap .checkbox');
    this.init();
  }

  renderItem({ iconClassName, name}) {
    return `
      <div class="select-container-item">
        <i class="es-icon ${iconClassName} select-container__icon-l"></i>
        <span class="select-container-item__text">${name}</span>
        <i class="es-icon es-icon-icon_close_circle js-select-container-item__close select-container__icon-r"></i>
      </div>
    `;
  }

  setOpts({ removeCb = function() {} }) {
    this.removeCb = removeCb;
  }

  addItem(opts) {
    let $item = $(this.renderItem(opts));
    let obj = {
      key: opts.key,
      type: opts.type,
      id: opts.id,
      item: opts.item,
      __$item__: $item
    };
    
    $item.get(0).__relation__ = obj;

    this.items.unshift(obj);
    this.$elem.prepend($item);
  } 

  removeItem(itemObj) {
    let index = -1;

    for(let i = 0; i < this.items.length; i++) {
      if (this.items[i].key == itemObj.key) {
        index = i;
        break;
      }
    }

    if (index >= 0) {
      let obj = this.items[index];
      let $item = obj.__$item__;

      this.items.splice(index, 1);      
      obj.__$item__ = null;
      $item.remove();
      return obj;
    }
  }

  init() {
    this.initEvent(); 
  }

  onClick(e) {
    let $item = $(e.target).parent();
    let dom = $item.get(0);
    let t = dom.__relation__;
    let obj = this.removeItem(t);
    if(t.item && $(t.item).hasClass('checkbox')){
      this.postSelect(t.item);
    }
    this.removeCb && this.removeCb(obj);
  }

  initEvent() {
    this.$elem.on('click', '.js-select-container-item__close', (e) => {
      this.onClick(e);
    });
  }
  
  postSelect(dom){
    let $checkbox = this.$checkbox;
    $checkbox.each((index,item)=>{
      if($(dom).data('id') === $(item).data('id') ){
        $(item).find('.checkbox-item').prop('checked', false);
      }
    });
  }

  tojson() {
    return this.items.map(({ type, id }, index) => {
      return { type, id };
    });

  }
}