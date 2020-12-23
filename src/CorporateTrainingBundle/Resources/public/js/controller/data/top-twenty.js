define(function (require, exports, module) {
  $('[data-toggle="tooltip"]').tooltip();
  $('.js-tab-link').click(function () {
    var $this = $(this);
    if ($this.parents('[data-type=courseType]').length > 0) {
      $this.parents('[data-type=courseType]').find('.active').removeClass('active');
    }
    if ($this.parents('[data-type=time]').length > 0) {
      $this.parents('[data-type=time]').find('.active').removeClass('active');
    }
    $(this).addClass('active');
    $.ajax({
      type: 'GET',
      url: $this.children('a').data('url'),
      data: {
        'orgCode': $('.study-rank').find('.treeview-val').val(),
        'postId' : $('.study-rank').find("[name=postId]").val(),
        'courseType' : $('[data-type=courseType]').children('.active').find('a').data('value'),
        'type' : $('[data-type=time]').children('.active').find('a').data('value'),
      },
      dataType: 'html',
      success: function(resp) {
        $('.js-rank-list').html(resp);
      }
    });
  });

  $('.js-rank-search').click(function () {
    $.ajax({
      type: 'GET',
      url: $('.js-rank-search').data('url'),
      data: {
        'orgCode': $('.study-rank').find('[name=orgCode]').val(),
        'postId' : $('.study-rank').find("[name=postId]").val(),
        'courseType' : $('[data-type=courseType]').children('.active').find('a').data('value'),
        'type' : $('[data-type=time]').children('.active').find('a').data('value'),
      },
      dataType: 'html',
      success: function(resp) {
        $('.js-rank-list').html(resp);
      }
    });
  });
});