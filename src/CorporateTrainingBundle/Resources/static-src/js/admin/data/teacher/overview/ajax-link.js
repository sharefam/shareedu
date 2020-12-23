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
    this.fetch();
  }

  fetch() {
    let index = this.i;
    let type = this.type;
    let $el = $(this.element);
    let url = $el.data(type);
    let $data = this['data'];
    let sec = $(this.sec)[index];
    let _currentRequest =  $.ajax({
      url,
      data: $data,
      method: 'post',
      dataType: 'html',
      success: function(res) {
        $(sec).html(res);   
      }
    });

  }
}
