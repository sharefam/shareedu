import notify from 'common/notify';

export default class ModalController {
  constructor() {
    this.stack = [];
    this.$elem = $('#modal');
    this.$attchmentModal = $('#attachment-modal');
    this.emptyName = '.js-modal-close';
    this.$elem.get(0).__relation__ = this;

    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    this.$attchmentModal.on('show.bs.modal', () => {
      this.$elem.hide();
    });

    this.$attchmentModal.on('hide.bs.modal', (e) => {
      
      if (e.target != e.currentTarget) {
        return;
      }
      
      this.$elem.show();
      return true;
    });

    this.$elem.on('show.bs.modal', (e) => {
      if (!this.stack.length) {
        return true;
      }

      if (e.relatedTarget) {
        this.remote($(e.relatedTarget).data('url'));
      }
    });

    this.$elem.on('hide.bs.modal ', (e) => {

      if (e.target != e.currentTarget) {
        return;
      }

      if (this.$elem.find(this.emptyName).length) {
        this.empty();
        return true;
      }
      return this.close();
    });
  }

  empty() {
    this.stack.length = 0;
    this.$elem.empty();
  }

  push($elem) {
    this.stack.push($elem);
  }

  pop() {
    return this.stack.pop();
  }

  close() {
    if (this.stack.length) {
      this.$elem.empty();
      this.$elem.append(this.pop());
      return false;
    }

    return true;
  }

  open(html) {
    let children = this.$elem.children();
    if (children.length) {
      this.push(children.detach());
      this.$elem.append(html);
    }
  }

  remote(url, data) {

    $.get(url, data)
      .done((html) => {
        this.open(html);
      }).fail((html) => {
        notify('danger', Translator.trans('创建失败'));
      });
  }
}
