export default class GroupSelector {
  constructor(selector) {
    this.$elem = $(selector);
    this.itemType = 'userGroup';
    this.init();
  }

  setOpts({ addCb = function() {}, removeCb = function() {} }) {
    this.addCb = addCb;
    this.removeCb = removeCb;
  }

  getItemObj($elem) {
    return  {
      key: `icon-3/${$elem.data('id')}`,
      item: $elem.get(0),
      id: $elem.data('id'),
      type: this.itemType,
      iconClassName: 'es-icon-group_fill',
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

  toggleItem(e) {
    let $elem = $(e.currentTarget);
    let $input = $(e.target);

    if ($input.prop('checked')) {
      let obj = this.getItemObj($elem);
      this.addCb && this.addCb(obj);
    } else {
      this.removeCb && this.removeCb(this.getItemObj($elem));
    }
  }

  init() {
    this.initEvent();
  }

  initSelect2() {
    let that = this;
    let $groupContainer = $('#groupModalInput');
    let dataSource = [];
    $('.group-select-item').each((index, item) => {
      dataSource.push({id: $(item).data('id') + '', text: $(item).data('name')});
    });
    let $select = $groupContainer.select2({
      data: dataSource,
      formatSelection: function (item) {
        return item.text;
      },
      formatResult: function (item) {
        return item.text;
      },
      formatNoMatches: function() {
        return Translator.trans('advanced_user_select.not_found_hint');
      },
      placeholder: Translator.trans('advanced_user_select.user_group_input'),
      multiple: true
    });
    $select.on('change', (e) => {
      that.$elem.find('.checkbox').each((index, item) => {
        let $item = $(item);
        if ($item.data('name') == e.added.text) {
          let obj = that.getItemObj($item);
          let isChecked = $(obj.item).find('input').prop('checked');

          if (!isChecked) {
            that.addItem(obj.item);
            that.addCb && that.addCb(obj);
          }
        }
      });
      $select.select2('val', '');
    });
  }

  initEvent() {
    this.$elem.on('click', '.checkbox', (e) => {
      this.toggleItem(e);
    });

    this.initSelect2();
  }
}