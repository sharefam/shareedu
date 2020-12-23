import OverviewDateRangePicker from '../../../../common/date-range-picker';
import ChangeList from '../../../../common/change-list';
import Count from './user-count';
import Tag from '../../../../common/tag-hover';
import './time-select';
import './login-heatmap';

new OverviewDateRangePicker('#date-range-picker');
new ChangeList({el: '.js-rank-list'});
new Count();
new Tag({el: '.user-group-tag_list'});

$('[data-toggle="tooltip"]').tooltip();
window.onload = function() {
  new window.$.CheckTreeviewInput({
    $elem: $('#user-orgCode'),
    selectType: 'single',
  });

  new window.$.CheckTreeviewInput({
    $elem: $('#user-orgCode-login'),
    selectType: 'single',
  });

  new window.$.CheckTreeviewInput({
    $elem: $('#user-orgCode-rank'),
    selectType: 'single',
  });
};
