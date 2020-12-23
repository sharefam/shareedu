export default class ajaxPage {
  constructor(props) {
    Object.assign(this, ajaxPage.getDefaultProps(), props);
  }

  init() {
    this.initEvent();
  }

  initEvent() {
    $(this.element).on('click', this.triggerClass, (e) => this.loadPage(e));
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
          $(this.element).find(this['pageContent']).animate({
            height: 'toggle',
            opacity: 'toggle'
          }, 'slow');
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
          }, 'slow');
          $element.addClass('is-active');

          $('[data-toggle="tooltip"]').tooltip({
            container: 'body'
          });
        },

        destroy: function(res) {
          $(this.element).find(this['triggerClass']).on('click', function(){
            $(this).parents('.js-page-wrapper').find('.js-page-content').animate({
              height: 'toggle',
              opacity: 'toggle'
            }, 'slow')
              .end().toggleClass('is-active');
          });
        }
      }
    };  
  }

  setCircleState(val, cbormes) {
    this['_currentState'] = val;

    if (this['cycle'][val]) {
      return this[val].call(this, cbormes);
    } else {
      console.error(`no cycle ${val}`);
    }
  }

  handleState(state, args) {
    return this['cycle'][state].call(this, args);
  }

  noop() {

  }

  setup() {

  }

  loadPage(e) {
    if (this['_currentState']) {
      return false;
    }
    this.setCircleState('beforeload', e);
  }

  beforeload(e) {
    
    if (!this.handleState('beforeload', e)) {
      this.reset();
      return false;
    }

    this.setCircleState('loading', e);
  }

  loading(e) {
    this.setItem(e);
    this.handleState('loading', e);

    let $target = this['_$target'];
    let context = this;        
    let url = $target.data(this['triggerUrl']); 

    let data = $target.data(this['triggerData']) || '';
    let arr = data.match(/[a-zA-Z0-9]+\=[a-z-A-Z0-9]+/g) || [];
    let requestData = {} ;        

    for (let i = 0; i < arr.length; i++) {
      let temp = arr[i].split('=');
      requestData[temp[0]] = temp[1];
    }

    this['_pageContentUrl'] = url;
    this['_ajaxStartTime'] = new Date().getTime();
    //TODO

    $.ajax({
      url: url,
      data: requestData,
      type: 'post',
      dataType: this['dataType']
    })
      .done(function(res) {
        context.setCircleState('done', res);
      })
      .fail(function(res) {
        context.setCircleState('fail', res);
      });
  }

  done(res) {
    this.handleState('done', res);

    if (res.status != 'ok') {
      this.setCircleState('success', res);
    
    } else {
      this.setCircleState('fail', res);
    }
  }

  fail(res) {
    this.handleState('fail', res);
    this.reset();
  }

  success(res) {
    this.handleState('success', res);
    this.reset();

    if (this['once'] == true) {
      this.setCircleState('destroy' , res);
    }
  }

  destroy(res) {
    this.handleState('destroy', res);
    $(this.element).off('click', this.triggerClass, this.loadPage);
  }

  setItem(e) {
    this['_$target'] = $(e.currentTarget);
    this['_$wrapperTarget'] = $(e.currentTarget).parents(this['wrappContainer']);
  }

  reset() {
    this['_currentState'] = false;
    this['_$target'] = null;
    this['_$wrapperTarget'] = null;
    this['_ajaxStartTime'] = -1;
  }

  failReset() {
    this.reset();
  }
}