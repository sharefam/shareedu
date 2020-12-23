export default class AjaxPaginator {
  constructor() {
    this.init();
  }
  
  init() {
    this.initAjaxElement();
  }
  
  initAjaxElement() {
    let that = this;
    $('.ajax-paginator-content').find('.ajax-paginator .pagination li').on('click', function() {
      $.get(this.dataset.url, function (html) {
        $('.ajax-paginator-content').html(html);
      }).success(function () {
        that.initAjaxElement();
      });
    });
  }
}