export default class ajaxLink {
  constructor(props) {
    Object.assign(this, ajaxLink.getDefaultProps(), props);
    this.init();
  }

  static getDefaultProps() {
    return  {     
      block: '.js-ajax-tab-block-wrap',
      link: '.js-tab-link',
      sec: '.js-tab-sec',
      data: {},
      _currentRequest: null
    };
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    if(this.mode == 'hover') {
      $(this.element).on('mouseover', this.link, (e) => this.toggle(e));
      $(this.element).on('mouseover', this.block, (e) => this.stopPropagation(e));

    } else {
      $(this.element).on('click', this.link, (e) => this.toggle(e));
      $(this.element).on('click', this.block, (e) => this.stopPropagation(e));
    }
  }

  stopPropagation(e) {
    e.stopPropagation();
  }

  toggle(e) {
    let $currentTarget = $(e.currentTarget);
    let that = this;
    $currentTarget.siblings().removeClass('active');
    $currentTarget.addClass('active');

    let $parents = $(this.element).find(this['block']).eq(0);
    let index = $currentTarget.index();

    let $sec = $parents.children(this['sec']);

    let $data = this['data'];
    let temp = this['_currentRequest'];

    let _currentRequest =  $.ajax({
      url: $currentTarget.children('a').data('url'),
      data: $data,
      method: 'post',
      dataType: 'html',
      success: function(res) {
        $sec.html(res);

        if(that.cb) {
          that.cb(e);
        }
      }
    });

  }
}
