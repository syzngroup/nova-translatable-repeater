Nova.booting((Vue, router, store) => {
    Vue.component('index-nova-translatable-repeater', require('./components/IndexField'))
    Vue.component('detail-nova-translatable-repeater', require('./components/DetailField'))
    Vue.component('form-nova-translatable-repeater', require('./components/FormField'))
})
