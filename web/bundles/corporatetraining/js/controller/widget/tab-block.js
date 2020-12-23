define(function (require, exports, module) {

  var Widget = require('widget');

  var tabBlockWrapper = Widget.extend({
    attrs: {
      block: '.js-tab-block-wrap',
      Link: '.js-tab-link',
      sec: '.js-tab-sec'
    },

    events: {
      'click {{attrs.Link}}': 'toggle',
      'click {{attrs.block}}': 'stopPropagation'
    },

    stopPropagation: function (e) {
      // e.stopPropagation();
    },

    toggle: function (e) {
      var $self = $(e.currentTarget);

      $self.siblings().removeClass('active');
      $self.addClass('active');

      var $parents = $self.closest(this.get('block'));
      var index = $self.index();

      $parents.children(this.get('sec')).removeClass('is-active')
        .eq(index).addClass('is-active');
    }
  });

  module.exports = tabBlockWrapper;

});
