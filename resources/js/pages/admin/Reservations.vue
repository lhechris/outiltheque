<template>

    <admin>

        <div class="grid grid-row w-full">
            <table>
                <tr align="left">
                    <th>Outil</th><th>Début</th><th>Fin</th><th>Nom</th><th>Paiement</th><th></th><th></th>
                </tr>
                <tr v-for="resa in reservations" class="my-2 " >
                    <td class="px-2">{{ resa.nomoutil }} </td>
                    <td><input type="date" v-model="resa.debut" class="border rounded-md"/></td>
                    <td><input type="date" v-model="resa.fin" class="border rounded-md"/></td>
                    <td>{{ resa.prenom }} {{ resa.nom }}</td>
                    <td>{{ resa.paiement_state }}</td>
                    <td><button @click="valideretour(resa)"
                            class="mx-2 bg-blue-400 px-4 py-2 text-white font-bold rounded-md hover:bg-blue-600">Valider retour</button></td>
                    <td><button @click="valideretour(resa)"
                            class="mx-1 bg-red-400 px-4 py-2 text-white font-bold rounded-md hover:bg-red-600">Supprimer</button></td>       
                </tr>
            </table>
        </div>

    </admin> 

</template>

<script setup>
import {ref, onMounted} from 'vue'
import {useRouter} from "vue-router";
import {request} from '../../helper'
import Admin from './Admin.vue'

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
                //await router.push('/login')
            }
        }

        const valideretour = async (resa,index) => {
            try {
                const req = await request('delete', `/api/adminreservations/${resa.id}`)
                reservations.value.splice(index, 1)
            } catch (e) {

            }
        }

</script>