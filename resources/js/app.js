/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

//window.Vue = require('vue');
import Vue from 'vue'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css';

import lang from 'element-ui/lib/locale/lang/es'
import locale from 'element-ui/lib/locale'
locale.use(lang)

import Axios from 'axios'

//Vue.use(ElementUI)
Vue.use(ElementUI, {
    size: 'small'
})
Vue.prototype.$eventHub = new Vue()
Vue.prototype.$http = Axios


Vue.component('configurations-index', require('./views/configurations/index.vue').default);
Vue.component('configurations-form-admin', require('./views/configurations/formadmin.vue').default);

Vue.component('documents-index', require('./views/documents/index.vue').default);
Vue.component('taxes-index', require('./views/taxes/index.vue').default);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#main-wrapper',
    methods: {
        openEditModal(companyId, identificationNumber, dv, typeDocId, typeRegimeId, typeLiabilityId, municipalityId, merchantReg, address, phone, apiToken) {
            // Guardar en variables globales para uso posterior
            window.currentCompanyId = companyId;
            window.currentApiToken = apiToken;

            // Usar setTimeout para asegurar que jQuery esté listo
            this.$nextTick(() => {
                // Cargar datos en el formulario
                $('#identification_number').val(identificationNumber);
                $('#dv').val(dv);
                $('#type_document_identification_id').val(typeDocId);
                $('#type_regime_id').val(typeRegimeId);
                $('#type_liability_id').val(typeLiabilityId);
                $('#municipality_id').val(municipalityId);
                $('#merchant_registration').val(merchantReg);
                $('#address').val(address);
                $('#phone').val(phone);

                // Abrir modal
                $('#editCompanyModal').modal('show');
            });
        }
    }
});
