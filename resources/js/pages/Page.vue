<template>
    <Navigation :user="user && user.name" :menus="menus"></Navigation>
    <div class="w-6/12 p-10 mx-auto">
        <Loader v-if="isLoading"/>
    </div>
    <slot></slot>

    <Footer></Footer>

</template>
<script>
import {ref, onMounted} from 'vue'
import {request,getmenus} from '../helper'
import Loader from '../components/Loader.vue';
import Footer from '../components/template2/Footer.vue';
import Navigation from '../components/template2/Navigation.vue'

export default {
    components: {
        Loader, Navigation,Footer
    },
    setup() {
        const user = ref()
        const isLoading = ref()
        const menus = ref([])

        onMounted(() => {
            authentication()
        });

        const authentication = async () => {
            isLoading.value = true
            try {
                const req = await request('get', '/api/user')
                user.value = req.data
                menus.value = getmenus(user.value.role)
            } catch (e) {
                await router.push('/login')
            }
            isLoading.value = false
        }

        return {
            user,
            isLoading,
            menus,
        }
    },
}
</script>
