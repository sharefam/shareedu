import 'vue-js';
import { Button ,Dialog} from 'element-ui';

Vue.use(Button);
Vue.use(Dialog);
let Main = {
  data:function() {
    return { visible: false,xx:'11' };
  }
};

let Ctor = Vue.extend(Main);
new Ctor().$mount('#btn');