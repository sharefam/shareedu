export default class MemberSelector {
  constructor(selector) {
    this.$elem = $(selector);
    this.itemType = 'user';
    this.init();
    this.userSelectMap = {};
  }

  setOpts({ addCb = function() {}, removeCb = function() {} }) {
    this.addCb = addCb;
    this.removeCb = removeCb;
  }

  getItemObj($elem) {
    return  {
      key: `icon-0/${$elem.data('id')}`,
      item: null,
      id: $elem.data('id'),
      type: this.itemType,
      iconClassName: 'es-icon-person',
      name: $elem.data('name')
    };
  }

  removeItem(obj) {
    let $dom = this.$elem.find(`input[data-id="${obj.id}"]`);

    if ($dom.length) {
      $dom.prop('checked', false);
    }

    if (this.userSelectMap[obj.id]) {
      delete this.userSelectMap[obj.id];
    }
  }

  addItem(dom) {
    let $dom = $(dom);
    $dom.prop('checked', true);

    if (!this.userSelectMap[$dom.data('id')]) {
      this.userSelectMap[$dom.data('id')] = true;
    }
  }

  addItems() {
    this.$elem.find('.js-checkbox').each((index, item) => {
      if (!$(item).prop('checked')) {
        this.addItem(item);
        let obj = this.getItemObj($(item));
        this.addCb && this.addCb(obj);
      }
    });
  }

  addItemsByJson(userArray) {
    for (let i = 0; i < userArray.length; i++) {
      this.addItemByJson(userArray[i]);
    }
  }

  addItemByJson(userInfo) {
    let selectItemObj =  {
      key: `icon-0/${userInfo.id}`,
      item: null,
      id: userInfo.id,
      type: this.itemType,
      iconClassName: 'es-icon-person',
      name: userInfo.name
    };

    if (!this.userSelectMap[userInfo.id]) {
      this.userSelectMap[userInfo.id] = true;
      this.addCb && this.addCb(selectItemObj);
    }

  }

  removeItems() {
    this.$elem.find('.js-checkbox').each((index, item) => {
      if ($(item).prop('checked')) {
        let obj = this.getItemObj($(item));
        this.removeItem(obj);
        this.removeCb && this.removeCb(obj);
      }
    });
  }

  toggleItem(e) {
    let $elem = $(e.currentTarget);

    if ($elem.prop('checked')) {
      if (!this.userSelectMap[$elem.data('id')]) {
        this.userSelectMap[$elem.data('id')] = true;
      }

      let obj = this.getItemObj($elem);
      this.addCb && this.addCb(obj);
    } else {
      if (this.userSelectMap[$elem.data('id')]) {
        delete this.userSelectMap[$elem.data('id')];
      }

      this.removeCb && this.removeCb(this.getItemObj($elem));
    }
  }

  updateTable() {
    this.$elem.find('.js-checkbox').each((index, item) => {
      if (this.userSelectMap[$(item).data('id')]) {
        $(item).prop('checked', true);
      }
    });
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    this.$elem.on('click', '.js-checkbox', (e) => {
      this.toggleItem(e);
    });

    this.$elem.on('click', '.js-select-all', (e) => {
      let $target = $(e.target);
      if($target.prop('checked')) {
        this.addItems();
      } else {
        this.removeItems();
      }
    });

    this.$elem.on('click', '.js-submit', (e) => {
      $.post($(e.target).data('url'), $('#user-select-form').serialize())
        .done((html) => {
          this.$elem.find('.js-search-by-member-wrap').html(html);
          this.updateTable();
        });
    });

    this.$elem.on('click', '.js-search-all-select', (e) => {




      new Promise((resolve, reject) => {

        $.post(this.$elem.find('.js-submit').data('url'), $('#user-select-form').serialize())
          .done((html) => {

            this.$elem.find('.js-search-by-member-wrap').html(html);
            this.$elem.find('.js-checkbox').each((index, item) => {
              if (this.userSelectMap[$(item).data('id')]) {
                $(item).prop('checked', true);
              }
            });
            resolve();
          });
      }).then(() => {
        return new Promise((resolve, reject) => {
          $.post($(e.target).data('url'), $('#user-select-form').serialize())
            .done((data) => {
              if(data && data.length) {
                this.addItemsByJson(data);
              }

              this.updateTable();
            });
        });
      }).then(() => {
        console.log('finish all');
      });
    });

    this.$elem.on('click', '.pagination a', (e) => {
      $.post($(e.currentTarget).prop('href'), $('#user-select-form').serialize())
        .done((html) => {
          this.$elem.find('.js-search-by-member-wrap').html(html);
          this.updateTable();
          $('[data-toggle="popover"]').popover();
        });
      e.stopPropagation();
      e.preventDefault();
    });

    $('[data-toggle="popover"]').popover();
  }
}