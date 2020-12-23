import 'vue-js';
import { DatePicker } from 'element-ui';

Vue.use(DatePicker);

const loginSearchTime = $('#loginSearchTime').val();

new Vue({
  el: '#login-heatmap-chart',
  delimiters: ['${', '}'],
  data: function() {
    return {
      value: new Date(loginSearchTime),
      pickerOptions: {
        firstDayOfWeek: 1
      }
    };
  },
  methods: {
    change(val) {
      const preDate = new Date(val - 24*60*60*1000);
      this.value = preDate;
    }
  }
});