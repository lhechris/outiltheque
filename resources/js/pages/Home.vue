<template>
    <enveloppe>
      <!--  <profile user="user"></profile>-->
      <div>
            <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap items-center  overflow-x-auto overflow-y-hidden py-10 justify-center   bg-white text-gray-800" >
                <!--<ul class="flex items-center flex-shrink-0 px-5 py-3 space-x-2text-gray-600" >-->
                    <li v-for="(cat,idx) in categories" :key="idx" class="me-2" role="presentation" >
                        <button class="inline-block 
                                       p-4
                                       px-1
                                       sm:px-4
                                       border-b-2 
                                       rounded-t-lg 
                                       hover:text-red-600 
                                       hover:border-gray-300 
                                       dark:hover:text-red-300" 
                                id="profile-tab" 
                                data-tabs-target="#profile" 
                                type="button" role="tab" 
                                aria-controls="profile" 
                                aria-selected="false"
                                @click="selecttab(idx)"
                        >{{cat.nom}}</button>
                    </li>
                </ul>
            </div>
            <div v-for="(cat,idx) in categories" :key="idx" >
                <div :class="{ 'hidden' : selected[idx] }">
                    <categorie :value="cat">
                        <div v-for="(val, idx) in cat.outils" :key="idx" >
                            <outil :value="val" :link="'reservation/'+val.id" linkname="Je réserve"></outil>
                        </div>
                    </categorie>
                </div>
            </div>
        </div>
    </enveloppe>

</template>
<script setup>
    import {ref, onMounted} from 'vue'
    import {request} from '../helper'
    import Enveloppe from '../components/Enveloppe.vue';
    import Outil from '../components/Outil.vue';
    import Categorie from '../components/Categorie.vue';
    import Profile from './admin/Profile.vue'

    const categories = ref([])
    const isLoading = ref()
    const selected = ref([])
    const currentselection = ref(0)

    onMounted(() => {
        handleCategories()
    });

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
        isLoading.value = false
    }

    function selecttab(id) {
        selected.value[currentselection.value] = true
        selected.value[id] = false
        currentselection.value=id
    }


</script>
