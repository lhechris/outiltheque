<template>
    <admin>
        <div v-if="user">

            <div class="mx-auto w-4/12 mt-10 p-4 ">
        <!-- component -->
        <div
            class="bg-white  rounded-lg px-8 pt-6 pb-8 mb-2 flex flex-col"
        >
            <h1 class="text-gray-600 py-5 font-bold text-3xl"> Modification du compte {{ user.username }}</h1>
            <div class="text-red-400" v-if="errors"> {{errors}} </div>
            <div v-if="success" class="text-green-400">{{ success }}</div>
            <form method="post" @submit="handleSubmit">
            <div class="mb-4 mt-3">
                <label
                    class="block text-grey-darker text-sm font-bold mb-2"
                    for="name"
                >
                   Nom complet
                </label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker"
                    id="name"
                    type="text"
                    required
                    v-model="user.name"
                />
            </div>
            <div class="mb-4">
                <label
                    class="block text-grey-darker text-sm font-bold mb-2"
                    for="email"
                >
                    Adresse email
                </label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-grey-darker"
                    type="email"
                    id="email"
                    required
                    v-model="user.email"
                />
            </div>
            <div class="mb-4" v-if="changermdp">
                <label 
                    class="block text-grey-darker text-sm font-bold mb-2"
                    for="password"
                >
                    Ancien mot de passe
                </label>
                <input 
                    class="shadow appearance-none border border-red rounded w-full py-2 px-3 text-grey-darker mb-3"
                    id="oldpassword"
                    type="password"
                    v-model="user.oldpassword"
                />
                <label 
                    class="block text-grey-darker text-sm font-bold mb-2"
                    for="password"
                >
                    Nouveau mot de passe
                </label>
                <input 
                    class="shadow appearance-none border border-red rounded w-full py-2 px-3 text-grey-darker mb-3"
                    id="password"
                    type="password"
                    v-model="user.password"
                />
                <!-- <p class="text-red text-xs italic">Please choose a password.</p> -->
            </div>
            
            <div v-else class="mb-4">
                <a class="text-blue-500 cursor-pointer" @click="changermdp=true">Changer le mot de passe</a>
            </div>

            <div class="flex items-center justify-between">
                <button
                    class="bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded"
                    type="submit"
                >
                    Enregistrer
                </button>
            </div>
            </form>
        </div>
    </div>
    </div>
</admin>
</template>

<script setup>
    import {ref, onMounted,reactive} from 'vue'
    import {request} from '../../helper'
    import Admin from './Admin.vue'

    const user = ref()
    const errors = ref();
    const success = ref("");
    const changermdp = ref(false)

    const handleSubmit = async(evt) => {
        evt.preventDefault()
        success.value=null
        errors.value=null

        try {
            const result = await request('put','/api/users/'+user.value.id, user.value);
            success.value="Modifications ok"
            user.value.password=null
            user.value.oldpassword=null
            changermdp.value=false

        }catch (e) {
            console.log(e)
            if(e.response.data && e.response.data.message) {
                errors.value = e.response.data.message
            }
            
        }            
    }


    onMounted(() => {
        handleUser();
    });

    const handleUser = async () => {
        try {
            const req = await request('get', '/api/user')
            user.value = req.data
            console.log(req)

        } catch (e) {

        }
    }

</script>