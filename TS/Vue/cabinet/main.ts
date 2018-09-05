import Vue from 'vue';
import Cabinet from './Cabinet.vue';
import Router from 'vue-router';
import routes from './router';
import store from './store';

import ToggleAside from './components/utils/toggle-aside.vue';
import Toggler from './components/utils/toggler.vue'

import BootstrapVue from 'bootstrap-vue';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';


Vue.config.productionTip = false;
Vue.use(BootstrapVue);
Vue.use(Router);

Vue.component('toggle-aside',ToggleAside);
Vue.component('toggler',Toggler);

const router = new Router({routes});


new Vue({
  router,
  store,
  beforeMount: () => {store.dispatch('loadCompany')},
  render: (h) =>  h(Cabinet) ,
}).$mount('#cabinet');
