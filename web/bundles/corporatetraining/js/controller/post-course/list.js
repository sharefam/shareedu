define(function(require, exports, module){
  require('jquery.sortable');
  var Notify = require('common/bootstrap-notify');

  exports.run = function() {
    $('.post-course-delete').click(function(){
      if (!confirm(Translator.trans('admin.post_course.delete_confirm_message'))) {
        return;
      };

      $.post($(this).data('url'), function(result){
        if (result.success) {
          Notify.success(Translator.trans('admin.post_course.delete_success'));
          window.location.reload();
        } else {
          Notify.danger(Translator.trans('admin.post_course.delete_error'));
        }
      });
    });

    var $list = $('.post-courses-list').sortable({
      distance: 20,
      itemSelector: '.sortable-item',
      onDrop: function (item, container, _super) {
        _super(item, container);
        sortList($list);
      },
      serialize: function(parent, children, isContainer) {
        return isContainer ? children : parent.attr('data-id');
      }
    });

    var sortList = function($list) {
      var data = $list.sortable('serialize').get();
      $.post($list.data('sortUrl'), {ids:data}, function(result){
        if (result.success){
          $(".sortable-item").each(function(index){
            $(this).find('.post-course-seq').html(index+1);
          });
          Notify.success(Translator.trans(result.message));
        }
      }).fail(function() {
        Notify.danger(Translator.trans('admin.post_course.sort_error'));
      });
    };
  }
});
