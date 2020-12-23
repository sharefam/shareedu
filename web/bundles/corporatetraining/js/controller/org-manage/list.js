define(function (require, exports, module) {
  require('jquery.sortable');
  
  var Notify = require('common/bootstrap-notify');
  
  exports.run = function () {
    $('#category-table-body>ul').sortable({
      distance: 20,
      isValidTarget: function ($item, container) {
        var $targetContainerItems = $(container.items).not('.placeholder');
        if ($targetContainerItems.length > 0) {
          if ($targetContainerItems.data('parentId') == $item.data('parentId')) {
            return true;
          }
        }
        return false;
      },
      onDrop: function ($item, container, _super) {
        var sortedItems = container.el.find('>li');
        var ids = [];
        sortedItems.each(function (i) {
          var $item = $(sortedItems.get(i));
          ids.push($item.data('id'));
          $item.find('>.row>.weight').text(i + 1);
        });
        
        $.post($('#category-table-body').data('sortUrl'), {ids: ids}, function (response) {
        
        });
        _super($item, container);
      }
    });
    $('.sync-department-from').click(function () {
      $(this).addClass('disabled').text($(this).data('loadingText'));
      
      $.post($(this).data('url'), function (result) {
        if (result.success) {
          Notify.success(result.message);
          window.location.reload();
        } else {
          if (result.type == 'warning') {
            Notify.warning(result.message);
          } else {
            Notify.danger(result.message);
          }
          $('.sync-department-from').removeClass('disabled').text('同步');
        }
      }).fail(function () {
        Notify.danger(Translator.trans('同步失败！'));
        $('.sync-department-from').removeClass('disabled').text('同步');
      });
    });
    
    $('.js-org-tree-select').change(function () {
      let $selectedOption = $("option:selected", this);
      let url = $selectedOption.data('url');
      window.location.href = url
    });
    
    $('.js-org-tree').on('click', '.js-org-tree-item-load-btn', function () {
      let isComplete = $(this).data('is-complete');
      let $orgTreeItem = $(this).closest('.js-org-tree-item');
      let $contentWrap = $orgTreeItem.find('.js-org-tree-item-wrap');
      let self = this;
      if (isComplete) {
        $(self).toggleClass('glyphicon-chevron-down').toggleClass('glyphicon-chevron-right');
        $contentWrap.animate({
          height: 'toggle',
          opacity: 'toggle'
        }, 'slow');
      } else {
        $contentWrap.animate({
          height: 'toggle',
          opacity: 'toggle'
        }, 'slow');
        $.get($(this).data('url'), function (html) {
          $contentWrap.html(html);
          $contentWrap.animate({
            height: 'toggle',
            opacity: 'toggle'
          }, 'slow');
          $(self).toggleClass('glyphicon-chevron-right').toggleClass('glyphicon-chevron-down');
          $(self).data('is-complete', true);
          
        });
      }
    });
  }
});
