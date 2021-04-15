window._ = require('lodash');
window.axios = require('axios');
import Vue from 'vue'
import {InertiaApp} from "@inertiajs/inertia-vue";
import {BootstrapVue, BootstrapVueIcons} from 'bootstrap-vue';
import VueAxios from 'vue-axios';
import VueMeta from 'vue-meta'
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.use(VueAxios, axios);
Vue.use(VueMeta);
Vue.use(require('vue-moment'));

// Vue.use(InertiaApp);
import { plugin } from '@inertiajs/inertia-vue'
Vue.use(plugin)

import { InertiaProgress } from '@inertiajs/progress'
InertiaProgress.init({
    // The delay after which the progress bar will
    // appear during navigation, in milliseconds.
    delay: 250,

    // The color of the progress bar.
    color: '#29d',

    // Whether to include the default NProgress styles.
    includeCSS: true,

    // Whether the NProgress spinner will be shown.
    showSpinner: false
})


Vue.component('isolation-point-select', require('./components/plant-item/IsolationPointSelect').default);
Vue.component('plant-item-detail', require('./components/plant-item/PlantItemDetail').default);
Vue.component('plant-item-detail-form', require('./components/plant-item/PlantItemDetailForm').default);
Vue.component('plant-item-detail-view', require('./components/plant-item/PlantItemDetailView').default);
Vue.component('plant-item-node', require('./components/plant-item/PlantItemNode').default);
Vue.component('plant-item-select', require('./components/plant-item/PlantItemSelect').default);
Vue.component('plant-item-tree', require('./components/plant-item/PlantItemTree').default);

Vue.mixin({ methods: { route }});

const app = document.getElementById('app')
let vueApp = new Vue({
    //store,
    render: h => h(InertiaApp, {
        props: {
            initialPage: JSON.parse(app.dataset.page),
            resolveComponent: name => require(`./${name}`).default,
            transformProps: props => {
                // We're using this hook as a point at which we can
                // set currentUser available globally rather than
                // having to do it in every top level component.
                if (props.currentUser){
                    //store.commit('auth/setCurrentUser', props.currentUser);
                }else{
                    //store.commit('auth/setCurrentUser', {});
                }
                return {...props}
            },
        },
    }),
}).$mount(app)
vueApp.$http.defaults.withCredentials=true
