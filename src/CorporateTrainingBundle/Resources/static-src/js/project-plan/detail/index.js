import ajaxPageTask from './ajax-page-project';
import chapterAnimate from '../../common/chapter-animate';
class Page {
  constructor() {
    this.initAjax($('.js-course-item'));
  }

  initAjax($element) {

    $element.each(function () {
      new ajaxPageTask({element: $(this)}).init();
      new chapterAnimate({element: $(this)});
    });
  }
}
new Page();

