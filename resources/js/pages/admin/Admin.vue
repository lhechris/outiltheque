<template>  
    <Navigation :user="user && user.name" :menus="menus" logout="true" @logout="logout"></Navigation>
    <div class="w-6/12 p-10 mx-auto">
        <Loader v-if="isLoading"/>
    </div>
    <slot></slot>

</template>
<script setup>
    import Navigation from '../../components/Navigation.vue'
    import Loader from '../../components/Loader.vue'
    import {ref,onMounted} from 'vue'
    import { getmenus,request } from '../../helper';
    import {useRouter} from "vue-router";

    const menus=ref(getmenus('admin'))

    const user = ref()
    const isLoading = ref()

    let router = useRouter()

    onMounted(() => {
        authentication()
    });

    const authentication = async () => {
        isLoading.value = true
        try {
            const req = await request('get', '/api/user')
            user.value = req.data
        } catch (e) {
            await router.push('/login')
            console.log(e)
        }
        isLoading.value = false
    }

    function logout() {
        localStorage.removeItem('APP_DEMO_USER_TOKEN')
        router.push('/login')

    }

</script>