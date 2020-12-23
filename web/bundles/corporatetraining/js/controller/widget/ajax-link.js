define(function (require, exports, module) {

  var Widget = require('widget');

  var tabBlockWrapper = Widget.extend({
    attrs: {
      block: '.js-ajax-tab-block-wrap',
      Link: '.js-tab-link',
      sec: '.js-tab-sec',
      _currentRequest: null,
    },

    events: {
      'click {{attrs.Link}}': 'toggle',
      'click {{attrs.block}}': 'stopPropagation'
    },

    stopPropagation: function (e) {
      e.stopPropagation();
    },

    toggle: function (e) {
      var $self = $(e.currentTarget);

      $self.siblings().removeClass('active');
      $self.addClass('active');

      var $parents = $self.closest(this.get('block'));
      var index = $self.index();

      var $sec = $parents.children(this.get('sec'));

      var temp = this.get('_currentRequest');

      var _currentRequest = $.ajax({
        url: $self.children('a').data('url'),
        data: {},
        method: 'post',
        dataType: 'html',
        success: function (res) {
          $sec.html(res);
        }
      });

    }
  });

  module.exports = tabBlockWrapper;

});
