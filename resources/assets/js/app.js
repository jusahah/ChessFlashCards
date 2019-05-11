import Vue from 'vue'
import moment from 'moment'

// Bootstrap-Vue templating
import BootstrapVue from 'bootstrap-vue'
//import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.use(BootstrapVue);

// This provided local prettification for timestamps. Can be used in all components.
Vue.filter('prettytime', function (datetimestring) {
    if (!datetimestring) {
        // Probably null
        return '---';
    }
    var d = moment(datetimestring);
    return d.format('DD.MM.YY HH:mm');
})

// This provided local prettification for timestamps that need only date part (no hours or mins). 
Vue.filter('prettydate', function (datetimestring) {
    if (!datetimestring) {
        // Probably null
        return '---';
    }
    var d = moment(datetimestring);
    return d.format('DD.MM.YYYY');
})

import App from './app/App'

import router from './app/routes/router'

const app = new Vue({
    el: '#app',
    components: { App },
    router,
});


console.log("Built Vue instance");
