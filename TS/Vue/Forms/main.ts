import Vue from "vue";

import BootstrapVue from 'bootstrap-vue';

// import 'bootstrap/dist/css/bootstrap.css';
// import 'bootstrap-vue/dist/bootstrap-vue.css';

import LoginForm  from './LoginForm.vue';
import RegisterForm from './RegisterForm.vue';
import PassResetForm from './PassResetForm.vue';
import AskQuestionForm from './AskQuestionForm.vue';

Vue.use(BootstrapVue);

let lf = new LoginForm({
    el: document.getElementById('loginForm')
});
let rf = new RegisterForm({
    el: document.getElementById('registerForm')
});
let prf = new PassResetForm({
    el: document.getElementById('passResetForm')
});

let aqf = new AskQuestionForm({
    el: document.getElementById('questionForm')
});



Vue.component('login-form',lf);
Vue.component('register-form',rf);
Vue.component('pass-reset-form',prf);
Vue.component('question-form', aqf);

new Vue();
