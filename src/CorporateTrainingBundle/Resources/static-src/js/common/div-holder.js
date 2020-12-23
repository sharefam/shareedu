export default class DevHolder {
  constructor(selector, opts) {
    this.$elem = $(selector);
    this.opts = opts;
    this.init();
  }

  init() {
    this.$elem.addClass('ct-dev-placeholder');
    this.$elem.attr('placeholder', this.opts.placeholder);
    this.initEvent();

    if (this.isEmpty()) {
      this.$elem.addClass('is-empty');
    }
  } 

  initEvent() {
    this.$elem.on('focus', (e) => {
      this.$elem.removeClass('is-empty');
    });

    this.$elem.on('blur', (e) => {

      if (this.isEmpty()) {
        this.$elem.addClass('is-empty');
      } 
    });
  }

  isEmpty() {
    let val = this.$elem.html();
    val = val.replace(/<div>|<\/div>/, '');

    if (val.replace(/<br>/ig,'') == '') {
      return true;
    }

    return false;
  }

  removeClass() {
    this.$elem.removeClass('is-empty');
  }
}