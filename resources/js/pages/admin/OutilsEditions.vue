<template>
    <admin>
        
        <div v-if="outiltoedit && categories">
            <outil v-model="outiltoedit" :mescategories="categories" ></outil>
            <div class="flex flex-row gap-4 p-6" >
                <div v-if="props.outilid==0">
                    <button id="reservation" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" @click="addOutil()">
                        <p>Ajouter</p>
                    </button>
                </div>
                <div v-else>
                    <button id="reservation" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" @click="updateOutil()">
                        <p>Enregistrer</p>
                    </button>
                </div>            
                <button class="bg-red-400 px-4 py-2 text-white font-bold rounded-md hover:bg-red-600"
                        @click="deleteOutil()">Supprimer
                </button>
                <button class="bg-blue-500 px-4 py-2 text-white font-bold rounded-md hover:bg-red-600"
                        @click="annuler()">Annuler
                </button>
                <p v-if="message" class="rounded-md bg-green-50 p-4 text-sm text-green-500">{{ message }}</p>
            </div>
        </div>
        <div v-else>Loading....</div>
        
    </admin>
</template>
<script setup>
    import {ref, onMounted,onBeforeUpdate} from 'vue'
    import {useRouter} from "vue-router";
    import {request} from '../../helper'
    import Admin from './Admin.vue';
    import Outil from '../../components/admin/OutilEdit.vue';

    const outiltoedit = ref()
    const categories = ref()
    const isLoading = ref(true)
    const message = ref()

    const props = defineProps([
        'outilid']
    )

    let router = useRouter();
    onMounted(() => {
        handleOutils()
        handleCategories()
    });

    onBeforeUpdate(() => {
        handleOutils()
        handleCategories()
    });

    const handleOutils = async () => {
        try {
            if (Number(props.outilid)>0) {
                const req = await request('get', '/api/outils/'+props.outilid)
                outiltoedit.value = req.data
            } else {
                outiltoedit.value = {
                    "id":0,
                    "nom":"",
                    "description":"",
                    "prix":1,
                    "duree":7,
                    "nombre":3,
                    "file_id":null,
                    "categorie_id":1,
                    "file2_id":null,
                    "conseil":"",
                    "precaution":"",
                    "caracteristique":[]
                }
            }
        } catch (e) {
            //await router.push('/')
        }
        isLoading.value = false
    }

    const handleCategories = async () => {
        try {
            const req = await request('get', '/api/categories')
            categories.value = req.data.data
        } catch (e) {
            //await router.push('/')
        }
        isLoading.value = false
    }

    const addOutil = async () => {
        try {
            //const data = {nom: nom,  description: description, prix:"1", duree:"7", nombre:"1"}
            const req = await request('post', '/api/outils', outiltoedit.value)
            if (req.data.message) {
                isLoading.value = false
                return alert(req.data.message)
            }
            outiltoedit.value = req.data.data
            message.value = "Outil sauvée"
        } catch (e) {
            //await router.push('/')
        }
        isLoading.value = false
    }


    const deleteOutil = async () => {
        message.value = ""
        if (window.confirm("Vous êtes sur?")) {
            try {
                const req = await request('delete', '/api/outils/'+outiltoedit.value.id)
                if (req.data.message) {
                    isLoading.value = false
                    outiltoedit.value.splice(index, 1)
                }
                message.value = "outil supprimé"
            } catch (e) {
                await router.push('/admin')
            }
            isLoading.value = false
        }
    }

    const updateOutil = async () => {
        message.value = ""
        try {
            const req = await request('put', `/api/outils/${outiltoedit.value.id}`, outiltoedit.value)
            message.value = "Outil sauvé"
            if (req.data.message) {
                isLoading.value = false
                return alert(req.data.message)
            }
        } catch (e) {
            await router.push('/admin')
        }
        isLoading.value = false
    }

    function annuler()  {
        router.push('/admin')
    }



</script>
