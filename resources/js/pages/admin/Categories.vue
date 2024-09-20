<template>
    <admin>
        <div>Administration des categories</div>

        <div v-for="cat in categories" class="py-4">
            <div v-if="!cat.todelete">
                <input  type="text" 
                        class="mx-4 px-2 text-xl max-w-30 border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 " 
                        v-model="cat.nom" 
                        />
                <input  type="text" 
                        class="mx-4 px-2 text-xl max-w-30 border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        v-model="cat.description" 
                />
                <button class="bg-red-400 mx-2 px-4 py-2 text-white font-bold rounded-md hover:bg-red-600"
                            @click="cat.todelete=true">Supprimer
                </button>
            </div>
        </div>
        <button class="bg-blue-500 mx-2 px-4 py-2 text-white font-bold rounded-md hover:bg-blue-600"
                        @click="addCat()">Ajouter
        </button>
        <button class="bg-blue-500 mx-2 px-4 py-2 text-white font-bold rounded-md hover:bg-blue-600"
                        @click="updCat()">Enregistrer
        </button>
        <button class="bg-blue-500 mx-2 px-4 py-2 text-white font-bold rounded-md hover:bg-blue-600"
                        @click="annuler()">Annuler
        </button>


    </admin>
</template>

<script setup>
    import Admin from './Admin.vue';
    import {request} from '../../helper';
    import {onMounted,ref} from 'vue';
    import {useRouter} from 'vue-router';

    const categories = ref()
    let router = useRouter()

    onMounted(() => {
        handleCategories()
    });

    const handleCategories = async () => {
        try {
            const req = await request('get', '/api/categories')
            categories.value = req.data.data
        } catch (e) {
            
        }
    }


    const addCat = async () => {
        categories.value.push({"id" : 0,"nom" :"" , "description" : ""})
    }


    const delCat = async (cat) => {
        cat.push({"todel":true})
    }

    const updCat = async () => {
        //try {
            console.log(categories.value)
            for (let k in categories.value) {
                const cat = categories.value[k]
                console.log(cat)
                if (cat.id == 0) {
                    if (!cat.todelete) {
                        await request('post', '/api/categories', cat)
                    }
                } else if (cat.todelete) {
                    await request('delete', `/api/categories/${cat.id}`)
                } else {
                    await request('put', `/api/categories/${cat.id}`, cat)
                }                
            }
        
        /*} catch (e) {
            await router.push('/admin')
        }*/
        isLoading.value = false
    }

    function annuler()  {
        router.push('/admin')
    }



</script>