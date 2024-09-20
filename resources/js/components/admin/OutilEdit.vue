<template>
<div class="container mx-auto px-4">

    <div class="flex flex-col gap-4  max-w-4xl">
        <div class="flex flex-row gap-4">
            <div class="w-32 h-32">
                <img src="/storage/app/images/LB_logo.png" />
            </div>
            <div class="w-full"> 
                <div class="text-[#1b716c]  " >
                    <input  type="text" 
                            class="text-5xl max-w-30 border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                            v-model="newval.nom" 
                            @input="check()" />                     
                    <div class="py-2 max-w-30">
                        <select id="selectcategorie" 
                                class="rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50"
                                v-model="newval.categorie_id"
                        >
                            <option v-for="cat in categories" :value="cat.id">{{ cat.nom }}</option>
                        </select>
                    </div>
                </div>
                <div class="border border-[#1b716c]"> </div>

                <div class="py-2">
                    <textarea class="w-full 
                                     border 
                                     rounded-md 
                                     border-gray-300 
                                     shadow-sm 
                                     focus:border-blue-400 
                                     focus:ring 
                                     focus:ring-blue-200 
                                     focus:ring-opacity-50 
                                     disabled:cursor-not-allowed 
                                     disabled:bg-gray-50 
                                     disabled:text-gray-500" 
                              rows="3" 
                              placeholder="Entrez une description ...."
                              v-model="newval.description"
                    ></textarea>                    
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <img :src="newval.file_path" alt="Photo manquante" />
                <input id="parcourir1" 
                        type="file" 
                        class="block max-w-32 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-blue-500 file:py-2.5 file:px-4 file:text-sm file:font-semibold file:text-white hover:file:bg-primary-700 focus:outline-none disabled:pointer-events-none disabled:opacity-60" 
                        @change="onInputChange"
                        />
            </div>
            <div class="flex flex-col">
                <div>
                    <div class="max-w-80"><img :src="newval.file2_path" alt="Photo manquante" /></div>
                    <input id="parcourir2" 
                        type="file" 
                        class="block max-w-32 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-blue-500 file:py-2.5 file:px-4 file:text-sm file:font-semibold file:text-white hover:file:bg-primary-700 focus:outline-none disabled:pointer-events-none disabled:opacity-60" 
                        @change="onInputChange2"
                        />
                </div>
                <div v-for="(c,idx) in newval.caracteristique" >
                    <div :class="{'bg-[#ebe5d1]' : (idx+1)%2}" class="p-2">
                        <input type="text" 
                            class="text-center  border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                           v-model="c.nom" 
                           @input="check()" />
                         : <input type="text" 
                            class="text-center  border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                           v-model="c.valeur" 
                           @input="check()" />
                        </div>
                </div>
                <div>
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded my-2" @click="addCaracteristique()">
                        Ajouter une caracteristique
                    </button>
                </div>
            </div>
        </div>
        <div class="bg-[#ebe5d1] mb-1 mx-2">
            <div class="bg-[#1b716c] text-sm text-center py-2">CONSEIL D'UTILISATION</div>
            <textarea class="py-2  
                                     w-full 
                                     border 
                                     rounded-md 
                                     border-gray-300 
                                     shadow-sm 
                                     focus:border-blue-400 
                                     focus:ring 
                                     focus:ring-blue-200 
                                     focus:ring-opacity-50 
                                     disabled:cursor-not-allowed 
                                     disabled:bg-gray-50 
                                     disabled:text-gray-500" 
                              rows="5" 
                              placeholder="Entrez les Conseils...."
                              v-model="newval.conseil"
                    >
                    </textarea>
        </div>
        <div class="bg-[#ebe5d1] mb-1 mx-2">
            <div class="bg-[#e74F10] text-sm text-center py-2">PRECAUTION</div>
            <textarea class="py-2 block 
                                     w-full 
                                     border
                                     rounded-md 
                                     border-gray-300 
                                     shadow-sm 
                                     focus:border-blue-500 
                                     focus:ring 
                                     focus:ring-blue-200 
                                     focus:ring-opacity-50 
                                     disabled:cursor-not-allowed 
                                     disabled:bg-gray-50" 
                              rows="5" 
                              placeholder="Entrez les precautions...."
                              v-model="newval.precaution"
                    >
                    </textarea>
        </div>
    </div>
    <div>
                    <label class="mb-1 text-sm font-medium text-gray-700">Prix :</label>
                    <input type="text" 
                            class="text-center max-w-8 border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                           v-model="newval.prix" 
                           @input="check()" />
                </div>
                <div>
                    <label class="mb-1 text-sm font-medium text-gray-700">Durée :</label>
                    <input type="text"  
                            class="text-center max-w-8 border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                            v-model="newval.duree" 
                            @input="check()" />
                </div>
                <div>
                    <label class="mb-1 text-sm font-medium text-gray-700">Nombre disponible :</label>
                    <input type="text"  
                            class="text-center max-w-8 border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                            v-model="newval.nombre" 
                            @input="check()" />
                </div>

</div>
</template>

<script setup>
    import {ref} from 'vue'
    import { upload_file } from '../../helper'
    
    const emits = defineEmits(['onDelete', 'onUpdate'])
    const props = defineProps(['mescategories'])
    const newval = defineModel()

    //const newval = ref(value.value)
    const oldval = ref(JSON.parse(JSON.stringify(newval.value)))
    const haschange = ref(false)
    const categories = ref(props.mescategories)

    function deleteOutil() {
        emits('onDelete')
    }
    function sauveOutil() {
        let data = newval.value
        
        oldval.value = JSON.parse(JSON.stringify(newval.value))
        haschange.value = false
        emits('onUpdate',data)
    }

    function check() {

        if ((oldval.value['nom'] !=newval.value['nom']) || 
        (oldval.value['description'] !=newval.value['description']) ||
        (oldval.value['prix'] !=newval.value['prix']) ||
        (oldval.value['duree'] !=newval.value['duree']) ||
        (oldval.value['nombre'] !=newval.value['nombre']) ||
        (oldval.value['categorie_id'] !=newval.value['categorie_id']) ||
        (oldval.value['file_id'] !=newval.value['file_id'])
        ){
            haschange.value=true
        } else {
            haschange.value=false
        }
    }

    const onInputChange = async(e)=> {
        var newFiles=e.target.files;
        var file = newFiles[0];
        try {
            const req = await upload_file("/api/upload-file", file);
            newval.value['file_id'] = req.data["fileid"];
            newval.value['file_path'] = req.data["filename"];
        } catch (err) {
            console.log(err)
        }

    }
    const onInputChange2 = async(e)=> {
        var newFiles=e.target.files;
        var file = newFiles[0];
        try {
            const req = await upload_file("/api/upload-file", file);
            newval.value['file2_id'] = req.data["fileid"];
            newval.value['file2_path'] = req.data["filename"];
        } catch (err) {
            console.log(err)
        }

    }

    function addCaracteristique() {
        newval.value.caracteristique.push({
            "outil_id":newval.value.id,
            "nom" : "",
            "valeur" : ""
        })
    }


</script>

<style scoped>


</style>