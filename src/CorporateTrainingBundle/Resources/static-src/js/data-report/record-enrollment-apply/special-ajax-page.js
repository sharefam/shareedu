import ajaxPage from '../../common/ajax-page';
import tabBlock from '../../common/tab-block';

export default class specialAjaxPage extends ajaxPage {
  constructor(props) {
    const NewProps = Object.assign(specialAjaxPage.getDefaultProps(), props);
    super(NewProps);
  }

  static getDefaultProps() {
    return {
      wrappContainer: '.js-page-wrapper',
      triggerClass: '.js-page-load-btn',
      triggerUrl: 'url',
      triggerData: 'content',
      pageContent: '.js-page-content',
      _pageContentUrl: '',
      _currentState: false,
      _$target: null,
      _$wrapperTarget: null,
      _ajaxStartTime: -1,
      dataType: 'html',
      once: true,
      cycle: {
        beforeload: function() {
          this.noop();
          return true; 
        },

        loading: function() {
          this.noop();
          const $element = $(this.element);
          $element.find(this['pageContent']).animate({
            height: 'toggle',
            opacity: 'toggle'
          });
          $element.find('.js-toggle-icon').toggleClass('vis-h');
          $element.append(this.getLoadingHtml());
        },

        done: function() {
          this.noop();
        },

        fail: function(res) {
          this.noop();
          res && res.msg && console.error(res.msg) || console.log( 'request fail' + res.status + 'or something wrong with your ajax');
        },

        success: function(res) {
          this.noop();

          const $element = this['_$wrapperTarget'];
          const pageContent = this['pageContent'];
          let content = res && res.content || res ;
          $element.find(pageContent).empty().append(content);
          $element.find(pageContent).animate({
            height: 'toggle',
            opacity: 'toggle'
          });
          $element.find('.js-load-spinner').remove();
          $element.addClass('is-active').find('.js-toggle-icon').toggleClass('es-icon-anonymous-iconfont').toggleClass('es-icon-remove');
          $element.find('.js-toggle-icon').toggleClass('vis-h');
              
          $('[data-toggle="tooltip"]').tooltip({
            container: 'body'
          });

          new tabBlock({ element: $element });
          
        },

        destroy: function(res) {
          $(this.element).find(this['triggerClass']).on('click', function(){
            const $wrap = $(this).parents('.js-page-wrapper');
            $wrap.find('.js-page-content').animate({
              height: 'toggle',
              opacity: 'toggle'
            })
              .end().toggleClass('is-active');
            $wrap.find('.js-toggle-icon').toggleClass('es-icon-anonymous-iconfont').toggleClass('es-icon-remove');
          });
        }
      }
    };
  }

  getLoadingHtml() {
    return  `<div class="load-spinner js-load-spinner load-spinner-record">
              <div class="load-bounce1"></div>
              <div class="load-bounce2"></div>
              <div class="load-bounce3"></div>
            </div>`;
  }
}