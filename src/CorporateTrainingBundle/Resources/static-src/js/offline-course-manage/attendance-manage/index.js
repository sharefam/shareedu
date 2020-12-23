import Cookies from 'js-cookie';

class Page {
  constructor() {
    this.init();
  }

  init() {
    let $array = {'color-warning':'none', 'color-danger':'unattended', 'color-success':'attended'};

    for (let $index in $array) {
      $('.' + $index).on('click', 'a', function () {
        let url = $(this).data('url');
        if (typeof (url) !== 'undefined') {
          Cookies.set('attendStatus', $array[$index]);
          window.location.href = url;
        }
      });
    }
  }
}

new Page();