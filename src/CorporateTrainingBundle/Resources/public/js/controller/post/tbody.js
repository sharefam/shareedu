define(function (require, exports, module) {

  require('jquery.sortable');
  var Notify = require('common/bootstrap-notify');
  exports.run = function () {

    $('.post>span').each(function (i) {
      if ($(this).text().length > 20) {
        $(this).attr("title", $(this).text());
        var text = $(this).text().substring(0, 20) + "...";
        $(this).text(text);
      }
    });

    $('.list-table .td.name>i').click(function () {
      var $parentNode = $(this).closest('.row');
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

    $('#post-table-body>ul').sortable({
      distance: 20,
      isValidTarget: function ($item, container) {
        var $targetContainerItems = $(container.items).not('.placeholder');
        if ($targetContainerItems.length > 0) {
          if ($targetContainerItems.data('groupId') == $item.data('groupId')) {
            return true;
          }
        }
        return false;
      },
      onDrop: function ($item, container, _super) {

        var sortedItems = container.el.find('>li');
        var ids = [];
        var groupId;
        var url;
        sortedItems.each(function (i) {
          var $item = $(sortedItems.get(i));
          groupId = $item.data('groupId');
          ids.push($item.data('id'));
          $item.find('>.row>.weight').text(i + 1);
          if (groupId) {
            $item.find('.post-seq').text(i + 1);
          }
        });

        if (groupId) {
          url = $('#post-table-body').data('postSortUrl');
        } else {
          url = $('#post-table-body').data('groupSortUrl');
        }

        $.post(url, {ids: ids}, function (response) {
        });
        _super($item, container);
      }
    });

    $('.delete-post-group').on('click', function () {
      if (!confirm(Translator.trans('admin.post_group.delete_confirm_message'))) {
        return;
      }
      var id = $(this).data('id');
      $.post($(this).data('url'), function (data) {
        if (data.success) {
          Notify.success(data.message);
          $('#' + id).remove();
        } else {
          Notify.danger(data.message);
        }
      }).fail(function () {
        Notify.danger(Translator.trans('admin.post_group.delete_error'));
      });
    });

    $('.delete-post').on('click', function () {
      if (!confirm(Translator.trans('admin.post.delete_confirm_message'))) {
        return;
      }
      var id = $(this).data('id');
      var groupId = $(this).data('groupId');
      var parent = $('#' + id).parent();
      $.post($(this).data('url'), function (result) {
        if (result.success) {
          Notify.success(result.message);
          $('#' + id).remove();
          var brother = parent.find('>li');
          brother.each(function (i) {
            var $item = $(brother.get(i));
            $item.find('.post-seq').text(i + 1);
          });
          if (brother.length == 0) {
            $('#' + groupId).remove();
          }
        } else {
          Notify.danger(result.message);
        }
      }).fail(function () {
        Notify.danger(Translator.trans('admin.post.delete_error'));
      });
    });
  };
});
