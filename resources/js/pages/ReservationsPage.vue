<template>

    <page>

        <div class="grid grid-row w-full">
            
            <div v-for="resa in reservations" class="my-2 " >
                <label class="px-2">{{ resa.nom }} </label>
                <label> Du </label>
                <input type="date" v-model="resa.debut" class="border rounded-md"/>
                <label> Au </label>
                <input type="date" v-model="resa.fin" class="border rounded-md"/>
                <label> par </label>
                <label>{{ resa.username }}</label>
                <button @click="valideretour(resa)"
                        class="mx-2 bg-blue-400 px-4 py-2 text-white font-bold rounded-md hover:bg-blue-600">Valider retour</button>
                <button @click="valideretour(resa)"
                        class="mx-1 bg-red-400 px-4 py-2 text-white font-bold rounded-md hover:bg-red-600">Supprimer</button>            
            </div>
        </div>

    </page> 

</template>

<script>
import {ref, onMounted} from 'vue'
import {useRouter} from "vue-router";
import {request} from '../helper'
import Cards from '../components/template2/Cards.vue';
import Page from './Page.vue'

export default {
    components: {
        Page,Cards
    },
    setup() {
        const reservations = ref([])

        let router = useRouter();
        onMounted(() => {
            handleResas()
        });

        const handleResas = async () => {
            try {
                const req = await request('get', '/api/adminreservations')
                reservations.value = req.data.data
            } catch (e) {
                await router.push('/login')
            }
        }

        const valideretour = async (resa,index) => {
            try {
                const req = await request('delete', `/api/adminreservations/${resa.id}`)
                reservations.value.splice(index, 1)
            } catch (e) {

            }
        }

        return {
            reservations,            
            valideretour
        }
    },
}
</script>