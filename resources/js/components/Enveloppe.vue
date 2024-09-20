<template>
    <Navigation :menus="menus"></Navigation>
    <div class="w-6/12 p-10 mx-auto">
        
    </div>
    <slot></slot>

    <Footer></Footer>

</template>
<script setup>
    import {ref} from 'vue'
    import Footer from '../components/Footer.vue';
    import Navigation from '../components/Navigation.vue'

    const menus = ref([])


</script>
