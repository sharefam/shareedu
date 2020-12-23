import './follow-btn';

class Tree {
  constructor() {
    this.init();
  }

  init() {
    this.initTreeviewInput();
    this.initEvent();
  }

  initEvent() {
    $('.teacher-list').on('click', '.pagination li', function () {
      console.log('ss');
      let url = $(this).data('url');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('#teacher-list-form').serialize(), function (data) {
          $('.teacher-list').html(data);
        });
      }
    });

    $('#teacher-list-form').on('click', '.js-tab-level li', function () {
      let event = $(this).find('a');
      let url = event.data('url');
      $('#level').val(event.data('level'));
      $('.js-tab-level').find('li').each(function (e,item) {
        $(item).removeClass('active');
      });
      $(this).addClass('active');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('#teacher-list-form').serialize(), function (data) {
          $('.teacher-list').html(data);
        });
      }
    });
    $('#teacher-list-form').on('click', '.js-tab-field li', function () {
      let event = $(this).find('a');
      let url = event.data('url');
      $('#field').val(event.data('field'));
      $('.js-tab-field').find('li').each(function (e,item) {
        $(item).removeClass('active');
      });
      $(this).addClass('active');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('#teacher-list-form').serialize(), function (data) {
          $('.teacher-list').html(data);
        });
      }
    });
  }
  initTreeviewInput() {
    new window.$.CheckTreeviewInput({
      $elem: $('#resource-orgCode'),
      saveColumn: 'id',
      disableNodeCheck: false,
      transportChildren: true,
    });
  }
}

new Tree();
