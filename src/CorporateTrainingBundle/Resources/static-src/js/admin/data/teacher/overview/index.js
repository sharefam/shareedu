import '../../../../common/echarts-theme';
import ChangeList from '../../../../common/change-list';
import ajaxLink from './ajax-link';
import './echarts';
new ChangeList({el: '.js-rank-list'});
const $searchWrap = $('.js-search-wrap');

$('.o-ct-tab_time').on('click', '.js-tab-link', (e)=>{
  let $that = $(e.currentTarget);
  let type = $that.data('type');
  $that.siblings().removeClass('active');
  $that.addClass('active');
  if(type=='current') {
    for (let i = 0; i < $searchWrap.length; i++) {
      new ajaxLink({element: $searchWrap[i], type: 'currenturl', i});
    }
  } else {
    for (let i = 0; i < $searchWrap.length; i++) {
      new ajaxLink({element: $searchWrap[i], type: 'lasturl', i});
    }
  }
});

