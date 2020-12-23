import DetailDateRangePicker from '../../../../common/date-range-picker';

let dateRangePicker = new DetailDateRangePicker('.js-learn-data');

$('.js-tooltip-twig-widget').find('.js-twig-widget-tips').each(function () {
  let $self = $(this);
  $self.popover({
    html: true,
    trigger: 'hover',//'hover','click'
    placement: $self.data('placement'),//'bottom',
    content: $self.next('.js-twig-widget-html').html()
  });
});
let $data = $('#data').val();
import 'vue-js';
import {
  Button,
  Table,
  Input,
  TableColumn,
  Popover
} from 'element-ui';

Vue.use(Button);
Vue.use(Table);
Vue.use(Input);
Vue.use(TableColumn);
Vue.use(Popover);
new Vue({
  el: '#teacher-detail',
  delimiters: ['${', '}'],
  mounted(){
    let self= this;
    $('#teacher-search-container').on('click', '.pagination li', function () {
      let url = $(this).data('url');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('#user-search-form').serialize(), function (data) {
          $('.data-list').html(data);
          let $data = $('#data').val();
          self.tableData = JSON.parse($data);
        });
      }
    });
  },
  data: function () {
    return {
      visible: false,
      tableData: JSON.parse($data)
    };
  },
  methods: {
    getRowClass({rowIndex}) {
      return rowIndex == 0 ? 'rowBg' : '';
    },
    showUserDetail: function (url) {
      let $modal = $('#modal').modal();
      $modal.data('manager', this);
      $.get(url,function(html) {
        $modal.html(html);
      });
    },
    nameLen(txt) {
      $('body').append(`<span class="js-txt-len">${txt}</span>`);
    
      const flag = $('.js-txt-len').outerWidth() > 80;

      $('.js-txt-len').remove();
      return flag;
    }
  }
});

new window.$.CheckTreeviewInput({
  $elem: $('#teacher-manage-orgCode'),
  selectType: 'single',
});


