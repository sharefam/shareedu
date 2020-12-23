export default class ChapterAnimate {

  constructor(props) {
    Object.assign(this, props);
    this.init();
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    $(this.element).on('click', '.js-task-chapter', function () {
      $(this).nextUntil('.js-task-chapter').animate({height: 'toggle', opacity: 'toggle'}, 'normal');
      const $icon = $(this).children('.js-remove-icon');
      if ($icon.hasClass('es-icon-remove')) {
        $icon.removeClass('es-icon-remove').addClass('es-icon-anonymous-iconfont');
      } else {
        $icon.removeClass('es-icon-anonymous-iconfont').addClass('es-icon-remove');
      }
    });

    $(this.element).on('click', '.js-project-item-toggle-btn', function() {
      $(this).closest('.js-project-item').find('.js-project-item__bd').animate({height: 'toggle', opacity: 'toggle'}, 'normal');
      const $icon = $(this).find('.js-remove-icon');
      if ($icon.hasClass('es-icon-remove')) {
        $icon.removeClass('es-icon-remove').addClass('es-icon-anonymous-iconfont');
      } else {
        $icon.removeClass('es-icon-anonymous-iconfont').addClass('es-icon-remove');
      }
    });
  }
}