<template>

    <admin>

        <div class="grid grid-row w-full">            
            <table v-if="reservations.length>0">
                <tbody>
                    <tr align="left">
                        <th>Outil</th><th>Début</th><th>Fin</th><th>Nom</th><th>Téléphone</th><th>Paiement</th><th>Statut</th><th>Commentaire</th><th></th><th></th>
                    </tr>
                    <tr v-for="resa in reservations" class="my-2" >
                        <td class="px-2">{{ resa.nomoutil }} </td>
                        <td><input type="date" v-model="resa.debut" class="border rounded-md"/></td>
                        <td><input type="date" v-model="resa.fin" class="border rounded-md"/></td>
                        <td>{{ resa.nom }}</td>
                        <td>{{ resa.telephone }}</td>
                        <td>{{ resa.paiement_state }}</td>
                        <td>{{ resa.state }}</td>
                        <td>
                            <input type="text"  
                                v-model=resa.commentaire
                                class="border rounded-md border-gray-300 shadow-sm focus:border-blue-400 pl-1 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                            />
                        </td>
                        <td>                        
                            <button v-if="resa.paiement_state=='A payer'" @click="payer(resa)"
                                class="mx-2 bg-green-400 px-4 py-2 text-white font-bold rounded-md hover:bg-green-600"
                            >Payer</button>
                        </td>
                        <td>
                            <button @click="valideretour(resa)"
                                class="mx-1 bg-blue-400 px-4 py-2 text-white font-bold rounded-md hover:bg-blue-600"
                            >Retour</button>
                        </td>       
                    </tr>
                </tbody>
            </table>
            <div v-else class="mx-20 max-w-lg rounded-md bg-green-50 p-4 text-sm text-green-500">Pas de réservation en cours</div>
            
            <div>
                <h1 class="w-full text-center bg-yellow-200 my-5">Historique</h1>
                <table v-if="historiques.length>0">
                    <tbody>
                        <tr class="text-left">
                            <th>Outil</th><th>Début</th><th>Fin</th><th>Nom</th><th>Paiement</th><th>Statut</th><th>Commentaire</th>
                        </tr>
                        <tr v-for="resa in historiques" class="my-2 " >
                            <td class="px-2">{{ resa.nomoutil }} </td>
                            <td class="px-2"> {{resa.debut }}</td>
                            <td class="px-2">{{resa.fin}}</td>
                            <td class="px-2">{{ resa.paiement_state }}</td>
                            <td class="px-2">{{ resa.state }}</td>
                            <td class="px-2">{{resa.commentaire}}</td>  
                        </tr>
                    </tbody>
                </table>
                <div v-else class="mx-20 max-w-lg rounded-md bg-green-50 p-4 text-sm text-green-500">Aucun historique de réservations</div>
            </div>
        </div>

    </admin> 

</template>

<script setup>
import {ref, onMounted} from 'vue'
import {request} from '../../helper'
import Admin from './Admin.vue'

        const reservations = ref([])
        const historiques = ref([])

        onMounted(() => {
            handleResas()
            handleHisto()
        });

        const handleResas = async () => {
            try {
                const req = await request('get', '/api/adminreservations')
                reservations.value = req.data.data
                console.log(reservations.value.length)
            } catch (e) {
                console.log(e)
            }
        }

        const handleHisto = async () => {
            try {
                const req = await request('get', '/api/historique')
                historiques.value = req.data.data
            } catch (e) {
                console.log(e)
            }
        }

        const valideretour = async (resa,index) => {
            try {
                const req = await request('delete', `/api/adminreservations/${resa.id}`)
                handleResas()
                handleHisto()
            } catch (e) {

            }
        }


        const payer = async (resa,index) => {
            try {
                resa.paiement_state="Payé"
                const req = await request('put', `/api/adminreservations/${resa.id}`,resa)
                reservations.value[index] = resa

            } catch (e) {

            }
        }

</script>