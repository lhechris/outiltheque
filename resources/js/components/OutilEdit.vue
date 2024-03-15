<template>
<div class="grid justify-start gap-5 border-b text-gray-600 border-t mt-3 rounded-md">
    <div class="flex rounded-md">
        <!-- Image et bt parcourir-->
        <div class="m-2 w-64">
            <img class="mb-1 block text-sm font-medium text-gray-700" :src="value.file_path" alt="" srcset="" />
            <input id="example1" 
                    type="file" 
                    class="block max-w-32 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-blue-500 file:py-2.5 file:px-4 file:text-sm file:font-semibold file:text-white hover:file:bg-primary-700 focus:outline-none disabled:pointer-events-none disabled:opacity-60" 
                    @change="onInputChange"
                    />

        </div>
        <!--<div class="flex w-[50%] justify-between flex-col flex-col items-center bg-purple-100">-->
        <div class="flex flex-col justify-start gap-4 m-2">

            <!-- Nom et description -->
                <div>
                    <label class="mb-1 text-sm font-medium text-gray-700">Nom :</label>
                    <input  type="text" 
                            class="max-w-30 border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                            v-model="newval.nom" 
                            @input="check()" > 
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Description :</label>
                    <input type="text" 
                        style="white-space:wrap;overflow:hidden;width:100%" 
                        class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        v-model="newval.description"
                        @input="check()" />                
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
    </div>
</div>
</template>

<script>
import {ref} from 'vue'
import { upload_file } from '../helper'

export default {
    
    emits : ['onDelete', 'onUpdate'],
    props : ['value'],
    

    setup(props,ctx) {
        const newval = ref(props.value)
        const oldval = ref(JSON.parse(JSON.stringify(props.value)))
        const haschange = ref(false)

        function deleteOutil() {
            ctx.emit('onDelete')
        }
        function sauveOutil() {
            let data = newval.value
            
            oldval.value = JSON.parse(JSON.stringify(props.value))
            haschange.value = false
            ctx.emit('onUpdate',data)
        }

        function check() {

            if ((oldval.value['nom'] !=newval.value['nom']) || 
            (oldval.value['description'] !=newval.value['description']) ||
            (oldval.value['prix'] !=newval.value['prix']) ||
            (oldval.value['duree'] !=newval.value['duree']) ||
            (oldval.value['nombre'] !=newval.value['nombre']) ||
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

        return {
            newval,
            haschange,
            deleteOutil, sauveOutil,check,onInputChange
        }
    },
}
</script>

<style scoped>


</style>