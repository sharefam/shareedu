define(function (require, exports, module) {

  var Notify = require('common/bootstrap-notify');

  module.exports = function ($element) {

    $('#batch-set-post').click(function () {
      var $btn = $(this);

      var ids = new Array();
      $('#post-member-table').find('[data-role=batch-item]:checked').each(function () {
        ids.push(this.value);
      })

      if (ids.length == 0) {
        Notify.danger(Translator.trans('admin.post_member.selected_empty'));
        return;
      }
      $element.find('.btn').addClass('disabled');
      $.get($btn.data('url'), {ids: ids}, function (data) {
        $("#modal").html(data).modal();
      });
    });

    $('[data-roll=item-set-post]').click(function () {
      var $btn = $(this);
      var ids = '';
      ids += $btn.attr('name') + ',';
      var primaryPostPath = '.' + $btn.attr('name') + ' .user-primary-post-show';
      var primaryPost = $(primaryPostPath).attr('name');
      var vicePostPath = $('.' + $btn.attr('name') + " span[class='user-post-show']");
      var vicePosts = [];
      $(vicePostPath).each(function () {
        vicePosts.push($(this).attr('name'));
      });

      $.get($btn.data('url'), {ids: ids, primaryPost: primaryPost, vicePosts: vicePosts}, function (data) {
        $("#uploadModal").html(data).modal();
      });
    });
  };

});
