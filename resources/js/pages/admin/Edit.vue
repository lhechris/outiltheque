<template>
<admin>
    <div>

        <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
            <ul class="flex flex-wrap items-center  overflow-x-auto overflow-y-hidden py-10 justify-center   bg-white text-gray-800" >
                <li v-for="(cat,idx) in categories" :key="idx" class="flex items-center flex-shrink-0 px-5 py-3 space-x-2text-gray-600" role="presentation" >
                    <button :class="'inline-block p-4 border-b-2 rounded-t-lg'+'hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'" 
                            id="profile-tab" 
                            data-tabs-target="#profile" 
                            type="button" role="tab" 
                            aria-controls="profile" 
                            aria-selected="false"
                            @click="selecttab(idx)"
                    >{{cat.nom}}</button>
                </li>
                <li>
                    <div class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <router-link to="/editoutil/0" >Créer un outil</router-link> 
                    </div>
                </li>
            </ul>
        </div>


        <div class="pt-8  bg-yellow-200">
            <div v-for="(cat,idx) in categories" :key="idx" >
                <div :class="{ 'hidden' : selected[idx] }">
                    <categorie :value="cat">
                        <div v-for="(val, idx) in cat.outils" :key="idx" >
                            <outil :value="val" :link="'editoutil/'+val.id" linkname="Modifier"></outil>
                        </div>
                    </categorie>
                </div>
            </div>
        </div>
    </div>
</admin>
</template>

<script setup>
import {onMounted, ref} from "vue"
import {request} from '../../helper'
import Admin from './Admin.vue'
import Outil from '../../components/Outil.vue';
import Categorie from '../../components/Categorie.vue';

const description = ref('')
const nom = ref('')

const selected = ref([])
const currentselection = ref(0)

const categories = ref([])

    onMounted(() => {
        handleCategories()
    })

    const handleCategories = async () => {
        try {
            const req = await request('get', '/api/categoriesdetailed')
            categories.value = req.data.data
            selected.value = []
            for (let cat in categories.value ) {
                selected.value.push(true)
            }
            selected.value[currentselection.value]=false
        } catch (e) {
            //await router.push('/login')
        }
    }


    const addOutil = () => {
        if (description.value === "") {
            return alert("Description cannot be empty");
        }
        if (nom.value === "") {
            return alert("Nom cannot be empty");
        }

        handleNewOutil(nom.value,description.value)
        nom.value = ""
        description.value = ""
    }


    function selecttab(id) {
        selected.value[currentselection.value] = true
        selected.value[id] = false
        currentselection.value=id
    }

</script>