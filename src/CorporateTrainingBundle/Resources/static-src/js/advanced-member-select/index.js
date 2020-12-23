import 'common/tabs-lavalamp/index';
import notify from 'common/notify';

import tabBlock from '../common/tab-block';
import SelectContainer from '../common/select-container';
import GroupSelector from './group-selector';
import DepartmentSelector from './department-selector';
import PostSelector from './post-selector';
import MemberSelector from './member-selector';
import asyncArray from '../common/async-array';
import Load from '../common/load';
$('.nav.nav-tabs').unbind();

let container =  new SelectContainer('.js-group-selector');
let groupSelector =  new GroupSelector('.js-search-by-group');
let postSelector = new PostSelector('.js-search-by-post');
let departmentSelector = new DepartmentSelector('.js-search-by-department');
let memberSelector = new MemberSelector('.js-search-by-member');

container.setOpts({
  removeCb: (obj) => {
    if (obj.type == memberSelector.itemType) {
      memberSelector.removeItem(obj);
    } else if (obj.type == departmentSelector.itemType) {
      departmentSelector.removeItem(obj.item);
    } else if (obj.type == postSelector.itemType) {
      postSelector.removeItem(obj.item);
    } else if (obj.type == groupSelector.itemType) {
      groupSelector.removeItem(obj.item);
    }
  }
});

postSelector.setOpts({
  addCb: (obj) => {
    container.addItem(obj);
  },
  removeCb: (obj) => {
    container.removeItem(obj);
  }
});

groupSelector.setOpts({
  addCb: (obj) => {
    container.addItem(obj);
  },
  removeCb: (obj) => {
    container.removeItem(obj);
  }
});

departmentSelector.setOpts({
  addCb: (obj) => {
    container.addItem(obj);
  },
  removeCb: (obj) => {
    container.removeItem(obj);
  }
});


memberSelector.setOpts({
  addCb: (obj) => {
    container.addItem(obj);
  },
  removeCb: (obj) => {
    container.removeItem(obj);
  }
});

new tabBlock({ element: '.js-member-projectplan', mode: 'click'});

class Page {
  constructor() {
    this.init();
  }

  init() {
    this.initTreeViewInput();
    this.initSelect();
    this.initDatetimePicker();
    this.initvalidator();
  }

  initSelect() {
    $('[data-role="tree-select"], [name="categoryId"]').select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholderOption: 'first',
      allowClear: true,
    });
  }

  initDatetimePicker() {
    const defaultOpts = {
      language: 'zh',
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 'month'
    };
    $('[name=hireDate_GTE]').datetimepicker(defaultOpts);

    $('[name=hireDate_LTE]').datetimepicker(defaultOpts);

    $('[name=hireDate_GTE]').on('changeDate',function(){
      $('[name=hireDate_LTE]').datetimepicker('setStartDate',$('[name=hireDate_GTE]').val().substring(0,16));
    });

    $('[name=hireDate_LTE]').on('changeDate',function(){
      $('[name=hireDate_GTE]').datetimepicker('setEndDate',$('[name=hireDate_LTE]').val().substring(0,16));
    });
  }

  initvalidator() {
    const $form = $('.department-search-form');

    var $postContainer = $('#postModalInput');
    $postContainer.select2({
      ajax: {
        url: $postContainer.data('url'),
        dataType: 'json',
        quietMillis: 100,
        data: function (term, page) {
          return {
            q: term,
            page_limit: 10
          };
        },
        results: function (data) {
          var results = [];
          $.each(data, function (index, item) {

            results.push({
              id: item.id,
              name: item.name
            });
          });

          return {
            results: results
          };

        }
      },
      initSelection: function(element, callback) {
        var id = $(element).val();
        if (id !== '') {
          var name = $(element).data('name');
          callback({id:id, name:name});
        }
      },
      formatSelection: function (item) {
        return item.name;
      },
      formatResult: function (item) {
        return item.name;
      },
      allowClear: true,
      width: 'off',
      placeholder: Translator.trans('advanced_user_select.all_post')
    });

  }

  initTreeViewInput() {
    new window.$.CheckTreeviewInput({
      $elem: $('.user-org-select').find('#resource-orgCode'),
      saveColumn: 'id',
      disableNodeCheck: false,
      transportChildren: true,
    });
  }
}

new Page();
$('[data-toggle="popover"]').popover();
$('#notificationSetting').prop('checked', true);

$('.js-add-btn').on('click', function() {
  let $notificationSetting = $('#notificationSetting').is(':checked') === true ? 1: 0;
  const load = new Load();
  load.show();
  $('.js-add-btn').button('loading');
  $.post($(this).data('convert-attribute-url'), {
    userAttribute: JSON.stringify(container.tojson()),
  }).done((data) => {
    if (data.userIds && data.userIds.length) {
      let increment = 200;
      let len = data.userIds.length;
      let time = Math.ceil(len/increment);
      let pos = 0;
      let end = 0;
      let temp = [];
      while (pos <= len - 1) {
        end = Math.min(pos + increment , len) - 1;
        temp.push(data.userIds.slice(pos, end + 1));
        pos = end + 1;
      }

      let asyncArr = new asyncArray();

      for (let i = 0 ; i < temp.length; i++) {
        asyncArr.push( (next) => {
          load.setPercent(Math.ceil(((i+1)/time)*100));
          $.post($(this).data('batch-add-url'), {
            userIds: temp[i],
            notificationSetting: $notificationSetting,
          }).done((data) => {
            if(asyncArr.isEmpty()) {
              notify('success',Translator.trans('advanced_user_select.add_member_success_hint'));
              $('.js-add-btn').button('reset');
              load.remove();
              window.location.reload();
            }

            if (data.status) {
              load.update();
              next();
            } else {
              notify('danger',Translator.trans('advanced_user_select.add_member_fail_hint'));
              load.remove();
            }
          });
        });
      }
    } else {
      notify('warning',Translator.trans('advanced_user_select.not_found_user_in_selected_hint'));
      $('.js-add-btn').button('reset');
      load.remove();
    }
  });
});

$('.js-tab-link').click(function(e){
  if($(e.currentTarget).hasClass('import')){
    $('.advanced-user-select-foot').addClass('hidden');
  }else{
    $('.advanced-user-select-foot').removeClass('hidden');
  }

});
