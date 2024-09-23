<template>
    <div v-if="outil" class="flex flex-col justify-start gap-4">     
        <FicheOutil :value="props.outil" />
        <div class="flex flex-row gap-4 py-4" >
            <div>
                <label for="debut" class="mb-1 block text-sm font-medium text-gray-700">Date de début</label>
                <input  type="date" 
                        id="debut" 
                        v-model="debut" 
                        :min="mindebut"
                        step="7"
                        class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        placeholder="date de début" 
                        @change="changedebut()"/>
            </div>
            <div>
                <label for="fin" class="mb-1 block text-sm font-medium text-gray-700">Date de fin ({{ outil.duree }}j maximum)</label>
                <input  type="date" 
                        id="fin" 
                        v-model="fin" 
                        :min="minfin"
                        step="7"
                        class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        placeholder="Date de fin" />
            </div>
        </div>
        <div class="flex flex-row gap-4 py-4" >
            <div>
                <label for="nom" class="mb-1 block text-sm font-medium text-gray-700">Nom</label>
                <input  type="text" 
                        id="nom" 
                        v-model="nom" 
                        class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        placeholder="Votre Nom" />
            </div>
            <div>
                <label for="prenom" class="mb-1 block text-sm font-medium text-gray-700">Prénom</label>
                <input  type="text" 
                        id="prenom" 
                        v-model="prenom" 
                        class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        placeholder="Votre Prénom" />
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                <input  type="email" 
                        id="email" 
                        v-model="email" 
                        class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        placeholder="Votre Email" />
            </div>
        </div>
    </div>
    <div v-else>Rien</div>

</template>
<script setup>
    import FicheOutil from './FicheOutil.vue';
    import moment from 'moment'
    import {ref,onMounted} from 'vue'
    

    let dec=0
    if (moment().day() >=3 ) { dec = 7}
    const mindebut = ref(moment().startOf('isoweek').add(3+dec,'days').format("YYYY-MM-DD"))
    const minfin = ref(moment().startOf('isoweek').add(9+dec,'days').format("YYYY-MM-DD"))

    const debut = defineModel('debut')
    const fin = defineModel('fin')

    const nom = defineModel('nom')
    const prenom = defineModel('prenom')
    const email = defineModel('email')

    const props = defineProps(['outil'])

    onMounted(() => {
        debut.value = mindebut.value
        fin.value = minfin.value
        nom.value = ""
        prenom.value = ""
        email.value = ""
    });

    function changedebut() {
        console.log(debut.value)
        const date = new Date(debut.value);
        console.log(date)
        date.setDate(date.getDate() + 7);
        console.log(date)
        fin.value = moment(date).format("YYYY-MM-DD")
        console.log(fin.value)
        
    }

</script>