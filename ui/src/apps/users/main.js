import "babel-polyfill";

import Vue from 'vue';

import VueRouter from 'vue-router';
Vue.use(VueRouter);

import Vuex from 'vuex';
Vue.use(Vuex);
import store from '../../js/store.js';
import userStore from './store';
store.registerModule('userModule', userStore);

import Vuetify from 'vuetify';
Vue.use(Vuetify);
import '@/../node_modules/vuetify/dist/vuetify.min.css';

import VueKindergarten from 'vue-kindergarten';
Vue.use(VueKindergarten, {
    child : store => store.getters.activeUser
});

import UserApp from './app.vue';

const router = new VueRouter({
    routes : [
        {
            path : '/',
            component : UserApp
        }
    ]
});

var app = new Vue({
    router,
    store
}).$mount('#clubmanApp');
