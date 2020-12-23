import chapterAnimate from '../../common/chapter-animate';
import notify from 'common/notify';
import tabBlock from '../../common/tab-block';

class Page {
  constructor() {
    this.init();
  }
  init() {
    echo.init();
    this.initAjax($('.js-post-course-record'));
    this.initToolTip();
  }

  initAjax($element) {

    $element.each(function() {
      new chapterAnimate({element: $(this)});
    });

    $('.js-project-item__bd').each(function (index, element) {
      new tabBlock({ element: $(element) });
    });

    $element.find('.js-page-load-btn').on('click', function(){
      const $wrap = $(this).parents('.js-page-wrapper');
      $wrap.find('.js-page-content').animate({
        height: 'toggle',
        opacity: 'toggle'
      }).end().toggleClass('is-active');
    });
    
    $('.post-course-task-list').on('click', (e) => {
      let $content = $(e.target).parents('.c-project-item').children('.c-project-item__bd');
      
      if (!$content.children().length) {
        $content.html('<div class="empty">加载中...</div>');
        $.get($(e.target).data('url'), (html) => {
          $content.html(html);
        });
      }
      
    });
  }

  initToolTip() {
    $('[data-toggle="tooltip"]').tooltip({
      container: 'body'
    });
  }
}

new Page();
