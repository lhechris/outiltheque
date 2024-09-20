<template>  
    <Navigation :user="user && user.name" :menus="menus" logout="true"></Navigation>
    <div class="w-6/12 p-10 mx-auto">
        <Loader v-if="isLoading"/>
    </div>
    <slot></slot>

</template>
<script setup>
    import Navigation from '../../components/Navigation.vue'
    import Loader from '../../components/Loader.vue'
    import {ref,onMounted} from 'vue'
    import { getmenus } from '../../helper';

    const menus=ref(getmenus('admin'))

    const user = ref()
    const isLoading = ref()

    onMounted(() => {
        authentication()
    });

    const authentication = async () => {
        isLoading.value = true
        try {
            const req = await request('get', '/api/user')
            user.value = req.data
        } catch (e) {
            //await router.push('/login')
        }
        isLoading.value = false
    }


</script>