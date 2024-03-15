<template>
    <page>
        <div class="pt-32  bg-white">
            <h1 class="text-center text-2xl font-bold text-gray-800">Editions des outils</h1>
        </div>
        <div class="flex lg">
            <div class="w-1/4 bg-blue-200">
                <ul>
                    <li v-for="(val, idx) in outils" :key="idx" class="py-2" >
                        <button @click="displayoutil(val)">{{val.nom}}</button>
                    </li>
                    <li class="py-3">
                        <input type="text" class="p-2 w-64 border rounded-md" v-model="nom" placeholder="Nom de l'outil"/>
                        <input type="text" class="p-2 w-64 border rounded-md" v-model="description" placeholder="Description de l'outil"/>
                        <button class="bg-blue-600 text-white px-5 py-2 rounded-md ml-2 hover:bg-blue-400" @click="addOutil">Ajouter</button>                    
                    </li>
                </ul>
            </div>
            <div class="w-3/4 h-40">
                <div v-for="(val, idx) in outils" :key="idx">
                    <div v-if="selectedoutil==val" >
                        <outil :value="val" ></outil>
                        <button id="reservation" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" @click="updateOutil(val.id,val)">
                            <p>Modifier</p>
                        </button>
                        <button class="bg-red-400 px-4 py-2 text-white font-bold rounded-md hover:bg-red-600"
                                @click="deleteOutil(val,idx)">Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </page>
</template>
<script>
import {ref, onMounted} from 'vue'
import {useRouter} from "vue-router";
import {request} from '../helper'
import Page from './Page.vue';
import Outil from '../components/OutilEdit.vue';

export default {
    components: {
        Page,Outil
    },
    setup() {
        const description = ref('')
        const nom = ref('')
        const outils = ref([])
        const selectedoutil = ref()
        const isLoading = ref(true)

        let router = useRouter();
        onMounted(() => {
            handleOutils()
        });

        const handleOutils = async () => {
            try {
                const req = await request('get', '/api/outils')
                outils.value = req.data.data
            } catch (e) {
                //await router.push('/')
            }
            isLoading.value = false
        }

        const handleNewOutil = async (nom,  description) => {
            try {
                const data = {nom: nom,  description: description, prix:"1", duree:"7", nombre:"1"}
                const req = await request('post', '/api/outils', data)
                if (req.data.message) {
                    isLoading.value = false
                    return alert(req.data.message)
                }
                outils.value.push(req.data.data)
                selectedoutil.value=req.data.data
            } catch (e) {
                //await router.push('/')
            }
            isLoading.value = false
        }

        const addOutil = () => {
            if (description.value === "") {
                return alert("Description cannot be empty");
            }
            if (nom.value === "") {
                return alert("Nom cannot be empty");
            }
            isLoading.value = true
            handleNewOutil(nom.value,description.value)
            nom.value = ""
            description.value = ""
        }

        const deleteOutil = async (val, index) => {
            if (window.confirm("Vous êtes sur?")) {
                try {
                    const req = await request('delete', `/api/outils/${val.id}`)
                    if (req.data.message) {
                        isLoading.value = false
                        outils.value.splice(index, 1)
                    }
                } catch (e) {
                    await router.push('/')
                }
                isLoading.value = false
            }
        }

        const updateOutil = async (id, val) => {
            try {
                const req = await request('put', `/api/outils/${id}`, val)
                if (req.data.message) {
                    isLoading.value = false
                    return alert(req.data.message)
                }
            } catch (e) {
                await router.push('/')
            }
            isLoading.value = false
        }

        function displayoutil(outil) {
            selectedoutil.value=outil
        }

        return {
            nom,
            description,
            outils,
            addOutil,
            deleteOutil, 
            updateOutil,
            selectedoutil,
            displayoutil
        }
    },
}
</script>
