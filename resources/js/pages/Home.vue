<template>
    <page>
        <profile user="user"></profile>
        <cards>
            <div v-for="(val, idx) in outils" :key="idx" >
                <!--<outil :value="val" @onReserve="reserve(val)"></outil>-->
                <outil :value="val" :link="'reservation/'+val.id"></outil>

            </div>
        </cards>
    </page>

</template>
<script>
import {ref, onMounted} from 'vue'
import {useRouter} from "vue-router";
import {request} from '../helper'
import Page from './Page.vue';
import Outil from '../components/template2/Outil.vue';
import Cards from '../components/template2/Cards.vue';
import Profile from './Profile.vue'

export default {
    components: {
        Outil, Page,Cards,Profile
    },
    setup() {
        const outils = ref([])
        const isLoading = ref()

        let router = useRouter();
        onMounted(() => {
            handleOutils()
        });

        const handleOutils = async () => {
            try {
                const req = await request('get', '/api/outils')
                outils.value = req.data.data
            } catch (e) {
                await router.push('/login')
            }
            isLoading.value = false
        }

        const reserve = async(outil) => {
            await router.push("/reservation/"+outil.id)
        }

        return {
            outils,            
            isLoading,
            reserve,
        }
    },
}
</script>
