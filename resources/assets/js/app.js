require('./bootstrap');

window.Vue = require('vue');

Vue.component('readings', require('./components/Readings.vue'));

const app = new Vue({
    el: '#app'
});
