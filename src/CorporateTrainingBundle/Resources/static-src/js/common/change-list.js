class ChangeList {
  constructor(props) {
    this.options = {
      el: '',
      desc: '.js-item-desc',
      elActive: '.js-item-active',
      post: '.js-item-post'
    };

    Object.assign(this.options, props);
    this.init();
  }
  init() {
    this.initEvent();
  }
  initEvent() {
    this.handel();
    this.tooltip();
  }
  handel() {
    let self = this;
    let desc = this.options.desc;
    $(this.options.el).on('mouseover', desc, function () {
      self.toogle('over', this);
    }).on('mouseleave', desc, function () {
      self.toogle('leave', this);
    });
  }
  toogle(type, that) {
    let active = this.options.elActive;
    if (type === 'over') {
      $(that).find(active).addClass('hidden').parent().find(this.options.post).removeClass('hidden');
    } else if (type === 'leave') {
      $(that).find(active).removeClass('hidden').parent().find(this.options.post).addClass('hidden');
    }
  }
  tooltip() {
    let post = this.options.post;
    $(this.options.el).on('mouseover', post, function () {
      $(this).tooltip('show');
    }).on('mouseleave', post, function () {
      $(this).tooltip('destroy');
    });
  }
}
export default ChangeList;