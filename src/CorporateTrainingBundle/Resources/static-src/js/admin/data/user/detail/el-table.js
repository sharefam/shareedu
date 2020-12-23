let $data = $('#data').val();
let $tips = $('#tips').val();
let $selectedColumns = $('#selectedColumns').val();
let $alternativeColumns = $('#alternativeColumns').val();

import notify from 'common/notify';
import 'vue-js';
import Cookies from 'js-cookie';
import {
  Button,
  Table,
  Input,
  TableColumn,
  Checkbox,
  Popover
} from 'element-ui';

Cookies.set('selectedColumns', $selectedColumns);
Cookies.set('alternativeColumns', $alternativeColumns);

Vue.use(Button);
Vue.use(Table);
Vue.use(Input);
Vue.use(TableColumn);
Vue.use(Checkbox);
Vue.use(Popover);

new Vue({
  el: '#tableData',
  delimiters: ['${', '}'],
  data: function () {
    return {
      isShow: false,
      alternative: false,
      selected: false,
      checkList: JSON.parse($alternativeColumns),
      selectList: JSON.parse($selectedColumns),
      tableData: JSON.parse($data)
    };
  },
  mounted() {
    this.ajaxGetDetail();
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
    cellShow() {
      this.isShow = !this.isShow;
    },
    chevron(type) {
      if(type === 'right') {
        this.mergeList('selectList', 'checkList');
      } else {
        this.mergeList('checkList', 'selectList');
      }
    },
    mergeList(targetList, sourceList) {
      let strSourceList = JSON.parse(JSON.stringify(this[sourceList]));
      let temp = [];
      for(let i = 0; i < strSourceList.length; i++) {
        if(strSourceList[i].checked) {
          temp.push(strSourceList[i]);
        }
      }
      let targetGroupId = this.getGroupId(this[targetList]);
      let sourceGroupId = this.getGroupId(this[sourceList]);
      let tempGroupId = this.getGroupId(temp);
      this.spliceGroup(tempGroupId, sourceGroupId, sourceList);
      this.addGroup(tempGroupId, targetGroupId, temp, targetList);
    },
    addGroup(tempGroupId, targetGroupId, temp, targetList) {
      for(let i = 0; i<tempGroupId.length; i++) {
        if(!targetGroupId.includes(tempGroupId[i])){
          this[targetList].push(temp[i]);
        }
      }
    },
    spliceGroup(group, byGroup, sourceList) {
      let temp = [];
      for(let i = 0;i<byGroup.length;i++) {
        if(!group.includes(byGroup[i])) {
          temp.push(this[sourceList][i]);
        }
      }
      this[sourceList] = temp;
    },
    getGroupId(targetGroup) {
      let temp = [];
      for(let i = 0;i< targetGroup.length;i++) {
        temp.push(targetGroup[i].id);
      }
      return temp;
    },
    handleCheckAllChange(val) {
      let list = this.checkList;
      this.sonList(list, val);
    },
    handleselectAllChange(val) {
      let list = this.selectList;
      this.sonList(list, val);
    },
    save() {
      const url = '/admin/user/learn_data/custom_column/save';
      let data = this.getGroupId(this.selectList);

      if(!data.length) {
        notify('warning', Translator.trans('admin.data_center.detail.at.least.one'));
        return;
      }

      data = JSON.stringify(this.getGroupId(this.selectList));
      this.fetchData(url, data).then(res => {
        console.log(res);
        if (res == 'success') {
          window.location.reload();
        }
      });
    },
    cancel: function () {
      this.checkList = JSON.parse(Cookies.get('alternativeColumns'));
      this.selectList = JSON.parse(Cookies.get('selectedColumns'));
    },
    sonList(list, val) {
      for(let i =0 ;i < list.length; i++) {
        list[i].checked = val;
      }
    },
    _list(newList, select) {
      for(let i =0 ;i < newList.length; i++) {
        if(newList[i].checked) {
          this[select] = true;
          return;
        }
      }
    },
    fetchData(url, data) {
      return new Promise((resolve, reject) => {
        $.ajax({
          url: url,
          type: 'POST',
          data: {'data': data},
          success: function (data) {
            resolve(data);
          },
          error: function () {
            console.log('error');
          },
        });
      });
    },
    ajaxGetDetail() {
      let self= this;
      $('.detail-table').on('click', '.pagination li', function () {
        let url = $(this).data('url');
        if (typeof (url) !== 'undefined') {
          $.post(url, $('#aside-department-learn-data').serialize(), function (data) {
            $('.data-list').html(data);
            let $data = $('#data').val();
            self.tableData = JSON.parse($data);
          });
        }
      });
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
  },
  watch: {
    checkList: {
      handler(newList) {
        this._list(newList, 'alternative');
      },
      deep: true,
      immediate: true
    },
    selectList: {
      handler(newList) {
        this._list(newList, 'selected');
      },
      deep: true,
      immediate: true
    }
  }
});