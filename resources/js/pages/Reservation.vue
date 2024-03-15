<template>
    <page>
        <div class="grid justify-center gap-5" >
            <h1 >Réservation</h1>
            <div v-if="outils" class="flex ">
                <div>
                    <img class="object-contain h-48 w-96":src="outils.file_path" />
                </div>
                <div class="flex flex-col justify-start gap-4">
                    <p>{{ outils.nom }}</p>
                    <p>{{ outils.description }}</p>
                    <div >
                        <div>
                            <label for="debut" class="mb-1 block text-sm font-medium text-gray-700">Date de début</label>
                            <input  type="date" 
                                    id="debut" 
                                    v-model="debut" 
                                    class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                                    placeholder="date de début" 
                                    @change="changedebut()"/>
                        </div>
                    </div>
                    
                    <div >
                        <div>
                            <label for="fin" class="mb-1 block text-sm font-medium text-gray-700">Date de fin ({{ outils.duree }}j maximum)</label>
                            <input type="date" id="fin" v-model="fin" class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" placeholder="Nb de jour" />
                        </div>
                    </div>

                    <button type="button" 
                            class="rounded-lg border border-blue-500 bg-blue-500 px-5 py-2.5 text-center text-sm font-medium text-white shadow-sm transition-all hover:border-blue-700 hover:bg-blue-700 focus:ring focus:ring-blue-200 disabled:cursor-not-allowed disabled:border-blue-300 disabled:bg-blue-300"
                            @click="reserver"
                    >Réserver
                    </button>
                </div>
            </div>
            <p v-if="message" class="rounded-md bg-green-50 p-4 text-sm text-green-500">{{ message }}</p>
            <p v-if="erreur" class="rounded-md bg-red-50 p-4 text-sm text-red-500">{{ erreur }}</p>
        </div>
    </page>

</template>
<script>
import {ref, onMounted} from 'vue'
import {useRouter} from "vue-router";
import {request} from '../helper'
import Cards from '../components/template2/Cards.vue';
import Page from './Page.vue'
import moment from 'moment'

export default {
    components: {
        Page,Cards
    },
    props : ['outilid'],    

    setup(props) {
        const outils = ref()

        const debut = ref("")
        const fin = ref("")
        const message = ref()
        const erreur = ref()

        let router = useRouter();
        onMounted(() => {
            handleOutils()
        });


        const reserver = async () => {

            let data = {"outil_id" : outils.value.id,
                    "debut" :debut.value,
                    "fin" :fin.value,
                    "user_id" : user.value.id}
            try {
                const req = await request('post','/api/reservations',data)
                if (req.data && req.data.status) {
                    message.value = "Réservé"
                    erreur.value=null
                } else {
                    erreur.value = req.data.message
                    message.value = null
                }
            } catch(e) {
                message.value = e.message
            }

        }

        const handleOutils = async () => {
            
            const url = '/api/outils/'+props.outilid
            //const url = '/api/outils/1'

            try {
                
                const req = await request('get', url)
                outils.value = req.data

            } catch (e) {
                //await router.push('/login')
            }
        }

        function changedebut() {
            const date = new Date(debut.value);
            date.setDate(date.getDate() + 7);
            fin.value = moment(date).format("YYYY-MM-DD")
            
        }

        return {
            outils,            
            debut,
            fin,
            reserver,
            message, erreur,
            changedebut,
        }
    },
}
</script>
