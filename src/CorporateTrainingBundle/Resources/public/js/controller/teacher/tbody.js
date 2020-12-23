define(function (require, exports, module) {

  require('jquery.sortable');
  let Notify = require('common/bootstrap-notify');
  exports.run = function () {

    $('.list-table .td.name>i').click(function () {
      let $parentNode = $(this).closest('.row');
      if ($parentNode.hasClass('row-collapse')) {
        $parentNode.removeClass('row-collapse').addClass('row-expand');
        $(this).removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
        $parentNode.next('ul.list-table').find('>li').slideDown();
      } else if ($parentNode.hasClass('row-expand')) {
        $parentNode.removeClass('row-expand').addClass('row-collapse');
        $(this).removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
        $parentNode.next('ul.list-table').find('>li').slideUp();
      }

    });

    $('#teacher-table-body>ul').sortable({
      distance: 20,
      onDrop: function ($item, container, _super) {
        let sortedItems = container.el.find('>li');
        sort(sortedItems);
        _super($item, container);
      }
    });


    $('.cancel-promote-teacher').on('click', function () {
      if (!confirm(Translator.trans('admin.teacher.list.cancel_promote_confirm_message'))) {
        return;
      }

      let id = $(this).data('id');
      let parent = $('#' + id).parent();

      $.post($(this).data('url'), function () {
        Notify.success(Translator.trans('admin.teacher.list.cancel_promote_success'));

        $('#' + id).remove();
        let brother = parent.find('>li');
        sort(brother);
      });
    });

  };

  function sort(sortedItems) {
    let ids = [];
    sortedItems.each(function (i) {
      let $item = $(sortedItems.get(i));
      ids.push($item.data('id'));
      $item.find('>.row>.weight').text(i + 1);
      $item.find('.user-seq').text(i + 1);
    });
    $.post($('#teacher-table-body').data('sortUrl'), {ids: ids}, function (response) {

    });
  }

});
