import DetailDateRangePicker from '../../../../common/date-range-picker';

let dateRangePicker = new DetailDateRangePicker('.js-learn-data');

let $data = $('#data').val();
let $tips = $('#tips').val();
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
  el: '#project_plan-detail',
  delimiters: ['${', '}'],
  mounted(){
    let self= this;
    $('#project_plan-search-container').on('click', '.pagination li', function () {
      let url = $(this).data('url');
      if (typeof (url) !== 'undefined') {
        $.post(url, $('#project_plan-search-form').serialize(), function (data) {
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
    renderHeader(creatElement, { column}) {
      const l = column.label.length;
      const f = 20;
      // txt should be from column
      const txt = JSON.parse($tips)[column['property']];
      if(txt){
        const popoverIcon = creatElement('i',{class:'el-icon-question', slot: 'reference'}, []);
        const popover = creatElement('el-popover', {class: 'el-tooltip', props: {placement: 'top', trigger: 'hover'}}, [txt, popoverIcon]);
        column.minWidth = (f * l < 100 ? 100 : f * l) + f;
        return creatElement('div',{class:'table-head',style:{width:'100%'}},[column.label, popover]);
      }
      column.minWidth = (f * l < 100 ? 100 : f * l) + f;
      return creatElement('div',{class:'table-head',style:{width:'100%'}},[column.label]);

    },
    getRowClass({rowIndex}) {
      return rowIndex == 0 ? 'rowBg' : '';
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
  $elem: $('#project_plan-manage-orgCode'),
  selectType: 'single',
});