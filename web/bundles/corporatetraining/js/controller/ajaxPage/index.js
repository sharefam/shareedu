define(function (require, exports, module) {
  var Widget = require('widget');
  /**
   * [异步请求页面，添加对应周期的hook]
   * @param  {String} wrappContainer [最外层委托class名]
   * @param  {String} triggerClass [触发ajax的class名]
   * @param  {String} triggerUrl [绑定在triggerClass上的data名,表示请求地址]
   * @param  {String} triggerData  [绑定在triggerClass上的fata名,表示请求携带的参数]
   * @param  {String} _pageContentUrl [前一次请求的url地址]
   * @param  {String} _currentState [表示当前请求状态,默认是false]
   * @param  {String} _$target [表示当前点击触发对象]
   * @param  {String} _$wrapperTarget [最外层委托的jq对象]
   * @param  {String} dataType [请求返回的的类型 html/json 默认html]
   * @param  {Object} cycle [请求周期中各个hook函数, 要改请整体替换]
   * @param  {Bool} once [请求是否只执行一次]
   * @param  {Number} _ajaxStartTime [ajax请求发起时间]
   */

  var ajaxPage = Widget.extend({
    attrs: {
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
        beforeload: function () {
          this.noop();
          return true;
        },

        loading: function () {
          this.noop();
          $(this.element).find(this.get('pageContent')).animate({
            height: 'toggle',
            opacity: 'toggle'
          }, 'slow');
        },

        done: function () {
          this.noop();
        },

        fail: function (res) {
          this.noop();
          res && res.msg && console.error(res.msg) || console.log('request fail' + res.status + 'or something wrong with your ajax');
        },

        success: function (res) {
          this.noop();

          var $element = this.get('_$wrapperTarget');
          var pageContent = this.get('pageContent');
          var content = res && res.content || res;
          $element.find(pageContent).empty().append(content);
          $element.find(pageContent).animate({
            height: 'toggle',
            opacity: 'toggle'
          }, 'slow');
          $element.addClass('is-active');

          $('[data-toggle="tooltip"]').tooltip({
            container: 'body'
          });

          if (this.get('dataType') === 'html') {

          } else {

          }
        },

        destroy: function (res) {
          $(this.element).find(this.get('triggerClass')).on('click', function () {
            $(this).parents('.js-page-wrapper').find('.js-page-content').animate({
              height: 'toggle',
              opacity: 'toggle'
            }, 'slow')
              .end().toggleClass('is-active');
          });
        }
      }
    },

    events: {
      'click {{attrs.triggerClass}}': 'loadPage',
    },

    setVal: function (attr, val, cbormes) {
      this.set(attr, val);

      if (this.get('cycle')[val]) {
        return this[val].call(this, cbormes);
      }

      if (cbormes && typeof cbormes === 'function') {
        return cbormes.call(this);
      }

    },

    handleState: function (state, args) {
      return this.get('cycle')[state].call(this, args);
    },

    noop: function () {

    },

    setup: function () {

    },

    loadPage: function (e) {
      if (this.get('_currentState')) {
        return false;
      }

      this.setVal('_currentState', 'beforeload', e);
    },

    beforeload: function (e) {

      if (!this.handleState('beforeload', e)) {
        this.reset();
        return false;
      }

      this.setVal('_currentState', 'loading', e);
    },

    loading: function (e) {
      this.setItem(e);
      this.handleState('loading', e);

      var $target = this.get('_$target');
      var context = this;
      var url = $target.data(this.get('triggerUrl'));

      var data = $target.data(this.get('triggerData')) || '';
      var arr = data.match(/[a-zA-Z0-9]+\=[a-z-A-Z0-9]+/g) || [];
      var requestData = {};

      for (var i = 0; i < arr.length; i++) {
        var temp = arr[i].split('=');
        requestData[temp[0]] = temp[1];
      }

      this.set('_pageContentUrl', url);
      this.set('_ajaxStartTime', new Date().getTime());

      $.ajax({
        url: url,
        data: requestData,
        type: 'post',
        dataType: this.get('dataType')
      })
        .done(function (res) {
          context.setVal('_currentState', 'done', res);
        })
        .fail(function (res) {
          context.setVal('_currentState', 'fail', res);
        });
    },

    done: function (res) {
      this.handleState('done', res);

      if (res.status != 'ok') {
        this.setVal('_currentState', 'success', res);

      } else {
        this.setVal('_currentState', 'fail', res);
      }
    },

    fail: function (res) {
      this.handleState('fail', res);
      this.reset();
    },

    success: function (res) {
      this.handleState('success', res);
      this.reset();

      if (this.get('once') == true) {
        this.setVal('_currentState', 'destroy', res);
      }
    },

    destroy: function (res) {
      this.handleState('destroy', res);
      ajaxPage.superclass.destroy.call(this);
    },

    setItem: function (e) {
      this.set('_$target', $(e.currentTarget));
      this.set('_$wrapperTarget', $(e.currentTarget).parents(this.get('wrappContainer')));


    },

    reset: function () {
      this.set('_currentState', false);
      this.set('_$target', null);
      this.set('_$wrapperTarget', null);
      this.set('_ajaxStartTime', -1);
    },

    failReset: function () {
      this.reset();
    }
  });

  module.exports = ajaxPage;
});

