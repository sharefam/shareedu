export default class tabBlock {
  constructor(props) {
    Object.assign(this, tabBlock.getDefaultProps() ,props);
    this.init();
  }

  static getDefaultProps() {
    return {
      block: '.js-tab-block-wrap',
      link: '.js-tab-link',
      sec: '.js-tab-sec'
    };
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    if(this.mode == 'hover') {
      $(this.element, window.parent.document).on('mouseover', this.link, (e) => this.toggle(e));
      $(this.element, window.parent.document).on('mouseover', this.block, (e) => this.stopPropagation(e));
    } else {
      $(this.element, window.parent.document).on('click', this.link, (e) => this.toggle(e));
      $(this.element, window.parent.document).on('click', this.block, (e) => this.stopPropagation(e));
    }
  }

  stopPropagation(e) {
    // e.stopPropagation();
  }

  toggle(e) {
  
    let $currentTarget = $(e.currentTarget);
    let that = this;
    $currentTarget.siblings().removeClass('active');
    $currentTarget.addClass('active');

    let $parents = $(this.element, window.parent.document).find(this['block']).eq(0);
    let index = $currentTarget.index();
    $parents.children(this['sec']).removeClass('is-active')
      .eq(index).addClass('is-active');

    if (that.cb) {
      that.cb(e);
    }
  }
}
